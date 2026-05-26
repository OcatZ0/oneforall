<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class OpenSearchService
{
    private $_opensearchHost;
    private $_opensearchUser;
    private $_opensearchPassword;
    private $_wazuhHost;
    private $_wazuhUser;
    private $_wazuhPassword;
    private $_requestCache = [];
    private const CACHE_TTL = 60;

    public function __construct()
    {
        $this->_opensearchHost     = config('opensearch.host');
        $this->_opensearchUser     = config('opensearch.user');
        $this->_opensearchPassword = config('opensearch.password');

        $this->_wazuhHost     = config('wazuh.host');
        $this->_wazuhUser     = config('wazuh.user');
        $this->_wazuhPassword = config('wazuh.password');
    }

    public function getAlertTrendLast7Days($agentIds = null, $isAdmin = true)
    {
        if (!$isAdmin && empty($agentIds)) {
            return [];
        }

        if (is_array($agentIds) && !empty($agentIds)) {
            $agentIds = array_map('strval', $agentIds);
        }

        $query = [
            'size' => 0,
            'aggs' => [
                'alerts_by_day' => [
                    'date_histogram' => [
                        'field'          => 'timestamp',
                        'fixed_interval' => '1d',
                        'min_doc_count'  => 0,
                    ]
                ]
            ],
            'query' => [
                'bool' => [
                    'filter' => [
                        ['range' => ['timestamp' => ['gte' => 'now-6d/d', 'lte' => 'now']]]
                    ],
                    'must_not' => [
                        ['term' => ['agent.id' => '000']]
                    ]
                ]
            ]
        ];

        if (!$isAdmin && is_array($agentIds) && !empty($agentIds)) {
            $query['query']['bool']['filter'][] = ['terms' => ['agent.id' => $agentIds]];
        }

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(2)->timeout(2)
                ->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)
                ->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                $buckets = $response->json('aggregations.alerts_by_day.buckets') ?? [];
                $trend   = array_map(fn($b) => $b['doc_count'], $buckets);
                return $trend ?: [0, 0, 0, 0, 0, 0, 0];
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::warning('OpenSearch alert trend timeout: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('OpenSearch alert trend failed: ' . $e->getMessage());
        }

        return [0, 0, 0, 0, 0, 0, 0];
    }

    public function getAlertSeverityDistribution($agentIds = null, $isAdmin = true)
    {
        $empty = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];

        if (!$isAdmin && empty($agentIds)) {
            return $empty;
        }

        if (is_array($agentIds) && !empty($agentIds)) {
            $agentIds = array_map('strval', $agentIds);
        }

        $query = [
            'size' => 0,
            'aggs' => ['severity_counts' => ['terms' => ['field' => 'rule.level', 'size' => 20]]],
            'query' => [
                'bool' => [
                    'filter'   => [['range' => ['timestamp' => ['gte' => 'now-7d']]]],
                    'must_not' => [['term' => ['agent.id' => '000']]]
                ]
            ]
        ];

        if (!$isAdmin && is_array($agentIds) && !empty($agentIds)) {
            $query['query']['bool']['filter'][] = ['terms' => ['agent.id' => $agentIds]];
        }

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(2)->timeout(2)
                ->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)
                ->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                return $this->parseSeverityResponse($response->json());
            }
        } catch (\Exception $e) {
            \Log::warning('OpenSearch severity query failed: ' . $e->getMessage());
        }

        return $empty;
    }

    public function getTotalAlertCount($agentIds = null, $isAdmin = true)
    {
        return array_sum($this->getAlertSeverityDistribution($agentIds, $isAdmin));
    }

    public function getAgentEvolutionByTimeRange($timeRange = '24h', $agentIds = null, $baseTime = null, $isAdmin = true)
    {
        if (!$isAdmin && empty($agentIds)) {
            return ['labels' => [], 'data' => []];
        }

        $cacheKey = 'agent_evolution_' . $timeRange . '_' . md5(json_encode($agentIds ?? []));
        if (isset($this->_requestCache[$cacheKey])) {
            $cached = $this->_requestCache[$cacheKey];
            if (time() - $cached['timestamp'] < self::CACHE_TTL) {
                return $cached['data'];
            }
        }

        $intervals = [
            '15m'   => ['interval' => '3m',  'duration' => 15],
            '30m'   => ['interval' => '5m',  'duration' => 30],
            '1h'    => ['interval' => '10m', 'duration' => 60],
            '24h'   => ['interval' => '1h',  'duration' => 1440],
            '7d'    => ['interval' => '12h', 'duration' => 10080],
            '30d'   => ['interval' => '1d',  'duration' => 43200],
            '90d'   => ['interval' => '1d',  'duration' => 129600],
            '1y'    => ['interval' => '1d',  'duration' => 525600],
            'today' => ['interval' => '1h'],
            'week'  => ['interval' => '12h'],
        ];

        $config = $intervals[$timeRange] ?? $intervals['24h'];
        $labels = [];

        try {
            $now      = $baseTime ?? Carbon::now();
            $timezone = 'Asia/Jakarta';
            $endDate  = $now->copy()->toIso8601String();

            if ($timeRange === 'today') {
                $startDate = $now->copy()->startOfDay()->toIso8601String();
            } elseif ($timeRange === 'week') {
                $startDate = $now->copy()->startOfWeek()->toIso8601String();
            } else {
                $startDate = $now->copy()->subMinutes($config['duration'])->toIso8601String();
            }

            $this->generateTimeLabels($timeRange, $config['interval'], $labels, $now);

            $query = [
                '_source'         => ['excludes' => []],
                'size'            => 0,
                'stored_fields'   => ['*'],
                'script_fields'   => (object)[],
                'docvalue_fields' => [['field' => 'timestamp', 'format' => 'date_time']],
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
                                'aggs' => ['4' => ['cardinality' => ['field' => 'id']]]
                            ]
                        ]
                    ]
                ],
                'query' => [
                    'bool' => [
                        'must'   => [['match_all' => (object)[]]],
                        'filter' => [
                            ['bool' => ['should' => [['term' => ['manager.keyword' => env('WAZUH_MANAGER_NAME', 'fadli')]]]]],
                            ['range' => ['timestamp' => ['gte' => $startDate, 'lte' => $endDate, 'format' => 'strict_date_optional_time']]]
                        ],
                        'should'   => [],
                        'must_not' => [['term' => ['id' => '000']]]
                    ]
                ],
            ];

            if ($agentIds && !empty($agentIds)) {
                $agentIds = array_map('strval', $agentIds);
                $query['query']['bool']['filter'][] = ['terms' => ['id' => $agentIds]];
            }

            $response = Http::withoutVerifying()
                ->connectTimeout(5)->timeout(10)
                ->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)
                ->post("{$this->_opensearchHost}/wazuh-monitoring-*/_search", $query);

            if (!$response->successful()) {
                return $this->getAgentEvolutionFallbackData($labels);
            }

            $timeBuckets = $response->json('aggregations.2.buckets') ?? [];
            $result      = [
                'labels' => [],
                'data'   => ['active' => [], 'disconnected' => [], 'never_connected' => [], 'pending' => []]
            ];

            foreach ($timeBuckets as $tb) {
                $bucketTime = Carbon::createFromTimestampMs($tb['key'])->setTimezone('Asia/Jakarta');
                $result['labels'][] = match($timeRange) {
                    '15m', '30m', '1h'  => $bucketTime->format('H:i'),
                    '24h', 'today'      => $bucketTime->format('M d H:i'),
                    '7d', 'week'        => $bucketTime->format('M d'),
                    default             => $bucketTime->format('M d Y'),
                };

                $statusCounts = ['active' => 0, 'disconnected' => 0, 'never_connected' => 0, 'pending' => 0];
                foreach ($tb['3']['buckets'] ?? [] as $sb) {
                    $status = $sb['key'] ?? '';
                    if (isset($statusCounts[$status])) {
                        $statusCounts[$status] = $sb['4']['value'] ?? 0;
                    }
                }

                $result['data']['active'][]          = $statusCounts['active'];
                $result['data']['disconnected'][]    = $statusCounts['disconnected'];
                $result['data']['never_connected'][] = $statusCounts['never_connected'];
                $result['data']['pending'][]         = $statusCounts['pending'];
            }

            $expectedCount = count($labels);
            foreach ($result['data'] as &$points) {
                while (count($points) < $expectedCount) $points[] = 0;
                $points = array_slice($points, -$expectedCount);
            }
            unset($points);

            $this->_requestCache[$cacheKey] = ['data' => $result, 'timestamp' => time()];
            return $result;

        } catch (\Exception $e) {
            \Log::error('[AgentEvolution] Exception: ' . $e->getMessage());
            return $this->getAgentEvolutionFallbackData($labels);
        }
    }

    public function getOsDistribution($agentIds = null, $isAdmin = true)
    {
        try {
            if (!$isAdmin && empty($agentIds)) return [];

            if (is_array($agentIds) && !empty($agentIds)) {
                $agentIds = array_map('strval', $agentIds);
            }

            $tokenResponse = Http::withoutVerifying()
                ->connectTimeout(2)->timeout(2)
                ->withBasicAuth($this->_wazuhUser, $this->_wazuhPassword)
                ->post("{$this->_wazuhHost}/security/user/authenticate");

            if (!$tokenResponse->successful()) return [];

            $token = $tokenResponse->json('data.token');

            $agentsResponse = Http::withoutVerifying()
                ->connectTimeout(2)->timeout(2)
                ->withToken($token)
                ->get("{$this->_wazuhHost}/agents", ['limit' => 500, 'select' => 'id,name,os.name']);

            if (!$agentsResponse->successful()) return [];

            $agents = $agentsResponse->json('data.affected_items') ?? [];

            if (!$isAdmin && is_array($agentIds) && !empty($agentIds)) {
                $agents = array_filter($agents, fn($a) => in_array((string)($a['id'] ?? null), $agentIds, true) && ($a['id'] ?? null) !== '000');
            } else {
                $agents = array_filter($agents, fn($a) => ($a['id'] ?? null) !== '000');
            }

            $osDistribution = [];
            foreach ($agents as $agent) {
                if (isset($agent['os']['name'])) {
                    $osName = $agent['os']['name'];
                    $osDistribution[$osName] = ($osDistribution[$osName] ?? 0) + 1;
                }
            }

            return $osDistribution ?: [];

        } catch (\Exception $e) {
            \Log::warning('OS distribution query failed: ' . $e->getMessage());
            return [];
        }
    }

    public function getTopTriggeredRules($limit = 5, $agentIds = null, $isAdmin = true)
    {
        if (!$isAdmin && empty($agentIds)) return [];

        if (is_array($agentIds) && !empty($agentIds)) {
            $agentIds = array_map('strval', $agentIds);
        }

        $query = [
            'size' => 0,
            'aggs' => [
                'top_rules' => [
                    'terms' => ['field' => 'rule.id', 'size' => $limit],
                    'aggs'  => [
                        'rule_description' => ['terms' => ['field' => 'rule.description', 'size' => 1]],
                        'rule_level'       => ['terms' => ['field' => 'rule.level', 'size' => 1]],
                    ]
                ]
            ],
            'query' => [
                'bool' => [
                    'filter'   => [['range' => ['timestamp' => ['gte' => 'now-7d']]]],
                    'must_not' => [['term' => ['agent.id' => '000']]]
                ]
            ]
        ];

        if (!$isAdmin && is_array($agentIds) && !empty($agentIds)) {
            $query['query']['bool']['filter'][] = ['terms' => ['agent.id' => $agentIds]];
        }

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(2)->timeout(2)
                ->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)
                ->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                $buckets = $response->json('aggregations.top_rules.buckets') ?? [];
                return array_map(fn($b) => [
                    'id'          => $b['key'] ?? '',
                    'description' => $b['rule_description']['buckets'][0]['key'] ?? 'Unknown',
                    'level'       => $b['rule_level']['buckets'][0]['key'] ?? 0,
                    'count'       => $b['doc_count'] ?? 0,
                ], $buckets);
            }
        } catch (\Exception $e) {
            \Log::warning('OpenSearch top rules failed: ' . $e->getMessage());
        }

        return [];
    }

    public function getTopAgentsByAlerts($limit = 5, $agentIds = null, $isAdmin = true)
    {
        if (!$isAdmin && empty($agentIds)) return [];

        if (is_array($agentIds) && !empty($agentIds)) {
            $agentIds = array_map('strval', $agentIds);
        }

        $query = [
            'size' => 0,
            'aggs' => [
                'top_agents' => [
                    'terms' => ['field' => 'agent.id', 'size' => $limit],
                    'aggs'  => [
                        'agent_name' => ['terms' => ['field' => 'agent.name',    'size' => 1]],
                        'agent_ip'   => ['terms' => ['field' => 'agent.ip',      'size' => 1]],
                        'agent_os'   => ['terms' => ['field' => 'agent.os.name', 'size' => 1]],
                    ]
                ]
            ],
            'query' => [
                'bool' => [
                    'filter'   => [['range' => ['timestamp' => ['gte' => 'now-7d']]]],
                    'must_not' => [['term' => ['agent.id' => '000']]]
                ]
            ]
        ];

        if (!$isAdmin && is_array($agentIds) && !empty($agentIds)) {
            $query['query']['bool']['filter'][] = ['terms' => ['agent.id' => $agentIds]];
        }

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(2)->timeout(2)
                ->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)
                ->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                $buckets = $response->json('aggregations.top_agents.buckets') ?? [];
                return array_map(fn($b) => [
                    'id'          => $b['key'] ?? '',
                    'name'        => $b['agent_name']['buckets'][0]['key'] ?? 'Unknown',
                    'ip'          => $b['agent_ip']['buckets'][0]['key']   ?? 'N/A',
                    'os'          => $b['agent_os']['buckets'][0]['key']   ?? 'Unknown',
                    'alert_count' => $b['doc_count'] ?? 0,
                ], $buckets);
            }
        } catch (\Exception $e) {
            \Log::warning('OpenSearch top agents failed: ' . $e->getMessage());
        }

        return [];
    }

    public function getFimEvents($agentId, $limit = 5)
    {
        $query = [
            'size' => $limit,
            'sort' => [['timestamp' => ['order' => 'desc']]],
            'query' => [
                'bool' => [
                    'must'   => [['term' => ['agent.id' => $agentId]], ['match' => ['rule.groups' => 'syscheck']]],
                    'filter' => [['range' => ['timestamp' => ['gte' => 'now-24h']]]]
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(3)->timeout(5)
                ->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)
                ->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                return array_map(function ($hit) {
                    $source = $hit['_source'] ?? [];
                    return [
                        'timestamp'   => $source['timestamp'] ?? '',
                        'path'        => $source['syscheck']['path'] ?? $source['file']['path'] ?? 'Unknown',
                        'action'      => $source['syscheck']['event'] ?? 'unknown',
                        'description' => $source['rule']['description'] ?? 'Unknown',
                        'level'       => $source['rule']['level'] ?? 0,
                        'rule_id'     => $source['rule']['id'] ?? '',
                    ];
                }, $response->json('hits.hits') ?? []);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch FIM events: ' . $e->getMessage());
        }

        return [];
    }

    public function getEventsCountEvolution($agentId, $timeRange = '24h')
    {
        $config = match($timeRange) {
            '24h'   => ['interval' => '1h', 'duration' => 1440],
            '7d'    => ['interval' => '6h', 'duration' => 10080],
            '30d'   => ['interval' => '1d', 'duration' => 43200],
            default => ['interval' => '1h', 'duration' => 1440],
        };

        $query = [
            'size' => 0,
            'aggs' => ['events_by_time' => ['date_histogram' => ['field' => 'timestamp', 'fixed_interval' => $config['interval'], 'min_doc_count' => 0]]],
            'query' => [
                'bool' => [
                    'must'   => [['term' => ['agent.id' => $agentId]]],
                    'filter' => [['range' => ['timestamp' => ['gte' => "now-{$timeRange}"]]]]
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(3)->timeout(5)
                ->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)
                ->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                $buckets = $response->json('aggregations.events_by_time.buckets') ?? [];
                $labels  = [];
                $data    = [];
                foreach ($buckets as $bucket) {
                    $labels[] = Carbon::createFromTimestampMs($bucket['key'])->format('H:i');
                    $data[]   = $bucket['doc_count'];
                }
                return ['labels' => $labels, 'data' => $data];
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch events count evolution: ' . $e->getMessage());
        }

        return ['labels' => [], 'data' => []];
    }

    public function getAgentAlertStats($agentId)
    {
        $query = [
            'size' => 0,
            'aggs' => ['alert_levels' => ['terms' => ['field' => 'rule.level', 'size' => 20]]],
            'query' => [
                'bool' => [
                    'must'   => [['term' => ['agent.id' => $agentId]]],
                    'filter' => [['range' => ['timestamp' => ['gte' => 'now-24h']]]]
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(3)->timeout(5)
                ->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)
                ->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                $buckets = $response->json('aggregations.alert_levels.buckets') ?? [];
                $stats   = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];
                foreach ($buckets as $bucket) {
                    $level = $bucket['key'];
                    $count = $bucket['doc_count'];
                    if ($level >= 12)      $stats['critical'] += $count;
                    elseif ($level >= 9)   $stats['high']     += $count;
                    elseif ($level >= 6)   $stats['medium']   += $count;
                    else                   $stats['low']       += $count;
                }
                return $stats;
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch agent alert stats: ' . $e->getMessage());
        }

        return ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];
    }

    public function getAgentCompliance($agentId, $complianceType = 'gdpr', $timeRange = '30d')
    {
        $managerName = env('WAZUH_MANAGER_NAME', 'ofa');

        $from = match($timeRange) {
            'today' => 'now/d',
            'week'  => 'now/w',
            default => "now-{$timeRange}",
        };

        $query = [
            'size' => 0,
            'aggs' => ['top_compliance' => ['terms' => ['field' => "rule.{$complianceType}", 'size' => 10]]],
            'query' => [
                'bool' => [
                    'filter' => [
                        ['match_all' => (object)[]],
                        ['match_phrase' => ['manager.name' => $managerName]],
                        ['match_phrase' => ['agent.id' => $agentId]],
                        ['exists' => ['field' => "rule.{$complianceType}"]],
                        ['range' => ['timestamp' => ['from' => $from, 'to' => 'now']]]
                    ],
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(3)->timeout(5)
                ->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)
                ->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                $buckets = $response->json('aggregations.top_compliance.buckets') ?? [];
                return array_map(fn($b) => ['name' => $b['key'], 'count' => $b['doc_count']], $buckets);
            }
        } catch (\Exception $e) {
            \Log::warning("Compliance query failed for agent {$agentId}: " . $e->getMessage());
        }

        return [];
    }

    public function getEventsCountEvolutionByCompliance($agentId, $complianceType = 'gdpr', $timeRange = '24h')
    {
        $configs    = ['24h' => ['interval' => '1h', 'duration' => 1440], '7d' => ['interval' => '12h', 'duration' => 10080], '30d' => ['interval' => '1d', 'duration' => 43200]];
        $timeConfig = $configs[$timeRange] ?? $configs['24h'];
        $labels     = [];
        $now        = Carbon::now();
        $endDate    = $now->copy()->toIso8601String();
        $startDate  = $now->copy()->subMinutes($timeConfig['duration'])->toIso8601String();
        $this->generateTimeLabels($timeRange, $timeConfig['interval'], $labels, $now);

        $query = [
            'size' => 0,
            'aggs' => ['events_by_time' => ['date_histogram' => ['field' => 'timestamp', 'fixed_interval' => $timeConfig['interval'], 'time_zone' => 'Asia/Jakarta', 'min_doc_count' => 0]]],
            'query' => [
                'bool' => [
                    'must'   => [['term' => ['agent.id' => $agentId]]],
                    'filter' => [
                        ['exists' => ['field' => "rule.{$complianceType}"]],
                        ['range' => ['timestamp' => ['gte' => $startDate, 'lte' => $endDate, 'format' => 'strict_date_optional_time']]]
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(3)->timeout(5)
                ->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)
                ->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);

            if ($response->successful()) {
                $data = array_map(fn($b) => $b['doc_count'], $response->json('aggregations.events_by_time.buckets') ?? []);
                if (empty($data)) $data = array_fill(0, count($labels), 0);
                return ['labels' => $labels, 'data' => $data];
            }
        } catch (\Exception $e) {
            \Log::warning("Events evolution compliance query failed: " . $e->getMessage());
        }

        return ['labels' => $labels, 'data' => array_fill(0, count($labels), 0)];
    }

    public function getSecurityEventsMetrics($agentId, $timeRange = 'now-24h')
    {
        $metrics = ['total' => 0, 'level12' => 0, 'auth_failure' => 0, 'auth_success' => 0];

        try {
            $base = ['size' => 0, 'query' => ['bool' => ['must' => [['term' => ['agent.id' => $agentId]]], 'filter' => [['range' => ['timestamp' => ['gte' => $timeRange]]]]]]];

            $r = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $base);
            if ($r->successful()) $metrics['total'] = $r->json('hits.total.value') ?? 0;

            $q = $base; $q['query']['bool']['filter'][] = ['range' => ['rule.level' => ['gte' => 12]]];
            $r = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $q);
            if ($r->successful()) $metrics['level12'] = $r->json('hits.total.value') ?? 0;

            $q = $base; $q['query']['bool']['filter'][] = ['bool' => ['should' => [['match' => ['rule.groups' => 'authentication_failed']], ['match' => ['rule.groups' => 'authentication_failures']], ['match' => ['rule.groups' => 'win_authentication_failed']]], 'minimum_should_match' => 1]];
            $r = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $q);
            if ($r->successful()) $metrics['auth_failure'] = $r->json('hits.total.value') ?? 0;

            $q = $base; $q['query']['bool']['filter'][] = ['match' => ['rule.groups' => 'authentication_success']];
            $r = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $q);
            if ($r->successful()) $metrics['auth_success'] = $r->json('hits.total.value') ?? 0;

        } catch (\Exception $e) {
            \Log::warning('Failed to fetch security events metrics: ' . $e->getMessage());
        }

        return $metrics;
    }

    public function getAlertGroupsEvolution($agentId, $timeRange = '24h')
    {
        $config = $this->getTimeRangeConfig($timeRange);

        try {
            $query = [
                'size' => 0,
                'aggs' => ['groups_evolution' => ['date_histogram' => ['field' => 'timestamp', 'fixed_interval' => $config['interval'], 'min_doc_count' => 1], 'aggs' => ['groups' => ['terms' => ['field' => 'rule.groups', 'size' => 5]]]]],
                'query' => ['bool' => ['must' => [['term' => ['agent.id' => $agentId]]], 'filter' => [['range' => ['timestamp' => ['gte' => "now-{$config['duration']}"]]]]]],
            ];

            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) return $this->parseGroupsEvolution($response->json());
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch alert groups evolution: ' . $e->getMessage());
        }

        return ['labels' => [], 'datasets' => []];
    }

    public function getTopAlerts($agentId, $timeRange = '24h', $limit = 5)
    {
        $config = $this->getTimeRangeConfig($timeRange);

        try {
            $query = [
                'size' => 0,
                'aggs' => ['top_alerts' => ['terms' => ['field' => 'rule.description', 'size' => $limit]]],
                'query' => ['bool' => ['must' => [['term' => ['agent.id' => $agentId]]], 'filter' => [['range' => ['timestamp' => ['gte' => "now-{$config['duration']}"]]]]]],
            ];

            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) {
                $buckets = $response->json('aggregations.top_alerts.buckets') ?? [];
                return ['labels' => array_map(fn($b) => substr($b['key'], 0, 50), $buckets), 'data' => array_map(fn($b) => $b['doc_count'], $buckets)];
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch top alerts: ' . $e->getMessage());
        }

        return ['labels' => [], 'data' => []];
    }

    public function getTopRuleGroups($agentId, $timeRange = '24h', $limit = 5)
    {
        $config = $this->getTimeRangeConfig($timeRange);

        try {
            $query = [
                'size' => 0,
                'aggs' => ['top_groups' => ['terms' => ['field' => 'rule.groups', 'size' => $limit]]],
                'query' => ['bool' => ['must' => [['term' => ['agent.id' => $agentId]]], 'filter' => [['range' => ['timestamp' => ['gte' => "now-{$config['duration']}"]]]]]],
            ];

            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) {
                $buckets = $response->json('aggregations.top_groups.buckets') ?? [];
                return ['labels' => array_map(fn($b) => $b['key'], $buckets), 'data' => array_map(fn($b) => $b['doc_count'], $buckets)];
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch top rule groups: ' . $e->getMessage());
        }

        return ['labels' => [], 'data' => []];
    }

    public function getTopPCIDSS($agentId, $timeRange = '24h', $limit = 5)
    {
        $config = $this->getTimeRangeConfig($timeRange);

        try {
            $query = [
                'size' => 0,
                'aggs' => ['top_pci_dss' => ['terms' => ['field' => 'rule.pci_dss', 'size' => $limit]]],
                'query' => ['bool' => ['must' => [['term' => ['agent.id' => $agentId]]], 'filter' => [['range' => ['timestamp' => ['gte' => "now-{$config['duration']}"]]]]]],
            ];

            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) {
                $buckets = $response->json('aggregations.top_pci_dss.buckets') ?? [];
                return ['labels' => array_map(fn($b) => $b['key'], $buckets), 'data' => array_map(fn($b) => $b['doc_count'], $buckets)];
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch top PCI DSS: ' . $e->getMessage());
        }

        return ['labels' => [], 'data' => []];
    }

    public function getRecentAlerts($agentId, $timeRange = '24h', $limit = 10, $offset = 0)
    {
        $config = $this->getTimeRangeConfig($timeRange);

        try {
            $query = [
                'size'              => $limit,
                'from'              => $offset,
                'track_total_hits'  => true,
                'sort'              => [['timestamp' => 'desc']],
                '_source'           => ['timestamp', 'rule.id', 'rule.description', 'rule.level', 'rule.groups'],
                'query'             => ['bool' => ['must' => [['term' => ['agent.id' => $agentId]]], 'filter' => [['range' => ['timestamp' => ['gte' => "now-{$config['duration']}"]]]]]],
            ];

            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) {
                $total = $response->json('hits.total.value') ?? 0;
                $data  = array_map(function ($hit) {
                    $source = $hit['_source'];
                    return [
                        'timestamp'   => $source['timestamp'] ?? '',
                        'rule_id'     => $source['rule']['id'] ?? 'N/A',
                        'description' => $source['rule']['description'] ?? 'No description',
                        'level'       => $source['rule']['level'] ?? 0,
                        'groups'      => is_array($source['rule']['groups'] ?? []) ? implode(', ', $source['rule']['groups']) : ($source['rule']['groups'] ?? 'unknown'),
                        'count'       => 1,
                    ];
                }, $response->json('hits.hits') ?? []);

                return ['data' => $data, 'total' => $total];
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch recent alerts: ' . $e->getMessage());
        }

        return ['data' => [], 'total' => 0];
    }

    public function getGroupsSummary($agentId, $timeRange = '24h', $limit = 10, $offset = 0)
    {
        $config    = $this->getTimeRangeConfig($timeRange);
        $fetchSize = max(500, $offset + $limit);

        try {
            $query = [
                'size' => 0,
                'aggs' => ['groups' => ['terms' => ['field' => 'rule.groups', 'size' => $fetchSize, 'order' => ['_count' => 'desc']]]],
                'query' => ['bool' => ['must' => [['term' => ['agent.id' => $agentId]]], 'filter' => [['range' => ['timestamp' => ['gte' => "now-{$config['duration']}"]]]]]],
            ];

            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) {
                $buckets = $response->json('aggregations.groups.buckets') ?? [];
                $total   = count($buckets);
                $sliced  = array_slice($buckets, $offset, $limit);
                $data    = array_map(fn($b) => ['group' => $b['key'], 'count' => $b['doc_count']], $sliced);
                return ['data' => $data, 'total' => $total];
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch groups summary: ' . $e->getMessage());
        }

        return ['data' => [], 'total' => 0];
    }

    public function getFimSummary($agentId, $timeRange = '24h')
    {
        $config = $this->getTimeRangeConfig($timeRange);
        try {
            $query = [
                'size' => 0,
                'aggs' => ['events' => ['terms' => ['field' => 'syscheck.event', 'size' => 10]]],
                'query' => ['bool' => [
                    'must'   => [['term' => ['agent.id' => $agentId]], ['match' => ['rule.groups' => 'syscheck']]],
                    'filter' => [['range' => ['timestamp' => ['gte' => "now-{$config['duration']}"]]]],
                ]],
            ];
            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) {
                $buckets = $response->json('aggregations.events.buckets') ?? [];
                $result  = ['total' => 0, 'added' => 0, 'modified' => 0, 'deleted' => 0];
                foreach ($buckets as $b) {
                    $result['total'] += $b['doc_count'];
                    if (array_key_exists($b['key'], $result)) $result[$b['key']] = $b['doc_count'];
                }
                return $result;
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch FIM summary: ' . $e->getMessage());
        }
        return ['total' => 0, 'added' => 0, 'modified' => 0, 'deleted' => 0];
    }

    public function getFimEvolution($agentId, $timeRange = '24h')
    {
        $config = $this->getTimeRangeConfig($timeRange);
        try {
            $query = [
                'size' => 0,
                'aggs' => ['evolution' => [
                    'date_histogram' => ['field' => 'timestamp', 'fixed_interval' => $config['interval'], 'min_doc_count' => 0],
                    'aggs'           => ['event_types' => ['terms' => ['field' => 'syscheck.event', 'size' => 5]]],
                ]],
                'query' => ['bool' => [
                    'must'   => [['term' => ['agent.id' => $agentId]], ['match' => ['rule.groups' => 'syscheck']]],
                    'filter' => [['range' => ['timestamp' => ['gte' => "now-{$config['duration']}"]]]],
                ]],
            ];
            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) {
                $buckets  = $response->json('aggregations.evolution.buckets') ?? [];
                $labels   = [];
                $added = $modified = $deleted = [];
                foreach ($buckets as $bucket) {
                    $labels[]   = Carbon::createFromTimestampMs($bucket['key'], 'UTC')->toIso8601String();
                    $byType     = collect($bucket['event_types']['buckets'] ?? [])->keyBy('key');
                    $added[]    = $byType->get('added',    ['doc_count' => 0])['doc_count'];
                    $modified[] = $byType->get('modified', ['doc_count' => 0])['doc_count'];
                    $deleted[]  = $byType->get('deleted',  ['doc_count' => 0])['doc_count'];
                }
                return [
                    'labels'   => $labels,
                    'datasets' => [
                        ['label' => 'Added',    'data' => $added,    'borderColor' => '#20c997', 'backgroundColor' => 'rgba(32,201,151,0.15)',  'fill' => true, 'tension' => 0.4, 'borderWidth' => 2],
                        ['label' => 'Modified', 'data' => $modified, 'borderColor' => '#ffc107', 'backgroundColor' => 'rgba(255,193,7,0.15)',   'fill' => true, 'tension' => 0.4, 'borderWidth' => 2],
                        ['label' => 'Deleted',  'data' => $deleted,  'borderColor' => '#dc3545', 'backgroundColor' => 'rgba(220,53,69,0.15)',   'fill' => true, 'tension' => 0.4, 'borderWidth' => 2],
                    ],
                ];
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch FIM evolution: ' . $e->getMessage());
        }
        return ['labels' => [], 'datasets' => []];
    }

    public function getFimTopRules($agentId, $timeRange = '24h', $limit = 5)
    {
        $config = $this->getTimeRangeConfig($timeRange);
        try {
            $query = [
                'size' => 0,
                'aggs' => ['top_rules' => ['terms' => ['field' => 'rule.description', 'size' => $limit]]],
                'query' => ['bool' => [
                    'must'   => [['term' => ['agent.id' => $agentId]], ['match' => ['rule.groups' => 'syscheck']]],
                    'filter' => [['range' => ['timestamp' => ['gte' => "now-{$config['duration']}"]]]],
                ]],
            ];
            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) {
                $buckets = $response->json('aggregations.top_rules.buckets') ?? [];
                return ['labels' => array_map(fn($b) => substr($b['key'], 0, 50), $buckets), 'data' => array_map(fn($b) => $b['doc_count'], $buckets)];
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch FIM top rules: ' . $e->getMessage());
        }
        return ['labels' => [], 'data' => []];
    }

    public function getFimTopFiles($agentId, $timeRange = '24h', $action = 'modified', $limit = 5)
    {
        $config = $this->getTimeRangeConfig($timeRange);
        try {
            $query = [
                'size' => 0,
                'aggs' => ['top_files' => ['terms' => ['field' => 'syscheck.path', 'size' => $limit]]],
                'query' => ['bool' => [
                    'must'   => [
                        ['term' => ['agent.id' => $agentId]],
                        ['match' => ['rule.groups' => 'syscheck']],
                        ['term' => ['syscheck.event' => $action]],
                    ],
                    'filter' => [['range' => ['timestamp' => ['gte' => "now-{$config['duration']}"]]]],
                ]],
            ];
            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) {
                $buckets = $response->json('aggregations.top_files.buckets') ?? [];
                return array_map(fn($b) => ['path' => $b['key'], 'count' => $b['doc_count']], $buckets);
            }
        } catch (\Exception $e) {
            \Log::warning("Failed to fetch FIM top files ({$action}): " . $e->getMessage());
        }
        return [];
    }

    public function getFimEventsPaginated($agentId, $timeRange = '24h', $limit = 10, $offset = 0)
    {
        $config = $this->getTimeRangeConfig($timeRange);
        try {
            $query = [
                'size'             => $limit,
                'from'             => $offset,
                'track_total_hits' => true,
                'sort'             => [['timestamp' => 'desc']],
                '_source'          => ['timestamp', 'syscheck.path', 'syscheck.event', 'syscheck.md5_after', 'rule.id', 'rule.description', 'rule.level'],
                'query'            => ['bool' => [
                    'must'   => [['term' => ['agent.id' => $agentId]], ['match' => ['rule.groups' => 'syscheck']]],
                    'filter' => [['range' => ['timestamp' => ['gte' => "now-{$config['duration']}"]]]],
                ]],
            ];
            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) {
                $total = $response->json('hits.total.value') ?? 0;
                $data  = array_map(function ($hit) {
                    $s = $hit['_source'];
                    return [
                        'timestamp'   => $s['timestamp'] ?? '',
                        'path'        => $s['syscheck']['path'] ?? 'Unknown',
                        'action'      => $s['syscheck']['event'] ?? 'unknown',
                        'md5'         => $s['syscheck']['md5_after'] ?? '',
                        'rule_id'     => $s['rule']['id'] ?? '',
                        'description' => $s['rule']['description'] ?? '',
                        'level'       => $s['rule']['level'] ?? 0,
                    ];
                }, $response->json('hits.hits') ?? []);
                return ['data' => $data, 'total' => $total];
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch FIM events paginated: ' . $e->getMessage());
        }
        return ['data' => [], 'total' => 0];
    }

    public function getAlertsEvolutionByLevel($agentId, $timeRange = '24h')
    {
        $config = $this->getTimeRangeConfig($timeRange);

        try {
            $query = [
                'size' => 0,
                'aggs' => ['alerts_evolution' => ['date_histogram' => ['field' => 'timestamp', 'fixed_interval' => $config['interval'], 'min_doc_count' => 1], 'aggs' => ['levels' => ['terms' => ['field' => 'rule.level', 'size' => 10]]]]],
                'query' => ['bool' => ['must' => [['term' => ['agent.id' => $agentId]]], 'filter' => [['range' => ['timestamp' => ['gte' => "now-{$config['duration']}"]]]]]],
            ];

            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) return $this->parseAlertsEvolution($response->json());
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch alerts evolution by level: ' . $e->getMessage());
        }

        return ['labels' => [], 'datasets' => []];
    }

    public function getMitreTechniques($agentId, $timeRange = '24h')
    {
        $managerName = env('WAZUH_MANAGER_NAME', 'fadli');
        $config      = $this->getTimeRangeConfig($timeRange);

        $query = [
            'size' => 0,
            'aggs' => [
                'techniques' => ['terms' => ['field' => 'rule.mitre.technique', 'size' => 10]],
            ],
            'query' => [
                'bool' => [
                    'filter' => [
                        ['match_all' => (object)[]],
                        ['match_phrase' => ['manager.name' => $managerName]],
                        ['match_phrase' => ['agent.id' => $agentId]],
                        ['exists' => ['field' => 'rule.mitre.id']],
                        ['range' => ['timestamp' => ['from' => "now-{$config['duration']}", 'to' => 'now']]],
                    ],
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) {
                $buckets = $response->json('aggregations.techniques.buckets') ?? [];
                return array_map(fn($b) => ['technique' => $b['key'] ?? 'Unknown', 'count' => $b['doc_count'] ?? 0], $buckets);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch MITRE techniques: ' . $e->getMessage());
        }

        return [];
    }

    public function getMitreAlerts($agentId, $timeRange = '24h', $limit = 10, $offset = 0)
    {
        $managerName = env('WAZUH_MANAGER_NAME', 'fadli');
        $config      = $this->getTimeRangeConfig($timeRange);

        try {
            $query = [
                'size'             => $limit,
                'from'             => $offset,
                'track_total_hits' => true,
                'sort'             => [['timestamp' => 'desc']],
                '_source'          => ['timestamp', 'rule.id', 'rule.description', 'rule.level', 'rule.mitre.id', 'rule.mitre.tactic', 'rule.mitre.technique'],
                'query'            => [
                    'bool' => [
                        'filter' => [
                            ['match_phrase' => ['manager.name' => $managerName]],
                            ['match_phrase' => ['agent.id' => $agentId]],
                            ['exists' => ['field' => 'rule.mitre.id']],
                            ['range' => ['timestamp' => ['gte' => "now-{$config['duration']}"]]]
                        ],
                    ]
                ],
            ];

            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) {
                $total = $response->json('hits.total.value') ?? 0;
                $data  = array_map(function ($hit) {
                    $s = $hit['_source'];
                    return [
                        'timestamp'   => $s['timestamp'] ?? '',
                        'rule_id'     => $s['rule']['id'] ?? 'N/A',
                        'description' => $s['rule']['description'] ?? '',
                        'level'       => $s['rule']['level'] ?? 0,
                        'mitre_id'    => is_array($s['rule']['mitre']['id'] ?? null)
                            ? implode(', ', $s['rule']['mitre']['id'])
                            : ($s['rule']['mitre']['id'] ?? ''),
                        'tactic'      => is_array($s['rule']['mitre']['tactic'] ?? null)
                            ? implode(', ', $s['rule']['mitre']['tactic'])
                            : ($s['rule']['mitre']['tactic'] ?? ''),
                        'technique'   => is_array($s['rule']['mitre']['technique'] ?? null)
                            ? implode(', ', $s['rule']['mitre']['technique'])
                            : ($s['rule']['mitre']['technique'] ?? ''),
                    ];
                }, $response->json('hits.hits') ?? []);

                return ['data' => $data, 'total' => $total];
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch MITRE alerts: ' . $e->getMessage());
        }

        return ['data' => [], 'total' => 0];
    }

    public function getMitreTactics($agentId, $timeRange = '24h')
    {
        $managerName = env('WAZUH_MANAGER_NAME', 'fadli');
        $config      = $this->getTimeRangeConfig($timeRange);

        $query = [
            'size' => 0,
            'aggs' => ['tactics' => ['terms' => ['field' => 'rule.mitre.tactic', 'size' => 10]]],
            'query' => [
                'bool' => [
                    'filter' => [
                        ['match_all' => (object)[]],
                        ['match_phrase' => ['manager.name' => $managerName]],
                        ['match_phrase' => ['agent.id' => $agentId]],
                        ['exists' => ['field' => 'rule.mitre.id']],
                        ['range' => ['timestamp' => ['from' => "now-{$config['duration']}", 'to' => 'now']]]
                    ],
                ]
            ]
        ];

        try {
            $response = Http::withoutVerifying()->connectTimeout(3)->timeout(5)->withBasicAuth($this->_opensearchUser, $this->_opensearchPassword)->post("{$this->_opensearchHost}/wazuh-alerts-*/_search", $query);
            if ($response->successful()) {
                $buckets = $response->json('aggregations.tactics.buckets') ?? [];
                return array_map(fn($b) => ['tactic' => $b['key'] ?? 'Unknown', 'count' => $b['doc_count'] ?? 0], $buckets);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch MITRE tactics: ' . $e->getMessage());
        }

        return [];
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function parseSeverityResponse($data): array
    {
        $buckets  = $data['aggregations']['severity_counts']['buckets'] ?? [];
        $critical = 0; $high = 0; $medium = 0; $low = 0;

        foreach ($buckets as $bucket) {
            $level = $bucket['key'];
            $count = $bucket['doc_count'];
            if ($level >= 12)    $critical += $count;
            elseif ($level >= 9) $high     += $count;
            elseif ($level >= 6) $medium   += $count;
            else                 $low       += $count;
        }

        return compact('critical', 'high', 'medium', 'low');
    }

    private function parseGroupsEvolution($data): array
    {
        $buckets    = $data['aggregations']['groups_evolution']['buckets'] ?? [];
        $labels     = [];
        $groupsData = [];

        foreach ($buckets as $bucket) {
            $labels[] = Carbon::createFromTimestampMs($bucket['key'], 'UTC')->toIso8601String();
            foreach ($bucket['groups']['buckets'] ?? [] as $group) {
                $groupsData[$group['key']][] = $group['doc_count'];
            }
        }

        foreach ($groupsData as &$data) {
            while (count($data) < count($labels)) $data[] = 0;
        }

        $colors   = ['#fd7e14', '#ffc107', '#dc3545', '#6f42c1', '#20c997', '#0d6efd'];
        $datasets = [];
        foreach ($groupsData as $groupName => $values) {
            $datasets[] = ['label' => $groupName, 'data' => $values, 'borderColor' => array_shift($colors) ?: '#6f42c1', 'fill' => true, 'tension' => 0.4, 'borderWidth' => 2];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    private function parseAlertsEvolution($data): array
    {
        $buckets   = $data['aggregations']['alerts_evolution']['buckets'] ?? [];
        $labels    = [];
        $levelData = [];

        foreach ($buckets as $bucket) {
            $labels[] = Carbon::createFromTimestampMs($bucket['key'], 'UTC')->toIso8601String();
            foreach ($bucket['levels']['buckets'] ?? [] as $level) {
                $levelData[$level['key']][] = $level['doc_count'];
            }
        }

        foreach ($levelData as &$data) {
            while (count($data) < count($labels)) $data[] = 0;
        }

        krsort($levelData);
        $levelData = array_slice($levelData, 0, 5, true);

        $colors   = ['#dc3545', '#fd7e14', '#ffc107', '#0d6efd', '#20c997'];
        $datasets = [];
        $idx      = 0;
        foreach ($levelData as $level => $values) {
            $datasets[] = ['label' => "Level $level", 'data' => $values, 'borderColor' => $colors[$idx % count($colors)], 'fill' => true, 'tension' => 0.4, 'borderWidth' => 2];
            $idx++;
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    private function getTimeRangeConfig($timeRange): array
    {
        $config = [
            '15m'  => ['duration' => '15m',  'interval' => '3m'],
            '30m'  => ['duration' => '30m',  'interval' => '5m'],
            '1h'   => ['duration' => '1h',   'interval' => '10m'],
            '24h'  => ['duration' => '24h',  'interval' => '30m'],
            '7d'   => ['duration' => '7d',   'interval' => '12h'],
            '30d'  => ['duration' => '30d',  'interval' => '1d'],
            '90d'  => ['duration' => '90d',  'interval' => '1d'],
            '1y'   => ['duration' => '1y',   'interval' => '1d'],
            'today'=> ['duration' => '1d',   'interval' => '30m'],
            'week' => ['duration' => '7d',   'interval' => '1d'],
        ];
        return $config[$timeRange] ?? $config['24h'];
    }

    private function getAgentEvolutionFallbackData($labels = []): array
    {
        if (empty($labels)) {
            $now    = Carbon::now();
            $labels = array_map(fn($i) => $now->copy()->subMinutes($i * 10)->format('H:i'), range(144, 0));
        }
        return ['labels' => $labels, 'data' => array_fill(0, count($labels), 0)];
    }

    private function generateTimeLabels($timeRange, $interval, &$labels, $baseTime = null): void
    {
        $now = $baseTime ?? Carbon::now();
        preg_match('/(\d+)([mhd])/', $interval, $matches);
        if (!$matches) return;

        $value = (int) $matches[1];
        $unit  = $matches[2];
        $step  = fn($dt) => match($unit) { 'm' => $dt->addMinutes($value), 'h' => $dt->addHours($value), 'd' => $dt->addDays($value) };

        $start = match(true) {
            $timeRange === 'today' => $now->copy()->startOfDay(),
            $timeRange === 'week'  => $now->copy()->startOfWeek(),
            default                => $now->copy()->subMinutes(($unit === 'm' ? $value * 144 : ($unit === 'h' ? $value * 24 : $value * 30))),
        };

        $current = $start;
        while ($current <= $now) {
            $labels[] = $current->format('M d H:i');
            $step($current);
        }
    }
}
