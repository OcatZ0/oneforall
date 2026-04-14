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
     * @param array $agentIds Optional list of agent IDs to filter by (for non-admin users)
     * @param bool $isAdmin Whether the user is admin
     * @param Carbon $baseTime Optional fixed point in time to use for queries (ensures consistency)
     */
    private function getAgentEvolution($timeRange = '24h', $agentIds = null, $isAdmin = true, $baseTime = null)
    {
        try {
            // Fetch evolution data from OpenSearch with fixed base time
            $evolution = $this->openSearch->getAgentEvolutionByTimeRange($timeRange, $agentIds, $baseTime, $isAdmin);

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
     * Check if current user has access to a specific agent
     * Admin has access to all agents, customers only to their assigned agents
     */
    private function userHasAccessToAgent($agentId)
    {
        $user = auth()->user();
        
        // Ensure agentId is a string for comparison
        $agentId = (string)$agentId;
        
        // Admin has access to all agents
        if ($user->peran === 'admin') {
            return true;
        }
        
        // Customer can only access their assigned agents
        $dbAgent = Agent::where('id_agent', $agentId)->first();
        
        if (!$dbAgent) {
            // Agent not in database - customer cannot access
            \Log::warning('Agent not found in database', [
                'agent_id' => $agentId,
                'user_id' => $user->id_pengguna
            ]);
            return false;
        }
        
        // Check if agent is assigned to this customer
        $hasAccess = $dbAgent->id_pengguna === $user->id_pengguna;
        
        if (!$hasAccess) {
            \Log::warning('Customer does not have access to agent', [
                'agent_id' => $agentId,
                'user_id' => $user->id_pengguna,
                'agent_owner_id' => $dbAgent->id_pengguna
            ]);
        }
        
        return $hasAccess;
    }

    /**
     * Get list of agent IDs accessible by current user
     * Admin can access all agents, customers only their assigned agents
     */
    private function getAccessibleAgentIds()
    {
        $user = auth()->user();
        
        // Admin gets all agents
        if ($user->peran === 'admin') {
            return Agent::pluck('id_agent')->toArray();
        }
        
        // Customer gets only their assigned agents
        return Agent::where('id_pengguna', $user->id_pengguna)
            ->pluck('id_agent')
            ->toArray();
    }

    /**
     * Get agent statistics filtered by user accessibility
     */
    private function getAgentStatsFiltered($token)
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
            $user = auth()->user();
            
            // If admin, get all stats from API
            if ($user->peran === 'admin') {
                $summary = Http::withoutVerifying()
                    ->connectTimeout(2)
                    ->timeout(2)
                    ->withToken($token)
                    ->get("{$this->wazuhBase}/agents/summary/status")
                    ->json('data.connection');

                return [
                    'total'           => $summary['total'] ?? 0,
                    'active'          => $summary['active'] ?? 0,
                    'disconnected'    => $summary['disconnected'] ?? 0,
                    'pending'         => $summary['pending'] ?? 0,
                    'never_connected' => $summary['never_connected'] ?? 0,
                ];
            }
            
            // For customers, get accessible agent IDs and fetch their individual status
            $accessibleIds = $this->getAccessibleAgentIds();
            
            if (empty($accessibleIds)) {
                return $stats;
            }
            
            // Get all agents and filter by accessible IDs
            try {
                $response = Http::withoutVerifying()
                    ->connectTimeout(3)
                    ->timeout(3)
                    ->withToken($token)
                    ->get("{$this->wazuhBase}/agents", [
                        'limit' => 50000,
                    ]);

                if ($response->successful()) {
                    $allAgents = $response->json('data.affected_items', []);
                    
                    foreach ($allAgents as $agent) {
                        if (in_array($agent['id'], $accessibleIds)) {
                            $stats['total']++;
                            $status = $agent['status'] ?? 'unknown';
                            if (isset($stats[$status])) {
                                $stats[$status]++;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch filtered agent stats: ' . $e->getMessage());
            }
            
            return $stats;
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch filtered agent summary: ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Display agents list with statistics
     */
    public function index()
    {
        try {
            \Log::info('Agent index request started');
            
            $user = auth()->user();
            $userRole = $user->peran ?? null;
            
            $token = $this->getToken();
            
            // Get agent statistics (filtered by user accessibility)
            $stats = $this->getAgentStatsFiltered($token);
            
            // Get pagination parameters
            $perPage = request('per_page', 10);
            $page = max(request('page', 1), 1);
            $offset = ($page - 1) * $perPage;
            
            // Get filter parameters
            $search = request('search');
            $status = request('status');
            
            // Fetch agents from Wazuh
            $wazuhData = $this->getAgentsFromWazuh($token, $offset, $perPage, $search, $status);

            // Get pagination parameters
            $perPage = request('per_page', 10);
            $page = max(request('page', 1), 1);
            
            // Get accessible agent IDs for filtering (non-admin users)
            $accessibleIds = ($userRole === 'admin') ? null : $this->getAccessibleAgentIds();
            
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
            })->filter(function ($agent) use ($accessibleIds, $userRole) {
                // Filter agents based on user role
                if ($userRole === 'admin') {
                    return true; // Admin sees all
                }
                // Customer sees only their assigned agents
                return in_array($agent->id_agent, $accessibleIds);
            });

            // Create a length-aware paginator
            $agents = new \Illuminate\Pagination\LengthAwarePaginator(
                items: $agentsList->forPage($page, $perPage)->values(),
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
            
            // Get accessible agent IDs for filtering (non-admin users)
            $isAdmin = $userRole === 'admin';
            $accessibleAgentIds = null;
            if (!$isAdmin) {
                $accessibleAgentIds = $this->getAccessibleAgentIds();
                // Convert to strings to match OpenSearch format
                $accessibleAgentIds = array_map(fn($id) => (string)$id, $accessibleAgentIds ?? []);
            }
            
            $evolution = $this->getAgentEvolution('24h', $accessibleAgentIds, $isAdmin, $baseTime);
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

    /**
     * Get chart data for detail page (agent-specific)
     */
    public function getDetailChartData($id)
    {
        try {
            $agentId = (string)$id;  // Get from route parameter and ensure string type
            $timeRange = request('time_range', '24h');
            $complianceType = request('compliance_type', 'gdpr');
            
            $user = auth()->user();
            
            \Log::info('Detail chart data request initiated', [
                'agent_id' => $agentId,
                'user_id' => $user->id_pengguna ?? auth()->id(),
                'user_role' => $user->peran,
                'time_range' => $timeRange,
                'compliance_type' => $complianceType
            ]);
            
            // Check if user has access to this agent
            if (!$this->userHasAccessToAgent($agentId)) {
                \Log::warning('Unauthorized chart data access for agent detail', [
                    'agent_id' => $agentId,
                    'user_id' => auth()->id(),
                    'user_role' => auth()->user()->peran
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - You do not have access to this agent'
                ], 403);
            }
            
            \Log::info('Detail chart data request', [
                'agent_id' => $agentId,
                'time_range' => $timeRange,
                'compliance_type' => $complianceType,
                'user_id' => auth()->id()
            ]);
            
            // Fetch events evolution for the agent
            $eventsEvolution = $this->openSearch->getEventsCountEvolution($agentId, $timeRange);
            
            // Fetch compliance data for the agent
            $complianceData = $this->openSearch->getAgentCompliance($agentId, $complianceType, $timeRange);
            
            // Fetch events evolution filtered by compliance type
            $eventsComplianceEvolution = $this->openSearch->getEventsCountEvolutionByCompliance($agentId, $complianceType, $timeRange);
            
            \Log::info('Detail chart data generated', [
                'agent_id' => $agentId,
                'time_range' => $timeRange,
                'events_labels_count' => count($eventsEvolution['labels'] ?? []),
                'events_data_count' => count($eventsEvolution['data'] ?? []),
                'compliance_count' => count($complianceData ?? [])
            ]);
            
            return response()->json([
                'success' => true,
                'events_evolution' => $eventsEvolution,
                'compliance_data' => $complianceData,
                'events_compliance_evolution' => $eventsComplianceEvolution
            ]);
        } catch (\Exception $e) {
            \Log::error('Get detail chart data error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch chart data'
            ], 500);
        }
    }

    /**
     * Display agent detail page
     */
    public function detail($id)
    {
        try {
            // Check if user has access to this agent
            if (!$this->userHasAccessToAgent($id)) {
                \Log::warning('Unauthorized agent detail access', [
                    'agent_id' => $id,
                    'user_id' => auth()->id(),
                    'user_role' => auth()->user()->peran
                ]);
                
                return view('agent.detail', [
                    'agent' => null,
                    'error' => 'You do not have permission to view this agent'
                ]);
            }
            
            $token = $this->getToken();
            
            if (!$token) {
                \Log::warning('Agent detail: No token available');
                return view('agent.detail', [
                    'agent' => null,
                    'error' => 'Unable to authenticate with Wazuh API'
                ]);
            }

            // Fetch agent from Wazuh API
            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(3)
                ->withToken($token)
                ->get("{$this->wazuhBase}/agents", [
                    'agents_list' => $id
                ]);

            if (!$response->successful()) {
                \Log::warning("Agent detail not found: $id");
                return view('agent.detail', [
                    'agent' => null,
                    'error' => 'Agent not found'
                ]);
            }

            $data = $response->json('data.affected_items');
            if (empty($data)) {
                \Log::warning("Agent detail empty response: $id");
                return view('agent.detail', [
                    'agent' => null,
                    'error' => 'Agent not found'
                ]);
            }

            $wazuhAgent = $data[0];
            
            // Map Wazuh agent fields to our view format
            $agent = (object) [
                'id_agent' => $wazuhAgent['id'] ?? null,
                'nama' => $wazuhAgent['name'] ?? 'Unknown',
                'ip' => $wazuhAgent['ip'] ?? 'N/A',
                'os' => is_array($wazuhAgent['os']) ? ($wazuhAgent['os']['name'] ?? 'Unknown') : ($wazuhAgent['os'] ?? 'Unknown'),
                'os_version' => is_array($wazuhAgent['os']) ? ($wazuhAgent['os']['version'] ?? 'N/A') : 'N/A',
                'version' => $wazuhAgent['version'] ?? 'N/A',
                'status' => $wazuhAgent['status'] ?? 'unknown',
                'cluster_node' => $wazuhAgent['node_name'] ?? $wazuhAgent['group'] ?? 'N/A',
                'dateAdd' => $wazuhAgent['dateAdd'] ?? null,
                'lastKeepAlive' => $wazuhAgent['lastKeepAlive'] ?? null,
                'group' => is_array($wazuhAgent['group']) ? implode(', ', $wazuhAgent['group']) : ($wazuhAgent['group'] ?? 'N/A'),
                'manager' => $wazuhAgent['manager'] ?? 'N/A',
                'user' => null,
            ];

            // Enrich with database information
            $agent = $this->enrichAgentData($agent);

            // Fetch data from OpenSearch
            $alertStats = $this->openSearch->getAgentAlertStats($agent->id_agent);
            $fimEvents = $this->openSearch->getFimEvents($agent->id_agent, 5);
            $eventsEvolution = $this->openSearch->getEventsCountEvolution($agent->id_agent, '24h');
            
            // Fetch compliance data for all frameworks
            $complianceGdpr = $this->openSearch->getAgentCompliance($agent->id_agent, 'gdpr', '30d');
            $compliancePciDss = $this->openSearch->getAgentCompliance($agent->id_agent, 'pci_dss', '30d');
            $complianceNist = $this->openSearch->getAgentCompliance($agent->id_agent, 'nist_800_53', '30d');
            $complianceHipaa = $this->openSearch->getAgentCompliance($agent->id_agent, 'hipaa', '30d');
            $complianceGpg13 = $this->openSearch->getAgentCompliance($agent->id_agent, 'gpg13', '30d');
            $complianceTsc = $this->openSearch->getAgentCompliance($agent->id_agent, 'tsc', '30d');
            
            // Fetch events evolution for each compliance type (filtered)
            $eventsEvolutionGdpr = $this->openSearch->getEventsCountEvolutionByCompliance($agent->id_agent, 'gdpr', '24h');
            $eventsEvolutionPciDss = $this->openSearch->getEventsCountEvolutionByCompliance($agent->id_agent, 'pci_dss', '24h');
            $eventsEvolutionNist = $this->openSearch->getEventsCountEvolutionByCompliance($agent->id_agent, 'nist_800_53', '24h');
            $eventsEvolutionHipaa = $this->openSearch->getEventsCountEvolutionByCompliance($agent->id_agent, 'hipaa', '24h');
            $eventsEvolutionGpg13 = $this->openSearch->getEventsCountEvolutionByCompliance($agent->id_agent, 'gpg13', '24h');
            $eventsEvolutionTsc = $this->openSearch->getEventsCountEvolutionByCompliance($agent->id_agent, 'tsc', '24h');

            return view('agent.detail', compact(
                'agent', 'alertStats', 'fimEvents', 'eventsEvolution',
                'complianceGdpr', 'compliancePciDss', 'complianceNist', 'complianceHipaa', 'complianceGpg13', 'complianceTsc',
                'eventsEvolutionGdpr', 'eventsEvolutionPciDss', 'eventsEvolutionNist', 'eventsEvolutionHipaa', 'eventsEvolutionGpg13', 'eventsEvolutionTsc'
            ));
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('Agent detail timeout: ' . $e->getMessage());
            return view('agent.detail', [
                'agent' => null,
                'error' => 'Connection timeout while fetching agent details'
            ]);
        } catch (\Exception $e) {
            \Log::error('Agent detail error: ' . $e->getMessage());
            return view('agent.detail', [
                'agent' => null,
                'error' => 'Error loading agent details: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Sync agents from Wazuh API to database
     */
    public function syncAgentsFromWazuh()
    {
        try {
            // Check if user is authenticated and is admin
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Please login first'
                ], 401);
            }

            $user = auth()->user();
            $userRole = $user->peran ?? null;
            
            // Only allow admin users to sync
            if ($userRole !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Only admins can sync agents'
                ], 403);
            }

            \Log::info('Agent sync started', [
                'user_id' => $user->id_pengguna,
                'user_role' => $userRole
            ]);

            $token = $this->getToken();
            
            if (!$token) {
                \Log::warning('Agent sync failed: Unable to authenticate with Wazuh');
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to authenticate with Wazuh API'
                ], 500);
            }

            $syncedCount = 0;
            $updatedCount = 0;
            $errorCount = 0;
            $offset = 0;
            $limit = 100;
            $totalAgents = 0;
            $processedAgents = 0;

            \Log::info('Starting agent sync process', [
                'limit_per_page' => $limit
            ]);

            // Fetch all agents from Wazuh API in batches
            do {
                $wazuhData = $this->getAgentsFromWazuh($token, $offset, $limit);
                $agents = $wazuhData['agents'] ?? [];
                $totalAgents = $wazuhData['total'] ?? 0;

                if (empty($agents)) {
                    break;
                }

                foreach ($agents as $wazuhAgent) {
                    try {
                        $agentId = $wazuhAgent['id'] ?? null;
                        
                        if (!$agentId) {
                            \Log::warning('Skipping agent with no ID', ['agent' => $wazuhAgent]);
                            continue;
                        }

                        // Prepare data for sync (only id_agent and nama)
                        $agentData = [
                            'id_agent' => $agentId,
                            'nama' => $wazuhAgent['name'] ?? 'Unknown',
                        ];

                        // Check if agent exists
                        $existingAgent = Agent::where('id_agent', $agentId)->first();

                        if ($existingAgent) {
                            // Update existing agent (only update nama if needed)
                            if ($existingAgent->nama !== $agentData['nama']) {
                                $existingAgent->update($agentData);
                            }
                            $updatedCount++;
                            
                            \Log::debug('Agent updated', [
                                'id_agent' => $agentId
                            ]);
                        } else {
                            // Create new agent if it doesn't exist
                            $agentData['deskripsi'] = '';
                            $agentData['tanggal_dibuat'] = Carbon::now();
                            // id_pengguna is not set - agents are not assigned to any user by default

                            Agent::create($agentData);
                            $syncedCount++;
                            
                            \Log::debug('Agent created', [
                                'id_agent' => $agentId
                            ]);
                        }

                        $processedAgents++;

                    } catch (\Exception $e) {
                        $errorCount++;
                        \Log::error('Error syncing agent', [
                            'agent_id' => $agentId ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Move to next batch
                $offset += $limit;

            } while ($processedAgents < $totalAgents && count($agents) > 0);

            \Log::info('Agent sync completed', [
                'synced_new' => $syncedCount,
                'updated_existing' => $updatedCount,
                'errors' => $errorCount,
                'total_processed' => $processedAgents,
                'total_in_wazuh' => $totalAgents
            ]);

            return response()->json([
                'success' => true,
                'message' => "Agent sync completed successfully",
                'data' => [
                    'synced_new' => $syncedCount,
                    'updated_existing' => $updatedCount,
                    'total_processed' => $processedAgents,
                    'errors' => $errorCount,
                    'total_in_wazuh' => $totalAgents
                ]
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('Agent sync connection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Connection timeout: Unable to reach Wazuh API'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Agent sync error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show security events page
     */
    public function securityEvents($id)
    {
        try {
            if (!$this->userHasAccessToAgent($id)) {
                \Log::warning('Unauthorized security events access', [
                    'agent_id' => $id,
                    'user_id' => auth()->id()
                ]);
                
                return view('agent.security-events', [
                    'agent' => null,
                    'error' => 'You do not have permission to view this agent'
                ]);
            }
            
            $token = $this->getToken();
            
            if (!$token) {
                return view('agent.security-events', [
                    'agent' => null,
                    'error' => 'Unable to authenticate with Wazuh API'
                ]);
            }

            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(3)
                ->withToken($token)
                ->get("{$this->wazuhBase}/agents", [
                    'agents_list' => $id
                ]);

            if (!$response->successful() || empty($response->json('data.affected_items'))) {
                \Log::warning("Agent not found: $id");
                return view('agent.security-events', [
                    'agent' => null,
                    'error' => 'Agent not found'
                ]);
            }

            $wazuhAgent = $response->json('data.affected_items')[0];
            
            $agent = (object) [
                'id_agent' => $wazuhAgent['id'] ?? null,
                'nama' => $wazuhAgent['name'] ?? 'Unknown',
                'ip' => $wazuhAgent['ip'] ?? 'N/A',
                'os' => is_array($wazuhAgent['os']) ? ($wazuhAgent['os']['name'] ?? 'Unknown') : ($wazuhAgent['os'] ?? 'Unknown'),
                'status' => $wazuhAgent['status'] ?? 'unknown',
            ];

            $agent = $this->enrichAgentData($agent);

            // Fetch security events data from OpenSearch
            $openSearch = new \App\Services\OpenSearchService();
            $timeRange = request('time_range', '24h');
            
            $metrics = $openSearch->getSecurityEventsMetrics($id, 'now-' . $timeRange);
            $alertGroupsEvolution = $openSearch->getAlertGroupsEvolution($id, $timeRange);
            $alertsEvolutionByLevel = $openSearch->getAlertsEvolutionByLevel($id, $timeRange);
            $topAlerts = $openSearch->getTopAlerts($id, $timeRange, 5);
            $topRuleGroups = $openSearch->getTopRuleGroups($id, $timeRange, 5);
            $topPCIDSS = $openSearch->getTopPCIDSS($id, $timeRange, 5);
            $recentAlerts = $openSearch->getRecentAlerts($id, $timeRange, 10);

            return view('agent.security-events', compact(
                'agent',
                'metrics',
                'alertGroupsEvolution',
                'alertsEvolutionByLevel',
                'topAlerts',
                'topRuleGroups',
                'topPCIDSS',
                'recentAlerts',
                'timeRange'
            ));
        } catch (\Exception $e) {
            \Log::error('Security events error: ' . $e->getMessage());
            return view('agent.security-events', [
                'agent' => null,
                'error' => 'Error loading security events'
            ]);
        }
    }

    /**
     * Show integrity monitoring page
     */
    public function integrityMonitoring($id)
    {
        try {
            if (!$this->userHasAccessToAgent($id)) {
                return view('agent.integrity-monitoring', [
                    'agent' => null,
                    'error' => 'You do not have permission to view this agent'
                ]);
            }
            
            $token = $this->getToken();
            if (!$token) {
                return view('agent.integrity-monitoring', [
                    'agent' => null,
                    'error' => 'Unable to authenticate with Wazuh API'
                ]);
            }

            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(3)
                ->withToken($token)
                ->get("{$this->wazuhBase}/agents", [
                    'agents_list' => $id
                ]);

            if (!$response->successful() || empty($response->json('data.affected_items'))) {
                return view('agent.integrity-monitoring', [
                    'agent' => null,
                    'error' => 'Agent not found'
                ]);
            }

            $wazuhAgent = $response->json('data.affected_items')[0];
            
            $agent = (object) [
                'id_agent' => $wazuhAgent['id'] ?? null,
                'nama' => $wazuhAgent['name'] ?? 'Unknown',
                'ip' => $wazuhAgent['ip'] ?? 'N/A',
                'os' => is_array($wazuhAgent['os']) ? ($wazuhAgent['os']['name'] ?? 'Unknown') : ($wazuhAgent['os'] ?? 'Unknown'),
                'status' => $wazuhAgent['status'] ?? 'unknown',
            ];

            $agent = $this->enrichAgentData($agent);

            return view('agent.integrity-monitoring', compact('agent'));
        } catch (\Exception $e) {
            \Log::error('Integrity monitoring error: ' . $e->getMessage());
            return view('agent.integrity-monitoring', [
                'agent' => null,
                'error' => 'Error loading integrity monitoring'
            ]);
        }
    }

    /**
     * Show SCA page
     */
    public function sca($id)
    {
        try {
            if (!$this->userHasAccessToAgent($id)) {
                return view('agent.sca', [
                    'agent' => null,
                    'error' => 'You do not have permission to view this agent'
                ]);
            }
            
            $token = $this->getToken();
            if (!$token) {
                return view('agent.sca', [
                    'agent' => null,
                    'error' => 'Unable to authenticate with Wazuh API'
                ]);
            }

            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(3)
                ->withToken($token)
                ->get("{$this->wazuhBase}/agents", [
                    'agents_list' => $id
                ]);

            if (!$response->successful() || empty($response->json('data.affected_items'))) {
                return view('agent.sca', [
                    'agent' => null,
                    'error' => 'Agent not found'
                ]);
            }

            $wazuhAgent = $response->json('data.affected_items')[0];
            
            $agent = (object) [
                'id_agent' => $wazuhAgent['id'] ?? null,
                'nama' => $wazuhAgent['name'] ?? 'Unknown',
                'ip' => $wazuhAgent['ip'] ?? 'N/A',
                'os' => is_array($wazuhAgent['os']) ? ($wazuhAgent['os']['name'] ?? 'Unknown') : ($wazuhAgent['os'] ?? 'Unknown'),
                'status' => $wazuhAgent['status'] ?? 'unknown',
            ];

            $agent = $this->enrichAgentData($agent);

            return view('agent.sca', compact('agent'));
        } catch (\Exception $e) {
            \Log::error('SCA error: ' . $e->getMessage());
            return view('agent.sca', [
                'agent' => null,
                'error' => 'Error loading SCA'
            ]);
        }
    }

    /**
     * Show vulnerabilities page
     */
    public function vulnerabilities($id)
    {
        try {
            if (!$this->userHasAccessToAgent($id)) {
                return view('agent.vulnerabilities', [
                    'agent' => null,
                    'error' => 'You do not have permission to view this agent'
                ]);
            }
            
            $token = $this->getToken();
            if (!$token) {
                return view('agent.vulnerabilities', [
                    'agent' => null,
                    'error' => 'Unable to authenticate with Wazuh API'
                ]);
            }

            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(3)
                ->withToken($token)
                ->get("{$this->wazuhBase}/agents", [
                    'agents_list' => $id
                ]);

            if (!$response->successful() || empty($response->json('data.affected_items'))) {
                return view('agent.vulnerabilities', [
                    'agent' => null,
                    'error' => 'Agent not found'
                ]);
            }

            $wazuhAgent = $response->json('data.affected_items')[0];
            
            $agent = (object) [
                'id_agent' => $wazuhAgent['id'] ?? null,
                'nama' => $wazuhAgent['name'] ?? 'Unknown',
                'ip' => $wazuhAgent['ip'] ?? 'N/A',
                'os' => is_array($wazuhAgent['os']) ? ($wazuhAgent['os']['name'] ?? 'Unknown') : ($wazuhAgent['os'] ?? 'Unknown'),
                'status' => $wazuhAgent['status'] ?? 'unknown',
            ];

            $agent = $this->enrichAgentData($agent);

            return view('agent.vulnerabilities', compact('agent'));
        } catch (\Exception $e) {
            \Log::error('Vulnerabilities error: ' . $e->getMessage());
            return view('agent.vulnerabilities', [
                'agent' => null,
                'error' => 'Error loading vulnerabilities'
            ]);
        }
    }

    /**
     * Show MITRE ATT&CK page
     */
    public function mitreAttack($id)
    {
        try {
            if (!$this->userHasAccessToAgent($id)) {
                return view('agent.mitre-attack', [
                    'agent' => null,
                    'error' => 'You do not have permission to view this agent'
                ]);
            }
            
            $token = $this->getToken();
            if (!$token) {
                return view('agent.mitre-attack', [
                    'agent' => null,
                    'error' => 'Unable to authenticate with Wazuh API'
                ]);
            }

            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(3)
                ->withToken($token)
                ->get("{$this->wazuhBase}/agents", [
                    'agents_list' => $id
                ]);

            if (!$response->successful() || empty($response->json('data.affected_items'))) {
                return view('agent.mitre-attack', [
                    'agent' => null,
                    'error' => 'Agent not found'
                ]);
            }

            $wazuhAgent = $response->json('data.affected_items')[0];
            
            $agent = (object) [
                'id_agent' => $wazuhAgent['id'] ?? null,
                'nama' => $wazuhAgent['name'] ?? 'Unknown',
                'ip' => $wazuhAgent['ip'] ?? 'N/A',
                'os' => is_array($wazuhAgent['os']) ? ($wazuhAgent['os']['name'] ?? 'Unknown') : ($wazuhAgent['os'] ?? 'Unknown'),
                'status' => $wazuhAgent['status'] ?? 'unknown',
            ];

            $agent = $this->enrichAgentData($agent);

            return view('agent.mitre-attack', compact('agent'));
        } catch (\Exception $e) {
            \Log::error('MITRE ATT&CK error: ' . $e->getMessage());
            return view('agent.mitre-attack', [
                'agent' => null,
                'error' => 'Error loading MITRE ATT&CK'
            ]);
        }
    }
}
