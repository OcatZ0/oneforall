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
     * Debug: Get sample documents to understand the data structure
     */
    public function getAgentEvolutionDebug($agentIds = null)
    {
        try {
            // Get sample documents to see the structure
            $query = [
                'size' => 5,
                'query' => [
                    'range' => [
                        'timestamp' => [
                            'gte' => 'now-24h',
                            'lte' => 'now'
                        ]
                    ]
                ]
            ];

            if ($agentIds && !empty($agentIds)) {
                $query['query'] = [
                    'bool' => [
                        'must' => [
                            [
                                'range' => [
                                    'timestamp' => [
                                        'gte' => 'now-24h',
                                        'lte' => 'now'
                                    ]
                                ]
                            ],
                            [
                                'terms' => [
                                    'agent.id' => $agentIds
                                ]
                            ]
                        ]
                    ]
                ];
            }

            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(3)
                ->withBasicAuth($this->opensearchUser, $this->opensearchPassword)
                ->post("{$this->opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                $hits = $response->json('hits.hits') ?? [];
                \Log::info('Sample documents from OpenSearch: ' . json_encode($hits, JSON_PRETTY_PRINT));
                return $hits;
            }

            \Log::warning('OpenSearch debug query unsuccessful: ' . $response->status());
            return null;

        } catch (\Exception $e) {
            \Log::error('OpenSearch debug query failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get agent evolution data for last 24 hours in 10-minute intervals
     * Filters by specific agent IDs if provided
     */
    public function getAgentEvolutionLast24Hours($agentIds = null)
    {
        $labels = [];
        $dataPoints = [];
        
        try {
            // Generate time labels for last 24 hours (144 intervals of 10 minutes)
            $now = Carbon::now();
            for ($i = 144; $i >= 0; $i--) {
                $time = $now->copy()->subMinutes($i * 10);
                $labels[] = $time->format('H:i');
            }

            // Build query to get distinct agent counts per time interval
            $query = [
                'size' => 0,
                'aggs' => [
                    'timeline' => [
                        'date_histogram' => [
                            'field' => 'timestamp',
                            'fixed_interval' => '10m',
                            'min_doc_count' => 0,
                        ],
                        'aggs' => [
                            'unique_agents' => [
                                'cardinality' => [
                                    'field' => 'agent.id',
                                    'precision_threshold' => 100
                                ]
                            ]
                        ]
                    ]
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'range' => [
                                    'timestamp' => [
                                        'gte' => 'now-24h',
                                        'lte' => 'now'
                                    ]
                                ]
                            ],
                            [
                                'term' => [
                                    'status' => 'active'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            // Add agent ID filter if provided
            if ($agentIds && !empty($agentIds)) {
                $query['query']['bool']['must'][] = [
                    'terms' => [
                        'agent.id' => $agentIds
                    ]
                ];
            }

            \Log::info('OpenSearch query: ' . json_encode($query));

            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(3)
                ->withBasicAuth($this->opensearchUser, $this->opensearchPassword)
                ->post("{$this->opensearchHost}/wazuh-monitoring-*/_search", $query);

            \Log::info('OpenSearch response status: ' . $response->status());

            if ($response->successful()) {
                $buckets = $response->json('aggregations.timeline.buckets') ?? [];
                
                \Log::info('OpenSearch buckets count: ' . count($buckets));
                
                // Extract data points from aggregation
                foreach ($buckets as $bucket) {
                    $dataPoints[] = $bucket['unique_agents']['value'] ?? 0;
                }

                // Ensure we have exactly 145 data points (one for each time interval)
                while (count($dataPoints) < 145) {
                    $dataPoints[] = 0;
                }
                $dataPoints = array_slice($dataPoints, -145);

                \Log::info('Agent evolution data retrieved: ' . count($dataPoints) . ' points, max: ' . max($dataPoints));

                return [
                    'labels' => $labels,
                    'data' => $dataPoints
                ];
            }

            \Log::warning('OpenSearch agent evolution query unsuccessful: ' . $response->status());
            \Log::warning('OpenSearch response body: ' . $response->body());
            return $this->getAgentEvolutionFallbackData($labels);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::warning('OpenSearch agent evolution query timeout: ' . $e->getMessage());
            return $this->getAgentEvolutionFallbackData($labels);
        } catch (\Exception $e) {
            \Log::error('OpenSearch agent evolution query failed: ' . $e->getMessage());
            \Log::error('Exception trace: ' . $e->getTraceAsString());
            return $this->getAgentEvolutionFallbackData($labels);
        }
    }

    /**
     * Get agent evolution data for a specific time range
     */
    public function getAgentEvolutionByTimeRange($timeRange = '24h', $agentIds = null)
    {
        $intervals = [
            '15m' => ['range' => '15m', 'interval' => '1m', 'count' => 15],
            '30m' => ['range' => '30m', 'interval' => '1m', 'count' => 30],
            '1h' => ['range' => '1h', 'interval' => '2m', 'count' => 30],
            '24h' => ['range' => '24h', 'interval' => '10m', 'count' => 145],
            '7d' => ['range' => '7d', 'interval' => '1h', 'count' => 168],
            '30d' => ['range' => '30d', 'interval' => '6h', 'count' => 120],
            '90d' => ['range' => '90d', 'interval' => '12h', 'count' => 180],
            '1y' => ['range' => '1y', 'interval' => '1d', 'count' => 365],
            'today' => ['range' => 'now/d', 'interval' => '30m', 'count' => null, 'custom' => true],
            'week' => ['range' => 'now/w', 'interval' => '1h', 'count' => null, 'custom' => true],
        ];

        $config = $intervals[$timeRange] ?? $intervals['24h'];
        $labels = [];
        $dataPoints = [];
        
        try {
            // Generate time labels
            $now = Carbon::now();
            
            if ($timeRange === 'today') {
                // From 00:00 today to now
                $start = $now->copy()->startOfDay();
                $interval = 30; // 30 minutes
                while ($start <= $now) {
                    $labels[] = $start->format('M d H:i');
                    $start->addMinutes($interval);
                }
            } elseif ($timeRange === 'week') {
                // From start of week to now
                $start = $now->copy()->startOfWeek();
                $interval = 60; // 1 hour
                while ($start <= $now) {
                    $labels[] = $start->format('M d H:i');
                    $start->addHours(1);
                }
            } else {
                // Standard time range
                $count = $config['count'];
                preg_match('/(\d+)([mhd])/', $config['interval'], $matches);
                $value = (int)$matches[1];
                $unit = $matches[2];
                
                for ($i = $count - 1; $i >= 0; $i--) {
                    $time = $now->copy();
                    switch ($unit) {
                        case 'm':
                            $time->subMinutes($i * $value);
                            break;
                        case 'h':
                            $time->subHours($i * $value);
                            break;
                        case 'd':
                            $time->subDays($i * $value);
                            break;
                    }
                    
                    if ($timeRange === '1y') {
                        $labels[] = $time->format('M d, Y');
                    } elseif ($timeRange === '90d') {
                        $labels[] = $time->format('M d, Y');
                    } elseif ($timeRange === '30d' || $timeRange === '7d') {
                        $labels[] = $time->format('M d');
                    } elseif ($timeRange === '24h') {
                        $labels[] = $time->format('M d H:i');
                    } else {
                        // 15m, 30m, 1h
                        $labels[] = $time->format('M d H:i');
                    }
                }
            }

            // Build query
            // For custom ranges (today, week), use the range value directly without 'now-' prefix
            $gteValue = (isset($config['custom']) && $config['custom']) 
                ? $config['range'] 
                : 'now-' . $config['range'];
            
            $query = [
                'size' => 0,
                'aggs' => [
                    'timeline' => [
                        'date_histogram' => [
                            'field' => 'timestamp',
                            'fixed_interval' => $config['interval'],
                            'min_doc_count' => 0,
                        ],
                        'aggs' => [
                            'unique_agents' => [
                                'cardinality' => [
                                    'field' => 'agent.id',
                                    'precision_threshold' => 1000
                                ]
                            ]
                        ]
                    ]
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'range' => [
                                    'timestamp' => [
                                        'gte' => $gteValue,
                                        'lte' => 'now'
                                    ]
                                ]
                            ],
                            [
                                'term' => [
                                    'status' => 'active'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            // Add agent ID filter if provided
            if ($agentIds && !empty($agentIds)) {
                $query['query']['bool']['must'][] = [
                    'terms' => [
                        'agent.id' => $agentIds
                    ]
                ];
            }

            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(3)
                ->withBasicAuth($this->opensearchUser, $this->opensearchPassword)
                ->post("{$this->opensearchHost}/wazuh-monitoring-*/_search", $query);

            if ($response->successful()) {
                $buckets = $response->json('aggregations.timeline.buckets') ?? [];
                
                foreach ($buckets as $bucket) {
                    $dataPoints[] = $bucket['unique_agents']['value'] ?? 0;
                }

                // Ensure we have correct number of data points
                $expectedCount = $timeRange === 'today' || $timeRange === 'week' ? count($labels) : $config['count'];
                while (count($dataPoints) < $expectedCount) {
                    $dataPoints[] = 0;
                }
                $dataPoints = array_slice($dataPoints, -$expectedCount);

                return [
                    'labels' => $labels,
                    'data' => $dataPoints
                ];
            }

            return $this->getAgentEvolutionFallbackData($labels);

        } catch (\Exception $e) {
            \Log::warning('OpenSearch agent evolution query failed: ' . $e->getMessage());
            return $this->getAgentEvolutionFallbackData($labels);
        }
    }

    /**
     * Fallback data for agent evolution
     */
    private function getAgentEvolutionFallbackData($labels = [])
    {
        if (empty($labels)) {
            $labels = [];
            $now = Carbon::now();
            for ($i = 144; $i >= 0; $i--) {
                $time = $now->copy()->subMinutes($i * 10);
                $labels[] = $time->format('H:i');
            }
        }

        // Return empty/zero data for all intervals
        $data = array_fill(0, 145, 0);

        return [
            'labels' => $labels,
            'data' => $data
        ];
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
