<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\DashboardLayout;
use App\Services\OpenSearchService;
use App\Services\WazuhService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    private WazuhService $_wazuhService;
    private OpenSearchService $_openSearch;

    public function __construct()
    {
        $this->_wazuhService = new WazuhService();
        $this->_openSearch   = new OpenSearchService();
    }

    // ── Public actions ────────────────────────────────────────────────────────

    public function index()
    {
        try {
            $user    = auth()->user();
            $isAdmin = ($user->peran ?? null) === 'admin';
            $token   = $this->_wazuhService->getToken();
            $stats   = $this->buildFilteredStats($token, $isAdmin);
            $perPage = request('per_page', 10);
            $page    = max(request('page', 1), 1);
            $offset  = ($page - 1) * $perPage;

            $wazuhData     = $this->_wazuhService->getAgents($token ?? '', $offset, $perPage, request('search'), request('status'));
            $accessibleIds = $isAdmin ? null : $this->getAccessibleAgentIds();

            $agentsList = collect($wazuhData['agents'])
                ->map(fn($a) => $this->enrichAgentData($this->mapWazuhAgent($a)))
                ->filter(function ($agent) use ($accessibleIds, $isAdmin) {
                    if ($agent->id_agent === '000') return false;
                    return $isAdmin || in_array($agent->id_agent, $accessibleIds);
                });

            $agents = new \Illuminate\Pagination\LengthAwarePaginator(
                items: $agentsList->forPage($page, $perPage)->values(),
                total: $wazuhData['total'],
                perPage: $perPage,
                currentPage: $page,
                options: ['path' => route('agent'), 'query' => request()->query()]
            );

            $sessionKey         = 'agent_evolution_base_time_' . floor(date('n'));
            $baseTime           = session($sessionKey) ?? tap(Carbon::now(), fn($t) => session([$sessionKey => $t]));
            $accessibleAgentIds = $isAdmin ? null : array_map('strval', $this->getAccessibleAgentIds());
            $evolution          = $this->getAgentEvolution('24h', $accessibleAgentIds, $isAdmin, $baseTime);
            $evolutionLabels    = json_encode($evolution['labels'] ?? []);
            $evolutionData      = json_encode($evolution['data']['active'] ?? $evolution['data'] ?? []);

            $savedLayout = DashboardLayout::where('id_pengguna', auth()->user()->id_pengguna)
                                          ->where('page', 'agent')
                                          ->value('layout');

            return view('agent.index', compact('agents', 'stats', 'evolutionLabels', 'evolutionData', 'savedLayout'));
        } catch (\Exception $e) {
            Log::error('Agent index error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->indexErrorView();
        }
    }

    public function getChartData()
    {
        try {
            $timeRange  = request('time_range', '24h');
            $sessionKey = 'agent_evolution_base_time_' . floor(date('n'));
            $baseTime   = session($sessionKey) ?? tap(Carbon::now(), fn($t) => session([$sessionKey => $t]));
            $evolution  = $this->getAgentEvolution($timeRange, null, true, $baseTime);

            return response()->json(['success' => true, 'labels' => $evolution['labels'] ?? [], 'data' => $evolution['data'] ?? []]);
        } catch (\Exception $e) {
            Log::error('Get chart data error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch chart data'], 500);
        }
    }

    public function getDetailChartData($id)
    {
        try {
            $agentId = (string) $id;
            if (!$this->userHasAccessToAgent($agentId)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $timeRange      = request('time_range', '24h');
            $complianceType = request('compliance_type', 'gdpr');

            return response()->json([
                'success'                     => true,
                'events_evolution'            => $this->_openSearch->getEventsCountEvolution($agentId, $timeRange),
                'compliance_data'             => $this->_openSearch->getAgentCompliance($agentId, $complianceType, $timeRange),
                'events_compliance_evolution' => $this->_openSearch->getEventsCountEvolutionByCompliance($agentId, $complianceType, $timeRange),
                'mitre_tactics'               => $this->_openSearch->getMitreTactics($agentId, $timeRange),
            ]);
        } catch (\Exception $e) {
            Log::error('Get detail chart data error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch chart data'], 500);
        }
    }

    public function detail($id)
    {
        try {
            if (!$this->userHasAccessToAgent($id)) {
                return view('agent.detail', ['agent' => null, 'error' => 'You do not have permission to view this agent']);
            }

            $token = $this->_wazuhService->getToken();
            if (!$token) {
                return view('agent.detail', ['agent' => null, 'error' => 'Unable to authenticate with Wazuh API']);
            }

            $wa = $this->_wazuhService->getAgent($token, $id);
            if (!$wa) {
                return view('agent.detail', ['agent' => null, 'error' => 'Agent not found']);
            }

            $agent = $this->enrichAgentData($this->mapWazuhAgent($wa, true));

            $savedLayout = DashboardLayout::where('id_pengguna', auth()->user()->id_pengguna)
                                          ->where('page', 'agent-detail')
                                          ->value('layout');

            return view('agent.detail', array_merge(compact('agent', 'savedLayout'), $this->buildDetailData($agent->id_agent)));
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Agent detail timeout: ' . $e->getMessage());
            return view('agent.detail', ['agent' => null, 'error' => 'Connection timeout while fetching agent details']);
        } catch (\Exception $e) {
            Log::error('Agent detail error: ' . $e->getMessage());
            return view('agent.detail', ['agent' => null, 'error' => 'Error loading agent details']);
        }
    }

    public function securityEvents($id)
    {
        try {
            if (!$this->userHasAccessToAgent($id)) {
                return view('agent.security-events', ['agent' => null, 'error' => 'You do not have permission to view this agent']);
            }

            $agent = $this->resolveAgent($id);
            if (!$agent) {
                return view('agent.security-events', ['agent' => null, 'error' => 'Agent not found or API unavailable']);
            }

            $timeRange    = request('time_range', '24h');
            $perPage      = in_array((int) request('per_page', 10), [10, 25, 50]) ? (int) request('per_page', 10) : 10;
            $page         = max((int) request('page', 1), 1);
            $offset       = ($page - 1) * $perPage;
            $groupsPerPage = in_array((int) request('groups_per_page', 10), [10, 25, 50]) ? (int) request('groups_per_page', 10) : 10;
            $groupsPage    = max((int) request('groups_page', 1), 1);
            $groupsOffset  = ($groupsPage - 1) * $groupsPerPage;

            $savedLayout  = DashboardLayout::where('id_pengguna', auth()->user()->id_pengguna)
                                           ->where('page', 'security-events')
                                           ->value('layout');

            $alertsResult = $this->_openSearch->getRecentAlerts($id, $timeRange, $perPage, $offset);
            $groupsResult = $this->_openSearch->getGroupsSummary($id, $timeRange, $groupsPerPage, $groupsOffset);

            return view('agent.security-events', array_merge(compact('agent', 'timeRange', 'savedLayout', 'page', 'perPage', 'groupsPage', 'groupsPerPage'), [
                'metrics'                => $this->_openSearch->getSecurityEventsMetrics($id, 'now-' . $timeRange),
                'alertGroupsEvolution'   => $this->_openSearch->getAlertGroupsEvolution($id, $timeRange),
                'alertsEvolutionByLevel' => $this->_openSearch->getAlertsEvolutionByLevel($id, $timeRange),
                'topAlerts'              => $this->_openSearch->getTopAlerts($id, $timeRange, 5),
                'topRuleGroups'          => $this->_openSearch->getTopRuleGroups($id, $timeRange, 5),
                'topPCIDSS'              => $this->_openSearch->getTopPCIDSS($id, $timeRange, 5),
                'recentAlerts'           => $alertsResult['data'],
                'totalAlerts'            => $alertsResult['total'],
                'groupsSummary'          => $groupsResult['data'],
                'totalGroups'            => $groupsResult['total'],
            ]));
        } catch (\Exception $e) {
            Log::error('Security events error: ' . $e->getMessage());
            return view('agent.security-events', ['agent' => null, 'error' => 'Error loading security events']);
        }
    }

    public function getSeAlerts($id)
    {
        if (!$this->userHasAccessToAgent($id)) return response()->json(['error' => 'Forbidden'], 403);
        $timeRange = request('time_range', '24h');
        $perPage   = in_array((int) request('per_page', 10), [10, 25, 50]) ? (int) request('per_page', 10) : 10;
        $page      = max((int) request('page', 1), 1);
        $result    = $this->_openSearch->getRecentAlerts($id, $timeRange, $perPage, ($page - 1) * $perPage);
        return response()->json(['data' => $result['data'], 'total' => $result['total'], 'page' => $page, 'perPage' => $perPage]);
    }

    public function getSeGroups($id)
    {
        if (!$this->userHasAccessToAgent($id)) return response()->json(['error' => 'Forbidden'], 403);
        $timeRange = request('time_range', '24h');
        $perPage   = in_array((int) request('per_page', 10), [10, 25, 50]) ? (int) request('per_page', 10) : 10;
        $page      = max((int) request('page', 1), 1);
        $result    = $this->_openSearch->getGroupsSummary($id, $timeRange, $perPage, ($page - 1) * $perPage);
        return response()->json(['data' => $result['data'], 'total' => $result['total'], 'page' => $page, 'perPage' => $perPage]);
    }

    public function getIntegrityEvents($id)
    {
        if (!$this->userHasAccessToAgent($id)) return response()->json(['error' => 'Forbidden'], 403);
        $timeRange = request('time_range', '24h');
        $perPage   = in_array((int) request('per_page', 10), [10, 25, 50]) ? (int) request('per_page', 10) : 10;
        $page      = max((int) request('page', 1), 1);
        $result    = $this->_openSearch->getFimEventsPaginated($id, $timeRange, $perPage, ($page - 1) * $perPage);
        return response()->json(['data' => $result['data'], 'total' => $result['total'], 'page' => $page, 'perPage' => $perPage]);
    }

    public function getScaChecksJson($id)
    {
        if (!$this->userHasAccessToAgent($id)) return response()->json(['error' => 'Forbidden'], 403);
        try {
            $token          = $this->_wazuhService->getToken();
            $policies       = $token ? $this->_wazuhService->getSCAPolicies($token, $id) : [];
            $policyId       = request('policy_id', $policies[0]['policy_id'] ?? null);
            $selectedPolicy = collect($policies)->firstWhere('policy_id', $policyId);
            $resultFilter   = in_array(request('result'), ['passed', 'failed', 'not_applicable']) ? request('result') : null;
            $perPage        = in_array((int) request('per_page', 10), [10, 25, 50]) ? (int) request('per_page', 10) : 10;
            $page           = max((int) request('page', 1), 1);
            $checksResult   = ($token && $policyId)
                ? $this->_wazuhService->getSCAChecks($token, $id, $policyId, $perPage, ($page - 1) * $perPage, $resultFilter)
                : ['data' => [], 'total' => 0];
            return response()->json([
                'checks'         => $checksResult['data'],
                'total'          => $checksResult['total'],
                'page'           => $page,
                'perPage'        => $perPage,
                'selectedPolicy' => $selectedPolicy,
                'policyId'       => $policyId,
                'resultFilter'   => $resultFilter,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load SCA checks'], 500);
        }
    }

    public function integrityMonitoring($id)
    {
        try {
            if (!$this->userHasAccessToAgent($id)) {
                return view('agent.integrity-monitoring', ['agent' => null, 'error' => 'You do not have permission to view this agent']);
            }
            $agent = $this->resolveAgent($id);
            if (!$agent) {
                return view('agent.integrity-monitoring', ['agent' => null, 'error' => 'Agent not found or API unavailable']);
            }

            $timeRange = request('time_range', '24h');
            $perPage   = in_array((int) request('per_page', 10), [10, 25, 50]) ? (int) request('per_page', 10) : 10;
            $page      = max((int) request('page', 1), 1);
            $offset    = ($page - 1) * $perPage;

            $savedLayout = DashboardLayout::where('id_pengguna', auth()->user()->id_pengguna)
                                          ->where('page', 'integrity-monitoring')
                                          ->value('layout');

            $eventsResult = $this->_openSearch->getFimEventsPaginated($id, $timeRange, $perPage, $offset);

            return view('agent.integrity-monitoring', array_merge(compact('agent', 'timeRange', 'savedLayout', 'page', 'perPage'), [
                'fimSummary'     => $this->_openSearch->getFimSummary($id, $timeRange),
                'fimEvolution'   => $this->_openSearch->getFimEvolution($id, $timeRange),
                'fimTopRules'    => $this->_openSearch->getFimTopRules($id, $timeRange, 5),
                'fimTopModified' => $this->_openSearch->getFimTopFiles($id, $timeRange, 'modified', 5),
                'fimTopDeleted'  => $this->_openSearch->getFimTopFiles($id, $timeRange, 'deleted', 5),
                'fimTopAdded'    => $this->_openSearch->getFimTopFiles($id, $timeRange, 'added', 5),
                'fimEvents'      => $eventsResult['data'],
                'totalEvents'    => $eventsResult['total'],
            ]));
        } catch (\Exception $e) {
            Log::error('Integrity monitoring error: ' . $e->getMessage());
            return view('agent.integrity-monitoring', ['agent' => null, 'error' => 'Error loading integrity monitoring']);
        }
    }

    public function sca($id)
    {
        try {
            if (!$this->userHasAccessToAgent($id)) {
                return view('agent.sca', ['agent' => null, 'error' => 'You do not have permission to view this agent']);
            }
            $agent = $this->resolveAgent($id);
            if (!$agent) {
                return view('agent.sca', ['agent' => null, 'error' => 'Agent not found or API unavailable']);
            }

            $token    = $this->_wazuhService->getToken();
            $policies = $token ? $this->_wazuhService->getSCAPolicies($token, $id) : [];

            $policyId       = request('policy_id', $policies[0]['policy_id'] ?? null);
            $selectedPolicy = collect($policies)->firstWhere('policy_id', $policyId);
            $resultFilter   = in_array(request('result'), ['passed', 'failed', 'not_applicable']) ? request('result') : null;
            $perPage        = in_array((int) request('per_page', 10), [10, 25, 50]) ? (int) request('per_page', 10) : 10;
            $page           = max((int) request('page', 1), 1);
            $offset         = ($page - 1) * $perPage;

            $checksResult = ($token && $policyId)
                ? $this->_wazuhService->getSCAChecks($token, $id, $policyId, $perPage, $offset, $resultFilter)
                : ['data' => [], 'total' => 0];

            $totalPass = (int) array_sum(array_column($policies, 'pass'));
            $totalFail = (int) array_sum(array_column($policies, 'fail'));
            $totalNA   = (int) array_sum(array_column($policies, 'not_applicable'));
            $avgScore  = count($policies) > 0
                ? (int) round(array_sum(array_column($policies, 'score')) / count($policies))
                : 0;

            $savedLayout = DashboardLayout::where('id_pengguna', auth()->user()->id_pengguna)
                                          ->where('page', 'sca')
                                          ->value('layout');

            return view('agent.sca', array_merge(
                compact('agent', 'policies', 'selectedPolicy', 'policyId', 'resultFilter', 'page', 'perPage', 'savedLayout'),
                [
                    'checks'      => $checksResult['data'],
                    'totalChecks' => $checksResult['total'],
                    'totalPass'   => $totalPass,
                    'totalFail'   => $totalFail,
                    'totalNA'     => $totalNA,
                    'avgScore'    => $avgScore,
                ]
            ));
        } catch (\Exception $e) {
            Log::error('SCA error: ' . $e->getMessage());
            return view('agent.sca', ['agent' => null, 'error' => 'Error loading SCA']);
        }
    }

    public function vulnerabilities($id)
    {
        try {
            if (!$this->userHasAccessToAgent($id)) {
                return view('agent.vulnerabilities', ['agent' => null, 'error' => 'You do not have permission to view this agent']);
            }
            $agent = $this->resolveAgent($id);
            if (!$agent) {
                return view('agent.vulnerabilities', ['agent' => null, 'error' => 'Agent not found or API unavailable']);
            }

            $token    = $this->_wazuhService->getToken();
            $severity = in_array(request('severity'), ['Critical', 'High', 'Medium', 'Low']) ? request('severity') : null;
            $perPage  = in_array((int) request('per_page', 10), [10, 25, 50]) ? (int) request('per_page', 10) : 10;
            $page     = max((int) request('page', 1), 1);
            $offset   = ($page - 1) * $perPage;

            $vulnResult = $token
                ? $this->_wazuhService->getVulnerabilities($token, $id, $perPage, $offset, $severity)
                : ['data' => [], 'total' => 0];

            $severityCounts = ['Critical' => 0, 'High' => 0, 'Medium' => 0, 'Low' => 0];
            foreach (['Critical', 'High', 'Medium', 'Low'] as $sev) {
                $severityCounts[$sev] = $token
                    ? $this->_wazuhService->getVulnerabilities($token, $id, 1, 0, $sev)['total']
                    : 0;
            }

            $summaryBatch = $token
                ? $this->_wazuhService->getVulnerabilities($token, $id, 100, 0, null)['data']
                : [];

            $lastScan = $token ? $this->_wazuhService->getVulnerabilitiesLastScan($token, $id) : null;

            $savedLayout = DashboardLayout::where('id_pengguna', auth()->user()->id_pengguna)
                                          ->where('page', 'vulnerabilities')
                                          ->value('layout');

            return view('agent.vulnerabilities', compact('agent', 'page', 'perPage', 'severity', 'severityCounts', 'summaryBatch', 'lastScan', 'savedLayout') + [
                'vulnerabilities' => $vulnResult['data'],
                'totalVulns'      => $vulnResult['total'],
            ]);
        } catch (\Exception $e) {
            Log::error('Vulnerabilities error: ' . $e->getMessage());
            return view('agent.vulnerabilities', ['agent' => null, 'error' => 'Error loading vulnerabilities']);
        }
    }

    public function mitreAttack($id)
    {
        try {
            if (!$this->userHasAccessToAgent($id)) {
                return view('agent.mitre-attack', ['agent' => null, 'error' => 'You do not have permission to view this agent']);
            }
            $agent = $this->resolveAgent($id);
            if (!$agent) {
                return view('agent.mitre-attack', ['agent' => null, 'error' => 'Agent not found or API unavailable']);
            }

            $timeRange = request('time_range', '24h');
            $perPage   = in_array((int) request('per_page', 10), [10, 25, 50]) ? (int) request('per_page', 10) : 10;
            $page      = max((int) request('page', 1), 1);
            $offset    = ($page - 1) * $perPage;

            $alertsResult = $this->_openSearch->getMitreAlerts($id, $timeRange, $perPage, $offset);

            $savedLayout = DashboardLayout::where('id_pengguna', auth()->user()->id_pengguna)
                                          ->where('page', 'mitre-attack')
                                          ->value('layout');

            return view('agent.mitre-attack', compact('agent', 'timeRange', 'page', 'perPage', 'savedLayout') + [
                'tactics'    => $this->_openSearch->getMitreTactics($id, $timeRange),
                'techniques' => $this->_openSearch->getMitreTechniques($id, $timeRange),
                'alerts'     => $alertsResult['data'],
                'totalAlerts' => $alertsResult['total'],
            ]);
        } catch (\Exception $e) {
            Log::error('MITRE ATT&CK error: ' . $e->getMessage());
            return view('agent.mitre-attack', ['agent' => null, 'error' => 'Error loading MITRE ATT&CK data']);
        }
    }

    public function syncAgentsFromWazuh()
    {
        try {
            if (!auth()->check()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized: Please login first'], 401);
            }
            if (auth()->user()->peran !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized: Only admins can sync agents'], 403);
            }

            $result = $this->syncFromWazuh();

            return response()->json(
                $result['success']
                    ? ['success' => true, 'message' => 'Agent sync completed successfully', 'data' => $result]
                    : ['success' => false, 'message' => $result['message'] ?? 'Sync failed'],
                $result['success'] ? 200 : 500
            );
        } catch (\Exception $e) {
            Log::error('Agent sync error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Sync failed: ' . $e->getMessage()], 500);
        }
    }

    // ── Static view helpers ───────────────────────────────────────────────────

    public static function getOSIcon(?string $os): string
    {
        if (!$os) return 'mdi-help-circle-outline';
        $os = strtolower($os);
        if (str_contains($os, 'windows')) return 'mdi-microsoft-windows';
        if (str_contains($os, 'ubuntu') || str_contains($os, 'debian') || str_contains($os, 'linux')
            || str_contains($os, 'centos') || str_contains($os, 'rhel') || str_contains($os, 'fedora')) {
            return 'mdi-linux';
        }
        if (str_contains($os, 'mac') || str_contains($os, 'darwin')) return 'mdi-apple';
        return 'mdi-help-circle-outline';
    }

    public static function getStatusBadgeColor(?string $status): string
    {
        return match ($status) {
            'active'          => 'success',
            'disconnected'    => 'danger',
            'pending'         => 'warning',
            'never_connected' => 'secondary',
            default           => 'secondary',
        };
    }

    public static function formatStatus(?string $status): string
    {
        return match ($status) {
            'active'          => 'Active',
            'disconnected'    => 'Disconnected',
            'pending'         => 'Pending',
            'never_connected' => 'Never Connected',
            default           => ucfirst($status ?? ''),
        };
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function userHasAccessToAgent(string $agentId): bool
    {
        $user    = auth()->user();
        $agentId = (string) $agentId;

        if ($user->peran === 'admin') return true;

        $dbAgent = Agent::where('id_agent', $agentId)->first();
        if (!$dbAgent) {
            Log::warning('Agent not found in database', ['agent_id' => $agentId, 'user_id' => $user->id_pengguna]);
            return false;
        }

        $hasAccess = $dbAgent->id_pengguna === $user->id_pengguna;
        if (!$hasAccess) {
            Log::warning('Customer does not have access to agent', ['agent_id' => $agentId, 'user_id' => $user->id_pengguna]);
        }

        return $hasAccess;
    }

    private function getAccessibleAgentIds(): array
    {
        $user = auth()->user();
        if ($user->peran === 'admin') return Agent::pluck('id_agent')->toArray();
        return Agent::where('id_pengguna', $user->id_pengguna)->pluck('id_agent')->toArray();
    }

    private function enrichAgentData(object $agent): object
    {
        $agentId = $agent->id_agent ?? null;
        if ($agentId) {
            $dbAgent = Agent::where('id_agent', $agentId)->with('user')->first();
            if ($dbAgent) $agent->user = $dbAgent->user;
        }
        return $agent;
    }

    private function mapWazuhAgent(array $wa, bool $full = false): object
    {
        $agent = (object) [
            'id_agent'     => $wa['id'] ?? null,
            'nama'         => $wa['name'] ?? 'Unknown',
            'ip'           => $wa['ip'] ?? 'N/A',
            'os'           => is_array($wa['os']) ? ($wa['os']['name'] ?? 'Unknown') : ($wa['os'] ?? 'Unknown'),
            'version'      => $wa['version'] ?? 'N/A',
            'status'       => $wa['status'] ?? 'unknown',
            'cluster_node' => $wa['node_name'] ?? (is_array($wa['group'] ?? null) ? implode(', ', $wa['group']) : ($wa['group'] ?? 'N/A')),
            'user'         => null,
        ];

        if ($full) {
            $agent->os_version    = is_array($wa['os'] ?? null) ? ($wa['os']['version'] ?? 'N/A') : 'N/A';
            $agent->dateAdd       = $wa['dateAdd'] ?? null;
            $agent->lastKeepAlive = $wa['lastKeepAlive'] ?? null;
            $agent->group         = is_array($wa['group'] ?? null) ? implode(', ', $wa['group']) : ($wa['group'] ?? 'N/A');
            $agent->manager       = $wa['manager'] ?? 'N/A';
        }

        return $agent;
    }

    private function syncFromWazuh(): array
    {
        $token = $this->_wazuhService->getToken();
        if (!$token) return ['success' => false, 'message' => 'Failed to authenticate with Wazuh API'];

        $synced = $updated = $errors = $processed = $total = 0;
        $offset    = 0;
        $limit     = 100;
        $syncedIds = [];

        do {
            $data   = $this->_wazuhService->getAgents($token, $offset, $limit);
            $agents = $data['agents'] ?? [];
            $total  = $data['total'] ?? 0;

            if (empty($agents)) break;

            foreach ($agents as $wa) {
                $agentId = $wa['id'] ?? null;
                if (!$agentId || $agentId === '000') continue;

                try {
                    $agentData   = ['id_agent' => $agentId, 'nama' => $wa['name'] ?? 'Unknown'];
                    $existing    = Agent::where('id_agent', $agentId)->first();
                    $syncedIds[] = $agentId;

                    if ($existing) {
                        if ($existing->nama !== $agentData['nama']) $existing->update($agentData);
                        $updated++;
                    } else {
                        Agent::create(array_merge($agentData, ['deskripsi' => '', 'tanggal_dibuat' => Carbon::now()]));
                        $synced++;
                    }
                    $processed++;
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Error syncing agent', ['agent_id' => $agentId, 'error' => $e->getMessage()]);
                }
            }

            $offset += $limit;
        } while ($processed < $total && count($agents) > 0);

        $deleted = Agent::whereNotIn('id_agent', $syncedIds)->delete();

        Log::info('Agent sync completed', compact('synced', 'updated', 'deleted', 'errors', 'processed', 'total'));

        return ['success' => true, 'synced_new' => $synced, 'updated_existing' => $updated, 'deleted_obsolete' => $deleted, 'total_processed' => $processed, 'errors' => $errors, 'total_in_wazuh' => $total];
    }

    private function buildFilteredStats(?string $token, bool $isAdmin): array
    {
        $empty = ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0];
        if (!$token) return $empty;

        if ($isAdmin) return $this->_wazuhService->getAgentSummaryStatus($token);

        $accessibleIds = $this->getAccessibleAgentIds();
        if (empty($accessibleIds)) return $empty;

        $stats = $empty;
        try {
            $data = $this->_wazuhService->getAgents($token, 0, 50000);
            foreach ($data['agents'] as $a) {
                if (in_array($a['id'], $accessibleIds)) {
                    $stats['total']++;
                    $status = $a['status'] ?? 'unknown';
                    if (isset($stats[$status])) $stats[$status]++;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch filtered agent stats: ' . $e->getMessage());
        }

        return $stats;
    }

    private function getAgentEvolution(string $timeRange = '24h', ?array $agentIds = null, bool $isAdmin = true, ?Carbon $baseTime = null): array
    {
        try {
            return $this->_openSearch->getAgentEvolutionByTimeRange($timeRange, $agentIds, $baseTime, $isAdmin);
        } catch (\Exception $e) {
            Log::error('Failed to fetch agent evolution data', ['error' => $e->getMessage()]);
            return ['labels' => [], 'data' => []];
        }
    }

    private function resolveAgent(string $id): ?object
    {
        $token = $this->_wazuhService->getToken();
        if (!$token) return null;

        $wa = $this->_wazuhService->getAgent($token, $id);
        if (!$wa) return null;

        return $this->enrichAgentData($this->mapWazuhAgent($wa));
    }

    private function resolveAgentView(string $id, string $view)
    {
        try {
            if (!$this->userHasAccessToAgent($id)) {
                return view($view, ['agent' => null, 'error' => 'You do not have permission to view this agent']);
            }
            $agent = $this->resolveAgent($id);
            if (!$agent) {
                return view($view, ['agent' => null, 'error' => 'Agent not found or API unavailable']);
            }
            return view($view, compact('agent'));
        } catch (\Exception $e) {
            Log::error("$view error: " . $e->getMessage());
            return view($view, ['agent' => null, 'error' => 'Error loading page']);
        }
    }

    private function buildDetailData(string $agentId): array
    {
        return [
            'alertStats'              => $this->_openSearch->getAgentAlertStats($agentId),
            'fimEvents'               => $this->_openSearch->getFimEvents($agentId, 5),
            'eventsEvolution'         => $this->_openSearch->getEventsCountEvolution($agentId, '24h'),
            'complianceGdpr'          => $this->_openSearch->getAgentCompliance($agentId, 'gdpr', '30d'),
            'compliancePciDss'        => $this->_openSearch->getAgentCompliance($agentId, 'pci_dss', '30d'),
            'complianceNist'          => $this->_openSearch->getAgentCompliance($agentId, 'nist_800_53', '30d'),
            'complianceHipaa'         => $this->_openSearch->getAgentCompliance($agentId, 'hipaa', '30d'),
            'complianceGpg13'         => $this->_openSearch->getAgentCompliance($agentId, 'gpg13', '30d'),
            'complianceTsc'           => $this->_openSearch->getAgentCompliance($agentId, 'tsc', '30d'),
            'eventsEvolutionGdpr'     => $this->_openSearch->getEventsCountEvolutionByCompliance($agentId, 'gdpr', '24h'),
            'eventsEvolutionPciDss'   => $this->_openSearch->getEventsCountEvolutionByCompliance($agentId, 'pci_dss', '24h'),
            'eventsEvolutionNist'     => $this->_openSearch->getEventsCountEvolutionByCompliance($agentId, 'nist_800_53', '24h'),
            'eventsEvolutionHipaa'    => $this->_openSearch->getEventsCountEvolutionByCompliance($agentId, 'hipaa', '24h'),
            'eventsEvolutionGpg13'    => $this->_openSearch->getEventsCountEvolutionByCompliance($agentId, 'gpg13', '24h'),
            'eventsEvolutionTsc'      => $this->_openSearch->getEventsCountEvolutionByCompliance($agentId, 'tsc', '24h'),
            'mitreTactics'            => $this->_openSearch->getMitreTactics($agentId, '24h'),
        ];
    }

    private function indexErrorView(): \Illuminate\View\View
    {
        $agents = new \Illuminate\Pagination\LengthAwarePaginator(
            items: [], total: 0, perPage: 10, currentPage: 1,
            options: ['path' => route('agent'), 'query' => request()->query()]
        );

        return view('agent.index', [
            'agents'          => $agents,
            'stats'           => ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0],
            'evolutionLabels' => json_encode([]),
            'evolutionData'   => json_encode([]),
        ]);
    }
}
