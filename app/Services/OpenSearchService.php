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
    private $requestCache = [];
    private const CACHE_TTL = 60; // seconds

    public function __construct()
    {
        $this->opensearchHost = env('OPENSEARCH_HOST', 'https://192.168.200.150:9200');
        $this->opensearchUser = env('OPENSEARCH_USER', 'admin');
        $this->opensearchPassword = env('OPENSEARCH_PASSWORD', 'Admin123.');
        
        $this->wazuhHost = env('WAZUH_HOST', 'https://192.168.200.150:55000');
        $this->wazuhUser = env('WAZUH_USER', 'admin');
        $this->wazuhPassword = env('WAZUH_PASSWORD', 'Admin123.');
    }

    /**
     * Get alert counts for the last 7 days
     * @param array $agentIds Optional list of agent IDs to filter by
     */
    public function getAlertTrendLast7Days($agentIds = null)
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
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'timestamp' => [
                                    'gte' => 'now-6d/d',
                                    'lte' => 'now'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        // Add agent filter if provided
        if ($agentIds && !empty($agentIds)) {
            $query['query']['bool']['filter'][] = [
                'terms' => ['agent.id' => $agentIds]
            ];
            \Log::info('Alert trend query with agent filter', [
                'agent_ids' => $agentIds,
                'agent_count' => count($agentIds),
            ]);
        } else {
            \Log::info('Alert trend query - no agent filter (admin or no accessible agents)', [
                'agent_ids' => $agentIds,
            ]);
        }

        try {
            \Log::debug('OpenSearch alert trend query', ['query' => $query]);
            
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
     * @param array $agentIds Optional list of agent IDs to filter by
     */
    public function getAlertSeverityDistribution($agentIds = null)
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
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'timestamp' => [
                                    'gte' => 'now-7d'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        // Add agent filter if provided
        if ($agentIds && !empty($agentIds)) {
            $query['query']['bool']['filter'][] = [
                'terms' => ['agent.id' => $agentIds]
            ];
        }

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
     * @param array $agentIds Optional list of agent IDs to filter by
     */
    public function getTotalAlertCount($agentIds = null)
    {
        $severity = $this->getAlertSeverityDistribution($agentIds);
        return array_sum($severity);
    }

    /**
     * Get agent evolution data for a specific time range
     * @param string $timeRange Time range identifier (15m, 30m, 1h, 24h, 7d, 30d, 90d, 1y, today, week)
     * @param array $agentIds Optional list of agent IDs to filter by
     * @param Carbon $baseTime Optional fixed timestamp to use as 'now' for consistent results
     * @return array Array with 'labels' and 'data' keys containing the evolution data
     */
    public function getAgentEvolutionByTimeRange($timeRange = '24h', $agentIds = null, $baseTime = null)
    {
        // Generate cache key
        $cacheKey = 'agent_evolution_' . $timeRange . '_' . md5(json_encode($agentIds ?? []));
        
        // Check cache
        if (isset($this->requestCache[$cacheKey])) {
            $cached = $this->requestCache[$cacheKey];
            if (time() - $cached['timestamp'] < self::CACHE_TTL) {
                \Log::info('[AgentEvolution] Using cached result', ['time_range' => $timeRange]);
                return $cached['data'];
            }
        }

        $intervals = [
            '15m'   => ['interval' => '3m',  'duration' => 15],
            '30m'   => ['interval' => '5m',  'duration' => 30],
            '1h'    => ['interval' => '10m', 'duration' => 60],
            '24h'   => ['interval' => '1h',  'duration' => 1440],
            '7d'    => ['interval' => '12h',  'duration' => 10080],
            '30d'   => ['interval' => '1d',  'duration' => 43200],
            '90d'   => ['interval' => '1d',  'duration' => 129600],
            '1y'    => ['interval' => '1d',  'duration' => 525600],
            'today' => ['interval' => '1h'],
            'week'  => ['interval' => '12h'],
        ];

        $config = $intervals[$timeRange] ?? $intervals['24h'];
        $labels = [];

        try {
            // Use provided baseTime or current time (ensures fixed window)
            $now      = $baseTime ?? Carbon::now();
            $timezone = 'Asia/Jakarta';

        $endDate = $now->copy()->toIso8601String();
        if ($timeRange === 'today') {
            $startDate = $now->copy()->startOfDay()->toIso8601String();
        } elseif ($timeRange === 'week') {
            $startDate = $now->copy()->startOfWeek()->toIso8601String();
        } else {
            $startDate = $now->copy()->subMinutes($config['duration'])->toIso8601String();
        }

        $this->generateTimeLabels($timeRange, $config['interval'], $labels, $now);

        $query = [
            '_source'        => ['excludes' => []],
            'size'           => 0,
            'stored_fields'  => ['*'],
            'script_fields'  => (object)[],
            'docvalue_fields' => [
                ['field' => 'timestamp', 'format' => 'date_time']
            ],
            'aggs' => [
                '2' => [
                    'date_histogram' => [
                        'field'          => 'timestamp',
                        'fixed_interval' => $config['interval'],
                        'time_zone'      => $timezone,
                        'min_doc_count'  => 1,
                    ],
                    'aggs' => [
                        '3' => [
                            'terms' => [
                                'field' => 'status',
                                'order' => ['_term' => 'desc'],
                                'size'  => 5,
                            ],
                            'aggs' => [
                                '4' => [
                                    'cardinality' => ['field' => 'id']
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'query' => [
                'bool' => [
                    'must'   => [['match_all' => (object)[]]],
                    'filter' => [
                        [
                            'bool' => [
                                'should' => [
                                    ['term' => ['manager.keyword' => env('WAZUH_MANAGER_NAME', 'fadli')]]
                                ]
                            ]
                        ],
                        [
                            'range' => [
                                'timestamp' => [
                                    'gte'    => $startDate,
                                    'lte'    => $endDate,
                                    'format' => 'strict_date_optional_time'
                                ]
                            ]
                        ]
                    ],
                    'should'   => [],
                    'must_not' => []
                ]
            ],
        ];

        if ($agentIds && !empty($agentIds)) {
            $query['query']['bool']['filter'][] = [
                'terms' => ['id' => $agentIds]
            ];
        }

        \Log::info('[AgentEvolution] Sending query to OpenSearch', [
            'index'      => 'wazuh-monitoring-*',
            'time_range' => $timeRange,
            'interval'   => $config['interval'],
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'agent_ids'  => $agentIds,
            'query'      => json_encode($query, JSON_PRETTY_PRINT),
        ]);

        $response = Http::withoutVerifying()
            ->connectTimeout(5)
            ->timeout(10)
            ->withBasicAuth($this->opensearchUser, $this->opensearchPassword)
            ->post("{$this->opensearchHost}/wazuh-monitoring-*/_search", $query);

        \Log::info('[AgentEvolution] Raw response from OpenSearch', [
            'http_status' => $response->status(),
            'body'        => $response->body(),
        ]);

        if (!$response->successful()) {
            \Log::error('[AgentEvolution] OpenSearch returned non-200', [
                'http_status' => $response->status(),
                'body'        => $response->body(),
            ]);
            return $this->getAgentEvolutionFallbackData($labels);
        }

        $json        = $response->json();
        $timeBuckets = $json['aggregations']['2']['buckets'] ?? [];
        $totalHits   = $json['hits']['total']['value'] ?? 0;

        \Log::info('[AgentEvolution] Aggregation result', [
            'total_hits'    => $totalHits,
            'bucket_count'  => count($timeBuckets),
            'raw_buckets'   => json_encode($timeBuckets, JSON_PRETTY_PRINT),
        ]);

        $result = [
            'labels' => [],
            'data'   => ['active' => [], 'disconnected' => [], 'never_connected' => [], 'pending' => []]
        ];

        foreach ($timeBuckets as $tb) {
            // Use bucket timestamp directly as label with date information
            $bucketTime = Carbon::createFromTimestampMs($tb['key'])
                ->setTimezone('Asia/Jakarta');
            
            // Format based on time range for better clarity
            $label = match($timeRange) {
                '15m', '30m', '1h' => $bucketTime->format('H:i'),              // HH:MM for short ranges
                '24h', 'today' => $bucketTime->format('M d H:i'),              // MMM DD HH:MM for daily
                '7d', 'week' => $bucketTime->format('M d'),                    // MMM DD for weekly
                default => $bucketTime->format('M d Y'),                        // MMM DD YYYY for monthly+
            };

            $result['labels'][] = $label;

            // Initialize all statuses with 0 (ensures consistency)
            $statusCounts = ['active' => 0, 'disconnected' => 0, 'never_connected' => 0, 'pending' => 0];
            
            // Fill in actual counts from response
            foreach ($tb['3']['buckets'] ?? [] as $sb) {
                $status = $sb['key'] ?? '';
                if (isset($statusCounts[$status])) {
                    $statusCounts[$status] = $sb['4']['value'] ?? 0;
                }
            }

            // Append all statuses to result (ensures complete data structure)
            $result['data']['active'][]          = $statusCounts['active'];
            $result['data']['disconnected'][]    = $statusCounts['disconnected'];
            $result['data']['never_connected'][] = $statusCounts['never_connected'];
            $result['data']['pending'][]         = $statusCounts['pending'];
        }

        // Ensure data points match label count
        $expectedCount = count($labels);
        foreach ($result['data'] as $status => &$points) {
            // Pad with zeros if needed
            while (count($points) < $expectedCount) {
                $points[] = 0;
            }
            // Trim to expected count
            $points = array_slice($points, -$expectedCount);
        }
        unset($points); // Break reference

        \Log::info('[AgentEvolution] Final result', [
            'time_range'            => $timeRange,
            'base_time'             => $now->toIso8601String(),
            'labels_count'          => count($result['labels']),
            'active_points'         => count($result['data']['active']),
            'disconnected_points'   => count($result['data']['disconnected']),
            'never_connected_points'=> count($result['data']['never_connected']),
            'pending_points'        => count($result['data']['pending']),
        ]);

        // Store in cache before returning
        $this->requestCache[$cacheKey] = [
            'data' => $result,
            'timestamp' => time()
        ];

        return $result;

    } catch (\Exception $e) {
        \Log::error('[AgentEvolution] Exception', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return $this->getAgentEvolutionFallbackData($labels);
    }
}

    /**
     * Generate time labels based on time range and interval
     * @param string $timeRange The time range identifier
     * @param string $interval The interval for bucketing (e.g., '1h', '1d')
     * @param array &$labels Reference to labels array to populate
     * @param Carbon $baseTime The base time to use (defaults to now)
     */
    private function generateTimeLabels($timeRange, $interval, &$labels, $baseTime = null)
    {
        $now = $baseTime ?? Carbon::now();
        $intervals = [];
        
        // Parse interval string (e.g., "30m", "1h", "12h", "1d")
        preg_match('/(\d+)([mhd])/', $interval, $matches);
        if (!$matches) {
            return;
        }
        
        $value = (int)$matches[1];
        $unit = $matches[2];
        
        if ($timeRange === 'today') {
            $start = $now->copy()->startOfDay();
            $end = $now;
            $step = fn($dt) => match($unit) {
                'm' => $dt->addMinutes($value),
                'h' => $dt->addHours($value),
                'd' => $dt->addDays($value),
            };
        } elseif ($timeRange === 'week') {
            $start = $now->copy()->startOfWeek();
            $end = $now;
            $step = fn($dt) => match($unit) {
                'm' => $dt->addMinutes($value),
                'h' => $dt->addHours($value),
                'd' => $dt->addDays($value),
            };
        } else {
            // Standard ranges
            $start = $now->copy();
            switch ($unit) {
                case 'm':
                    preg_match('/(\d+)m/', $interval, $m);
                    $start->subMinutes((int)$m[1] * 144); // Approximate for standard intervals
                    break;
                case 'h':
                    preg_match('/(\d+)h/', $interval, $m);
                    $start->subHours((int)$m[1] * 24);
                    break;
                case 'd':
                    preg_match('/(\d+)d/', $interval, $m);
                    $start->subDays((int)$m[1] * 30);
                    break;
            }
            $end = $now;
            $step = fn($dt) => match($unit) {
                'm' => $dt->addMinutes($value),
                'h' => $dt->addHours($value),
                'd' => $dt->addDays($value),
            };
        }
        
        $current = $start;
        while ($current <= $end) {
            $labels[] = $current->format('M d H:i');
            $step($current);
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
    public function getOsDistribution($agentIds = null)
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

            // Filter agents if specified
            if ($agentIds && !empty($agentIds)) {
                $agents = array_filter($agents, fn($agent) => in_array($agent['id'] ?? null, $agentIds));
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
    public function getTopTriggeredRules($limit = 5, $agentIds = null)
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
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'timestamp' => [
                                    'gte' => 'now-7d'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        // Add agent filter if provided
        if ($agentIds && !empty($agentIds)) {
            $query['query']['bool']['filter'][] = [
                'terms' => ['agent.id' => $agentIds]
            ];
        }

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
    public function getTopAgentsByAlerts($limit = 5, $agentIds = null)
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
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'timestamp' => [
                                    'gte' => 'now-7d'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        // Add agent filter if provided
        if ($agentIds && !empty($agentIds)) {
            $query['query']['bool']['filter'][] = [
                'terms' => ['agent.id' => $agentIds]
            ];
        }

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

    /**
     * Get FIM (File Integrity Monitoring) events for an agent
     */
    public function getFimEvents($agentId, $limit = 5)
    {
        $query = [
            'size' => $limit,
            'sort' => [['timestamp' => ['order' => 'desc']]],
            'query' => [
                'bool' => [
                    'must' => [
                        ['term' => ['agent.id' => $agentId]],
                        ['match' => ['rule.groups' => 'syscheck']]
                    ],
                    'filter' => [
                        ['range' => ['timestamp' => ['gte' => 'now-24h']]]
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(5)
                ->withBasicAuth($this->opensearchUser, $this->opensearchPassword)
                ->post("{$this->opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                $hits = $response->json('hits.hits') ?? [];
                $events = [];
                
                foreach ($hits as $hit) {
                    $source = $hit['_source'] ?? [];
                    $events[] = [
                        'timestamp' => $source['timestamp'] ?? '',
                        'path' => $source['syscheck']['path'] ?? $source['file']['path'] ?? 'Unknown',
                        'action' => $source['syscheck']['event'] ?? 'unknown',
                        'description' => $source['rule']['description'] ?? 'Unknown',
                        'level' => $source['rule']['level'] ?? 0,
                        'rule_id' => $source['rule']['id'] ?? '',
                    ];
                }
                
                return $events;
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch FIM events: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get events count evolution for an agent
     */
    public function getEventsCountEvolution($agentId, $timeRange = '24h')
    {
        $config = match($timeRange) {
            '24h' => ['interval' => '1h', 'duration' => 1440],
            '7d' => ['interval' => '6h', 'duration' => 10080],
            '30d' => ['interval' => '1d', 'duration' => 43200],
            default => ['interval' => '1h', 'duration' => 1440],
        };

        $query = [
            'size' => 0,
            'aggs' => [
                'events_by_time' => [
                    'date_histogram' => [
                        'field' => 'timestamp',
                        'fixed_interval' => $config['interval'],
                        'min_doc_count' => 0,
                    ]
                ]
            ],
            'query' => [
                'bool' => [
                    'must' => [['term' => ['agent.id' => $agentId]]],
                    'filter' => [
                        ['range' => ['timestamp' => ['gte' => "now-{$timeRange}"]]]
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(5)
                ->withBasicAuth($this->opensearchUser, $this->opensearchPassword)
                ->post("{$this->opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                $buckets = $response->json('aggregations.events_by_time.buckets') ?? [];
                $labels = [];
                $data = [];

                foreach ($buckets as $bucket) {
                    $time = Carbon::createFromTimestampMs($bucket['key']);
                    $labels[] = $time->format('H:i');
                    $data[] = $bucket['doc_count'];
                }

                return ['labels' => $labels, 'data' => $data];
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch events count evolution: ' . $e->getMessage());
        }

        return ['labels' => [], 'data' => []];
    }

    /**
     * Get alert statistics for an agent
     */
    public function getAgentAlertStats($agentId)
    {
        $query = [
            'size' => 0,
            'aggs' => [
                'alert_levels' => [
                    'terms' => [
                        'field' => 'rule.level',
                        'size' => 20,
                    ]
                ]
            ],
            'query' => [
                'bool' => [
                    'must' => [['term' => ['agent.id' => $agentId]]],
                    'filter' => [
                        ['range' => ['timestamp' => ['gte' => 'now-24h']]]
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(5)
                ->withBasicAuth($this->opensearchUser, $this->opensearchPassword)
                ->post("{$this->opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                $buckets = $response->json('aggregations.alert_levels.buckets') ?? [];
                $stats = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];

                foreach ($buckets as $bucket) {
                    $level = $bucket['key'];
                    $count = $bucket['doc_count'];

                    if ($level >= 12) {
                        $stats['critical'] += $count;
                    } elseif ($level >= 9) {
                        $stats['high'] += $count;
                    } elseif ($level >= 6) {
                        $stats['medium'] += $count;
                    } else {
                        $stats['low'] += $count;
                    }
                }

                return $stats;
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch agent alert stats: ' . $e->getMessage());
        }

        return ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];
    }

    /**
     * Get compliance data for an agent (GDPR, PCI-DSS, etc.)
     */
    public function getAgentCompliance($agentId, $complianceType = 'gdpr', $timeRange = '30d')
    {
        $managerName = env('WAZUH_MANAGER_NAME', 'fadli');
        
        $query = [
            'size' => 0,
            'aggs' => [
                'top_compliance' => [
                    'terms' => [
                        'field' => "rule.{$complianceType}",
                        'size' => 10,
                    ]
                ]
            ],
            'query' => [
                'bool' => [
                    'must' => [],
                    'filter' => [
                        ['match_all' => (object)[]],
                        ['match_phrase' => ['manager.name' => $managerName]],
                        ['match_phrase' => ['agent.id' => $agentId]],
                        ['exists' => ['field' => "rule.{$complianceType}"]],
                        ['range' => ['timestamp' => ['from' => "now-{$timeRange}", 'to' => 'now']]]
                    ],
                    'should' => [],
                    'must_not' => []
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(5)
                ->withBasicAuth($this->opensearchUser, $this->opensearchPassword)
                ->post("{$this->opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                $buckets = $response->json('aggregations.top_compliance.buckets') ?? [];
                $complianceData = [];

                foreach ($buckets as $bucket) {
                    $complianceData[] = [
                        'name' => $bucket['key'],
                        'count' => $bucket['doc_count'],
                    ];
                }

                \Log::info("Compliance {$complianceType} data for agent {$agentId}: " . count($complianceData) . ' items');
                return $complianceData;
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::warning("Compliance query timeout for agent {$agentId}: " . $e->getMessage());
        } catch (\Exception $e) {
            \Log::warning("Failed to fetch compliance data for agent {$agentId}: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Get events count evolution filtered by compliance type for an agent
     */
    public function getEventsCountEvolutionByCompliance($agentId, $complianceType = 'gdpr', $timeRange = '24h')
    {
        $config = [
            '24h' => ['interval' => '1h', 'duration' => 1440],
            '7d' => ['interval' => '12h', 'duration' => 10080],
            '30d' => ['interval' => '1d', 'duration' => 43200],
        ];

        $timeConfig = $config[$timeRange] ?? $config['24h'];
        $labels = [];
        $now = Carbon::now();
        $endDate = $now->copy()->toIso8601String();
        $startDate = $now->copy()->subMinutes($timeConfig['duration'])->toIso8601String();

        $this->generateTimeLabels($timeRange, $timeConfig['interval'], $labels, $now);

        $query = [
            'size' => 0,
            'aggs' => [
                'events_by_time' => [
                    'date_histogram' => [
                        'field' => 'timestamp',
                        'fixed_interval' => $timeConfig['interval'],
                        'time_zone' => 'Asia/Jakarta',
                        'min_doc_count' => 0,
                    ]
                ]
            ],
            'query' => [
                'bool' => [
                    'must' => [['term' => ['agent.id' => $agentId]]],
                    'filter' => [
                        ['exists' => ['field' => "rule.{$complianceType}"]],
                        ['range' => [
                            'timestamp' => [
                                'gte' => $startDate,
                                'lte' => $endDate,
                                'format' => 'strict_date_optional_time'
                            ]
                        ]]
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(5)
                ->withBasicAuth($this->opensearchUser, $this->opensearchPassword)
                ->post("{$this->opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                $buckets = $response->json('aggregations.events_by_time.buckets') ?? [];
                $data = [];

                foreach ($buckets as $bucket) {
                    $data[] = $bucket['doc_count'];
                }

                \Log::info("Evolution data for compliance {$complianceType} agent {$agentId}: " . count($data) . ' time buckets');
                
                // If no data, return same structure as request for consistency
                if (empty($data)) {
                    $data = array_fill(0, count($labels), 0);
                }

                return [
                    'labels' => $labels,
                    'data' => $data
                ];
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::warning("Events evolution query timeout for compliance {$complianceType}: " . $e->getMessage());
        } catch (\Exception $e) {
            \Log::warning("Failed to fetch events evolution for compliance {$complianceType}: " . $e->getMessage());
        }

        return ['labels' => $labels, 'data' => array_fill(0, count($labels), 0)];
    }
}
