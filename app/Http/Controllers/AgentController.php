<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Services\OpenSearchService;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AgentController extends Controller
{
    private $wazuhBase;
    private $wazuhUser;
    private $wazuhPass;
    private $openSearch;

    public function __construct(OpenSearchService $openSearch)
    {
        $this->wazuhBase = env('WAZUH_HOST', 'https://192.168.200.150:55000');
        $this->wazuhUser = env('WAZUH_USER', 'admin');
        $this->wazuhPass = env('WAZUH_PASSWORD', 'admin');
        $this->openSearch = $openSearch;
    }

    /**
     * Get authentication token from Wazuh API
     */
    private function getToken()
    {
        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(2)
                ->timeout(2)
                ->withBasicAuth($this->wazuhUser, $this->wazuhPass)
                ->post("{$this->wazuhBase}/security/user/authenticate");

            if (!$response->successful()) {
                \Log::warning('Wazuh token request failed: ' . $response->status());
                return null;
            }

            return $response->json('data.token');
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::warning('Wazuh API connection timeout: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            \Log::warning('Wazuh API unreachable: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get agent statistics from Wazuh API
     */
    private function getAgentStats($token)
    {
        $stats = [
            'total'           => 0,
            'active'          => 0,
            'disconnected'    => 0,
            'pending'         => 0,
            'never_connected' => 0,
        ];

        if (!$token) {
            return $stats;
        }

        try {
            $summary = Http::withoutVerifying()
                ->connectTimeout(2)
                ->timeout(2)
                ->withToken($token)
                ->get("{$this->wazuhBase}/agents/summary/status")
                ->json('data.connection');

            $stats = [
                'total'           => $summary['total'] ?? 0,
                'active'          => $summary['active'] ?? 0,
                'disconnected'    => $summary['disconnected'] ?? 0,
                'pending'         => $summary['pending'] ?? 0,
                'never_connected' => $summary['never_connected'] ?? 0,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::warning('Failed to fetch agent summary (timeout): ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch agent summary: ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Get agents list from Wazuh API with pagination and filtering
     */
    private function getAgentsFromWazuh($token, $offset = 0, $limit = 10, $search = null, $status = null)
    {
        if (!$token) {
            return ['agents' => [], 'total' => 0];
        }

        try {
            $url = "{$this->wazuhBase}/agents";
            
            $params = [
                'offset' => $offset,
                'limit' => $limit,
                'sort' => 'id',
            ];

            // Add search filter if provided
            if ($search) {
                $params['search'] = $search;
            }

            // Add status filter if provided
            if ($status && in_array($status, ['active', 'disconnected', 'pending', 'never_connected'])) {
                $params['status'] = $status;
            }

            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(3)
                ->withToken($token)
                ->get($url, $params);

            if ($response->successful()) {
                $data = $response->json('data');
                return [
                    'agents' => $data['affected_items'] ?? [],
                    'total' => $data['total_affected_items'] ?? 0,
                ];
            }

            return ['agents' => [], 'total' => 0];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::warning('Failed to fetch agents from Wazuh (timeout): ' . $e->getMessage());
            return ['agents' => [], 'total' => 0];
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch agents from Wazuh: ' . $e->getMessage());
            return ['agents' => [], 'total' => 0];
        }
    }

    /**
     * Enrich agent data with database information
     */
    private function enrichAgentData($wazuhAgent)
    {
        $agentId = $wazuhAgent->id_agent ?? null;

        if ($agentId) {
            $dbAgent = Agent::where('id_agent', $agentId)->with('user')->first();
            if ($dbAgent) {
                $wazuhAgent->user = $dbAgent->user;
            }
        }

        return $wazuhAgent;
    }

    /**
     * Get evolution data for user's agents using OpenSearch
     * @param string $timeRange The time range for the query
     * @param Carbon $baseTime Optional fixed point in time to use for queries (ensures consistency)
     */
    private function getAgentEvolution($timeRange = '24h', $baseTime = null)
    {
        try {
            // Get current user and their role
            $user = auth()->user();
            $userId = $user->id_pengguna ?? auth()->id();
            $userRole = $user->peran ?? null;
            
            \Log::info('Fetching agent evolution', [
                'user_id' => $userId,
                'user_role' => $userRole,
                'time_range' => $timeRange,
                'base_time' => $baseTime ? $baseTime->toIso8601String() : 'now'
            ]);
            
            // Only filter by agent IDs if user is a customer
            // Admin users see all agents
            $userAgents = null;
            if ($userRole === 'customer') {
                $userAgents = Agent::where('id_pengguna', $userId)->pluck('id_agent')->toArray();
                
                \Log::info('Customer user - filtering by assigned agents', [
                    'count' => count($userAgents),
                    'agent_ids' => $userAgents
                ]);
            } else {
                \Log::info('Admin/Manager user - showing all agents');
            }

            // Fetch evolution data from OpenSearch with fixed base time
            $evolution = $this->openSearch->getAgentEvolutionByTimeRange($timeRange, $userAgents ?: null, $baseTime);

            \Log::info('Agent evolution data fetched', [
                'labels_count' => count($evolution['labels'] ?? []),
                'data_count' => count($evolution['data'] ?? [])
            ]);
            
            return $evolution;
        } catch (\Exception $e) {
            \Log::error('Failed to fetch agent evolution data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return fallback data
            return [
                'labels' => [],
                'data' => []
            ];
        }
    }

    /**
     * Get icon for operating system
     */
    public static function getOSIcon($os)
    {
        if (!$os) {
            return 'mdi-help-circle-outline';
        }

        $os = strtolower($os);

        if (str_contains($os, 'windows')) {
            return 'mdi-microsoft-windows';
        } elseif (str_contains($os, 'ubuntu') || str_contains($os, 'debian') || str_contains($os, 'linux')) {
            return 'mdi-linux';
        } elseif (str_contains($os, 'centos') || str_contains($os, 'rhel') || str_contains($os, 'fedora')) {
            return 'mdi-linux';
        } elseif (str_contains($os, 'mac') || str_contains($os, 'darwin')) {
            return 'mdi-apple';
        }

        return 'mdi-help-circle-outline';
    }

    /**
     * Get status badge color
     */
    public static function getStatusBadgeColor($status)
    {
        return match ($status) {
            'active' => 'success',
            'disconnected' => 'danger',
            'pending' => 'warning',
            'never_connected' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Format status for display
     */
    public static function formatStatus($status)
    {
        return match ($status) {
            'active' => 'Active',
            'disconnected' => 'Disconnected',
            'pending' => 'Pending',
            'never_connected' => 'Never Connected',
            default => ucfirst($status),
        };
    }

    /**
     * Display agents list with statistics
     */
    public function index()
    {
        try {
            \Log::info('Agent index request started');
            
            $token = $this->getToken();
            $stats = $this->getAgentStats($token);

            // Get pagination parameters
            $perPage = request('per_page', 10);
            $page = max(request('page', 1), 1);
            $offset = ($page - 1) * $perPage;
            
            // Get filter parameters
            $search = request('search');
            $status = request('status');

            \Log::info('Fetching agents', [
                'page' => $page,
                'per_page' => $perPage,
                'offset' => $offset,
                'search' => $search,
                'status' => $status
            ]);

            // Fetch agents from Wazuh
            $wazuhData = $this->getAgentsFromWazuh($token, $offset, $perPage, $search, $status);
            
            // Convert Wazuh agents to objects
            $agentsList = collect($wazuhData['agents'])->map(function ($agent) {
                // Map Wazuh agent fields to our view format
                $agentData = (object) [
                    'id_agent' => $agent['id'] ?? null,
                    'nama' => $agent['name'] ?? 'Unknown',
                    'ip' => $agent['ip'] ?? 'N/A',
                    'os' => is_array($agent['os']) ? ($agent['os']['name'] ?? 'Unknown') : ($agent['os'] ?? 'Unknown'),
                    'version' => $agent['version'] ?? 'N/A',
                    'status' => $agent['status'] ?? 'unknown',
                    'cluster_node' => $agent['node_name'] ?? $agent['group'] ?? 'N/A',
                    'user' => null, // Will be enriched from database
                ];
                
                return $this->enrichAgentData($agentData);
            });

            // Create a length-aware paginator
            $agents = new \Illuminate\Pagination\LengthAwarePaginator(
                items: $agentsList,
                total: $wazuhData['total'],
                perPage: $perPage,
                currentPage: $page,
                options: [
                    'path' => route('agent'),
                    'query' => request()->query(),
                ]
            );

            // Get evolution data for chart with consistent base time
            // Capture time once per minute for consistency across requests
            $sessionKey = 'agent_evolution_base_time_' . floor(date('n')); // Changes every minute
            $baseTime = session($sessionKey);
            if (!$baseTime) {
                $baseTime = Carbon::now();
                session([$sessionKey => $baseTime]);
            }
            
            $evolution = $this->getAgentEvolution('24h', $baseTime);
            $evolutionLabels = json_encode($evolution['labels'] ?? []);
            $evolutionData   = json_encode($evolution['data']['active'] ?? $evolution['data'] ?? []);

            \Log::info('Agent index completed', [
                'agents_count' => count($agentsList),
                'evolution_labels_count' => count($evolution['labels'] ?? []),
                'evolution_data_count' => count($evolution['data'] ?? [])
            ]);

            return view('agent.index', compact('agents', 'stats', 'evolutionLabels', 'evolutionData'));
        } catch (\Exception $e) {
            \Log::error('Agent controller error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return view with empty data on error
            $agents = new \Illuminate\Pagination\LengthAwarePaginator(
                items: [],
                total: 0,
                perPage: 10,
                currentPage: 1,
                options: [
                    'path' => route('agent'),
                    'query' => request()->query(),
                ]
            );

            $evolutionLabels = json_encode([]);
            $evolutionData   = json_encode([]);

            return view('agent.index', [
                'agents' => $agents,
                'stats' => [
                    'total' => 0,
                    'active' => 0,
                    'disconnected' => 0,
                    'pending' => 0,
                    'never_connected' => 0,
                ],
                'evolutionLabels' => $evolutionLabels,
                'evolutionData' => $evolutionData,
            ]);
        }
    }

    /**
     * Get chart data with selected time range (AJAX endpoint)
     */
    public function getChartData()
    {
        try {
            $timeRange = request('time_range', '24h');
            
            // Use consistent base time from session (same per minute)
            $sessionKey = 'agent_evolution_base_time_' . floor(date('n'));
            $baseTime = session($sessionKey);
            if (!$baseTime) {
                $baseTime = Carbon::now();
                session([$sessionKey => $baseTime]);
            }
            
            \Log::info('Chart data request', [
                'time_range' => $timeRange,
                'user_id' => auth()->id(),
                'base_time' => $baseTime->toIso8601String()
            ]);
            
            $evolution = $this->getAgentEvolution($timeRange, $baseTime);
            
            \Log::info('Chart data generated', [
                'time_range' => $timeRange,
                'labels_count' => count($evolution['labels'] ?? []),
                'data_count' => count($evolution['data'] ?? []),
                'data_keys' => array_keys($evolution['data'] ?? [])
            ]);
            
            return response()->json([
                'success' => true,
                'labels' => $evolution['labels'] ?? [],
                'data' => $evolution['data'] ?? []
            ]);
        } catch (\Exception $e) {
            \Log::error('Get chart data error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch chart data'
            ], 500);
        }
    }
}
