<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class OpenSearchService
{
    private $opensearchHost;
    private $opensearchUser;
    private $opensearchPassword;
    private $wazuhHost;
    private $wazuhUser;
    private $wazuhPassword;

    public function __construct()
    {
        $this->opensearchHost = env('OPENSEARCH_HOST', 'https://192.168.200.150:9200');
        $this->opensearchUser = env('OPENSEARCH_USER', 'admin');
        $this->opensearchPassword = env('OPENSEARCH_PASSWORD', 'admin');
        
        $this->wazuhHost = env('WAZUH_HOST', 'https://192.168.200.150:55000');
        $this->wazuhUser = env('WAZUH_USER', 'admin');
        $this->wazuhPassword = env('WAZUH_PASSWORD', 'admin');
    }

    /**
     * Get alert counts for the last 7 days
     */
    public function getAlertTrendLast7Days()
    {
        $query = [
            'size' => 0,
            'aggs' => [
                'alerts_by_day' => [
                    'date_histogram' => [
                        'field' => 'timestamp',
                        'fixed_interval' => '1d',
                        'min_doc_count' => 0,
                    ]
                ]
            ],
            'query' => [
                'range' => [
                    'timestamp' => [
                        'gte' => 'now-6d/d',
                        'lte' => 'now'
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(2)
                ->timeout(2)
                ->withBasicAuth($this->opensearchUser, $this->opensearchPassword)
                ->post("{$this->opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                return $this->parseAlertTrendResponse($response->json());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::warning('OpenSearch alert trend query timeout: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('OpenSearch alert trend query failed: ' . $e->getMessage());
        }

        return $this->getFallbackData();
    }

    /**
     * Parse OpenSearch response and extract daily alert counts
     */
    private function parseAlertTrendResponse($data)
    {
        $buckets = $data['aggregations']['alerts_by_day']['buckets'] ?? [];
        $trend = [];

        foreach ($buckets as $bucket) {
            $trend[] = $bucket['doc_count'];
        }

        return $trend ?: $this->getFallbackData();
    }

    /**
     * Get alert severity distribution (Critical, High, Medium, Low)
     */
    public function getAlertSeverityDistribution()
    {
        $query = [
            'size' => 0,
            'aggs' => [
                'severity_counts' => [
                    'terms' => [
                        'field' => 'rule.level',
                        'size' => 20,
                    ]
                ]
            ],
            'query' => [
                'range' => [
                    'timestamp' => [
                        'gte' => 'now-7d'
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(2)
                ->timeout(2)
                ->withBasicAuth($this->opensearchUser, $this->opensearchPassword)
                ->post("{$this->opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                return $this->parseSeverityResponse($response->json());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::warning('OpenSearch severity query timeout: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('OpenSearch severity query failed: ' . $e->getMessage());
        }

        return [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ];
    }

    /**
     * Parse severity distribution from OpenSearch
     */
    private function parseSeverityResponse($data)
    {
        $buckets = $data['aggregations']['severity_counts']['buckets'] ?? [];
        
        $critical = 0;
        $high = 0;
        $medium = 0;
        $low = 0;

        foreach ($buckets as $bucket) {
            $level = $bucket['key'];
            $count = $bucket['doc_count'];

            if ($level >= 12) {
                $critical += $count;
            } elseif ($level >= 9) {
                $high += $count;
            } elseif ($level >= 6) {
                $medium += $count;
            } else {
                $low += $count;
            }
        }

        return [
            'critical' => $critical,
            'high' => $high,
            'medium' => $medium,
            'low' => $low,
        ];
    }

    /**
     * Get total alert count
     */
    public function getTotalAlertCount()
    {
        $severity = $this->getAlertSeverityDistribution();
        return array_sum($severity);
    }

    /**
     * Get OS distribution from Wazuh Agent API
     */
    public function getOsDistribution()
    {
        try {
            // Get Wazuh API token with aggressive timeout
            $tokenResponse = Http::withoutVerifying()
                ->connectTimeout(2)
                ->timeout(2)
                ->withBasicAuth($this->wazuhUser, $this->wazuhPassword)
                ->post("{$this->wazuhHost}/security/user/authenticate");

            if (!$tokenResponse->successful()) {
                \Log::warning('Failed to authenticate with Wazuh API: ' . $tokenResponse->status());
                return $this->getOsFallbackData();
            }

            $token = $tokenResponse->json('data.token');
            \Log::info('Wazuh token obtained successfully');

            // Get all agents from Wazuh API
            $agentsResponse = Http::withoutVerifying()
                ->connectTimeout(2)
                ->timeout(2)
                ->withToken($token)
                ->get("{$this->wazuhHost}/agents", [
                    'limit' => 500,
                    'select' => 'id,name,os.name'
                ]);

            \Log::info('Wazuh agents API response: ' . $agentsResponse->status());
            
            if (!$agentsResponse->successful()) {
                \Log::warning('Wazuh agents API request failed: ' . $agentsResponse->status());
                return $this->getOsFallbackData();
            }

            $agents = $agentsResponse->json('data.affected_items') ?? [];
            \Log::info('Agents retrieved: ' . count($agents));
            
            if (empty($agents)) {
                \Log::warning('No agents returned from Wazuh API');
                return $this->getOsFallbackData();
            }

            // Aggregate agents by OS
            $osDistribution = [];
            foreach ($agents as $agent) {
                if (isset($agent['os']) && isset($agent['os']['name'])) {
                    $osName = $agent['os']['name'];
                    $osDistribution[$osName] = ($osDistribution[$osName] ?? 0) + 1;
                }
            }

            if (!empty($osDistribution)) {
                \Log::info('OS distribution from Wazuh API: ' . json_encode($osDistribution));
                return $osDistribution;
            }

            \Log::warning('No OS data found in Wazuh agents');
            return $this->getOsFallbackData();

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::warning('Wazuh API connection timeout or unreachable: ' . $e->getMessage() . '. Using fallback data.');
            return $this->getOsFallbackData();
        } catch (\Exception $e) {
            \Log::warning('Wazuh OS distribution query failed: ' . $e->getMessage() . '. Using fallback data.');
            return $this->getOsFallbackData();
        }
    }

    /**
     * Get top triggered rules
     */
    public function getTopTriggeredRules($limit = 5)
    {
        $query = [
            'size' => 0,
            'aggs' => [
                'top_rules' => [
                    'terms' => [
                        'field' => 'rule.id',
                        'size' => $limit,
                    ],
                    'aggs' => [
                        'rule_description' => [
                            'terms' => [
                                'field' => 'rule.description',
                                'size' => 1,
                            ]
                        ],
                        'rule_level' => [
                            'terms' => [
                                'field' => 'rule.level',
                                'size' => 1,
                            ]
                        ]
                    ]
                ]
            ],
            'query' => [
                'range' => [
                    'timestamp' => [
                        'gte' => 'now-7d'
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(2)
                ->timeout(2)
                ->withBasicAuth($this->opensearchUser, $this->opensearchPassword)
                ->post("{$this->opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                return $this->parseTopRulesResponse($response->json());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::warning('OpenSearch top rules query timeout: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('OpenSearch top rules query failed: ' . $e->getMessage());
        }

        return $this->getTopRulesFallbackData();
    }

    /**
     * Parse top rules response
     */
    private function parseTopRulesResponse($data)
    {
        $buckets = $data['aggregations']['top_rules']['buckets'] ?? [];
        $rules = [];

        foreach ($buckets as $bucket) {
            $ruleId = $bucket['key'] ?? '';
            $count = $bucket['doc_count'] ?? 0;
            $description = $bucket['rule_description']['buckets'][0]['key'] ?? 'Unknown';
            $level = $bucket['rule_level']['buckets'][0]['key'] ?? 0;

            $rules[] = [
                'id' => $ruleId,
                'description' => $description,
                'level' => $level,
                'count' => $count,
            ];
        }

        return $rules ?: $this->getTopRulesFallbackData();
    }

    /**
     * Get top agents by alert count
     */
    public function getTopAgentsByAlerts($limit = 5)
    {
        $query = [
            'size' => 0,
            'aggs' => [
                'top_agents' => [
                    'terms' => [
                        'field' => 'agent.id',
                        'size' => $limit,
                    ],
                    'aggs' => [
                        'agent_name' => [
                            'terms' => [
                                'field' => 'agent.name',
                                'size' => 1,
                            ]
                        ],
                        'agent_ip' => [
                            'terms' => [
                                'field' => 'agent.ip',
                                'size' => 1,
                            ]
                        ],
                        'agent_os' => [
                            'terms' => [
                                'field' => 'agent.os.name',
                                'size' => 1,
                            ]
                        ]
                    ]
                ]
            ],
            'query' => [
                'range' => [
                    'timestamp' => [
                        'gte' => 'now-7d'
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(2)
                ->timeout(2)
                ->withBasicAuth($this->opensearchUser, $this->opensearchPassword)
                ->post("{$this->opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                return $this->parseTopAgentsResponse($response->json());
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::warning('OpenSearch top agents query timeout: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('OpenSearch top agents query failed: ' . $e->getMessage());
        }

        return $this->getTopAgentsFallbackData();
    }

    /**
     * Parse top agents response
     */
    private function parseTopAgentsResponse($data)
    {
        $buckets = $data['aggregations']['top_agents']['buckets'] ?? [];
        $agents = [];

        foreach ($buckets as $bucket) {
            $agentId = $bucket['key'] ?? '';
            $count = $bucket['doc_count'] ?? 0;
            $name = $bucket['agent_name']['buckets'][0]['key'] ?? 'Unknown';
            $ip = $bucket['agent_ip']['buckets'][0]['key'] ?? 'N/A';
            $os = $bucket['agent_os']['buckets'][0]['key'] ?? 'Unknown';

            $agents[] = [
                'id' => $agentId,
                'name' => $name,
                'ip' => $ip,
                'os' => $os,
                'alert_count' => $count,
            ];
        }

        return $agents ?: $this->getTopAgentsFallbackData();
    }

    /**
     * Fallback data
     */
    private function getFallbackData()
    {
        return [1420, 1835, 1230, 2105, 1784, 980, 1493];
    }

    private function getOsFallbackData()
    {
        return [
            'Linux' => 22,
            'Windows' => 14,
            'macOS' => 6,
            'FreeBSD' => 3,
            'Other' => 2,
        ];
    }

    private function getTopRulesFallbackData()
    {
        return [
            ['id' => '5501', 'description' => 'User successfully logged in', 'level' => 3, 'count' => 2341],
            ['id' => '40111', 'description' => 'Firewall Drop event', 'level' => 8, 'count' => 1892],
            ['id' => '1002', 'description' => 'Unknown problem somewhere in the system', 'level' => 10, 'count' => 1204],
            ['id' => '5402', 'description' => 'PAM: Login session opened', 'level' => 3, 'count' => 987],
            ['id' => '31101', 'description' => 'Web server 400 error code', 'level' => 6, 'count' => 763],
        ];
    }

    private function getTopAgentsFallbackData()
    {
        return [
            ['id' => '001', 'name' => 'web-server-prod', 'ip' => '192.168.1.10', 'os' => 'Ubuntu 22.04', 'alert_count' => 3241],
            ['id' => '008', 'name' => 'db-server-01', 'ip' => '192.168.1.25', 'os' => 'CentOS 7', 'alert_count' => 2108],
            ['id' => '014', 'name' => 'firewall-edge', 'ip' => '10.0.0.1', 'os' => 'Windows Server 2019', 'alert_count' => 1874],
            ['id' => '022', 'name' => 'mail-server', 'ip' => '192.168.2.5', 'os' => 'Debian 11', 'alert_count' => 1102],
            ['id' => '031', 'name' => 'workstation-dev3', 'ip' => '192.168.3.11', 'os' => 'Windows 11', 'alert_count' => 892],
        ];
    }
}
