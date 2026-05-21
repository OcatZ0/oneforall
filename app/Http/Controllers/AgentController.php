<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\IAgentService;
use App\Services\Interfaces\IOpenSearchService;
use App\Services\Interfaces\IWazuhService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    public function __construct(
        private IWazuhService $wazuhService,
        private IAgentService $agentService,
        private IOpenSearchService $openSearch
    ) {}

    public function index()
    {
        try {
            $user     = auth()->user();
            $isAdmin  = ($user->peran ?? null) === 'admin';
            $token    = $this->wazuhService->getToken();
            $stats    = $this->buildFilteredStats($token, $isAdmin);
            $perPage  = request('per_page', 10);
            $page     = max(request('page', 1), 1);
            $offset   = ($page - 1) * $perPage;

            $wazuhData     = $this->wazuhService->getAgents($token ?? '', $offset, $perPage, request('search'), request('status'));
            $accessibleIds = $isAdmin ? null : $this->agentService->getAccessibleAgentIds();

            $agentsList = collect($wazuhData['agents'])
                ->map(fn($a) => $this->agentService->enrichAgentData($this->agentService->mapWazuhAgent($a)))
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

            $sessionKey          = 'agent_evolution_base_time_' . floor(date('n'));
            $baseTime            = session($sessionKey) ?? tap(Carbon::now(), fn($t) => session([$sessionKey => $t]));
            $accessibleAgentIds  = $isAdmin ? null : array_map('strval', $this->agentService->getAccessibleAgentIds());
            $evolution           = $this->getAgentEvolution('24h', $accessibleAgentIds, $isAdmin, $baseTime);
            $evolutionLabels     = json_encode($evolution['labels'] ?? []);
            $evolutionData       = json_encode($evolution['data']['active'] ?? $evolution['data'] ?? []);

            return view('agent.index', compact('agents', 'stats', 'evolutionLabels', 'evolutionData'));
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
            if (!$this->agentService->userHasAccessToAgent($agentId)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized - You do not have access to this agent'], 403);
            }

            $timeRange      = request('time_range', '24h');
            $complianceType = request('compliance_type', 'gdpr');

            return response()->json([
                'success'                     => true,
                'events_evolution'            => $this->openSearch->getEventsCountEvolution($agentId, $timeRange),
                'compliance_data'             => $this->openSearch->getAgentCompliance($agentId, $complianceType, $timeRange),
                'events_compliance_evolution' => $this->openSearch->getEventsCountEvolutionByCompliance($agentId, $complianceType, $timeRange),
                'mitre_tactics'               => $this->openSearch->getMitreTactics($agentId, $timeRange),
            ]);
        } catch (\Exception $e) {
            Log::error('Get detail chart data error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch chart data'], 500);
        }
    }

    public function detail($id)
    {
        try {
            if (!$this->agentService->userHasAccessToAgent($id)) {
                return view('agent.detail', ['agent' => null, 'error' => 'You do not have permission to view this agent']);
            }

            $token = $this->wazuhService->getToken();
            if (!$token) {
                return view('agent.detail', ['agent' => null, 'error' => 'Unable to authenticate with Wazuh API']);
            }

            $wa = $this->wazuhService->getAgent($token, $id);
            if (!$wa) {
                return view('agent.detail', ['agent' => null, 'error' => 'Agent not found']);
            }

            $agent = $this->agentService->enrichAgentData($this->agentService->mapWazuhAgent($wa, true));

            return view('agent.detail', array_merge(compact('agent'), $this->buildDetailData($agent->id_agent)));
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
            if (!$this->agentService->userHasAccessToAgent($id)) {
                return view('agent.security-events', ['agent' => null, 'error' => 'You do not have permission to view this agent']);
            }

            $agent = $this->resolveAgent($id);
            if (!$agent) {
                return view('agent.security-events', ['agent' => null, 'error' => 'Agent not found or API unavailable']);
            }

            $timeRange = request('time_range', '24h');

            return view('agent.security-events', array_merge(compact('agent', 'timeRange'), [
                'metrics'                => $this->openSearch->getSecurityEventsMetrics($id, 'now-' . $timeRange),
                'alertGroupsEvolution'   => $this->openSearch->getAlertGroupsEvolution($id, $timeRange),
                'alertsEvolutionByLevel' => $this->openSearch->getAlertsEvolutionByLevel($id, $timeRange),
                'topAlerts'              => $this->openSearch->getTopAlerts($id, $timeRange, 5),
                'topRuleGroups'          => $this->openSearch->getTopRuleGroups($id, $timeRange, 5),
                'topPCIDSS'              => $this->openSearch->getTopPCIDSS($id, $timeRange, 5),
                'recentAlerts'           => $this->openSearch->getRecentAlerts($id, $timeRange, 10),
            ]));
        } catch (\Exception $e) {
            Log::error('Security events error: ' . $e->getMessage());
            return view('agent.security-events', ['agent' => null, 'error' => 'Error loading security events']);
        }
    }

    public function integrityMonitoring($id)
    {
        return $this->resolveAgentView($id, 'agent.integrity-monitoring');
    }

    public function sca($id)
    {
        return $this->resolveAgentView($id, 'agent.sca');
    }

    public function vulnerabilities($id)
    {
        return $this->resolveAgentView($id, 'agent.vulnerabilities');
    }

    public function mitreAttack($id)
    {
        return $this->resolveAgentView($id, 'agent.mitre-attack');
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

            $result = $this->agentService->syncFromWazuh();

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

    private function buildFilteredStats(?string $token, bool $isAdmin): array
    {
        $empty = ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0];
        if (!$token) return $empty;

        if ($isAdmin) return $this->wazuhService->getAgentSummaryStatus($token);

        $accessibleIds = $this->agentService->getAccessibleAgentIds();
        if (empty($accessibleIds)) return $empty;

        $stats = $empty;
        try {
            $data = $this->wazuhService->getAgents($token, 0, 50000);
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
            return $this->openSearch->getAgentEvolutionByTimeRange($timeRange, $agentIds, $baseTime, $isAdmin);
        } catch (\Exception $e) {
            Log::error('Failed to fetch agent evolution data', ['error' => $e->getMessage()]);
            return ['labels' => [], 'data' => []];
        }
    }

    private function resolveAgent(string $id): ?object
    {
        $token = $this->wazuhService->getToken();
        if (!$token) return null;

        $wa = $this->wazuhService->getAgent($token, $id);
        if (!$wa) return null;

        return $this->agentService->enrichAgentData($this->agentService->mapWazuhAgent($wa));
    }

    private function resolveAgentView(string $id, string $view)
    {
        try {
            if (!$this->agentService->userHasAccessToAgent($id)) {
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
            'alertStats'              => $this->openSearch->getAgentAlertStats($agentId),
            'fimEvents'               => $this->openSearch->getFimEvents($agentId, 5),
            'eventsEvolution'         => $this->openSearch->getEventsCountEvolution($agentId, '24h'),
            'complianceGdpr'          => $this->openSearch->getAgentCompliance($agentId, 'gdpr', '30d'),
            'compliancePciDss'        => $this->openSearch->getAgentCompliance($agentId, 'pci_dss', '30d'),
            'complianceNist'          => $this->openSearch->getAgentCompliance($agentId, 'nist_800_53', '30d'),
            'complianceHipaa'         => $this->openSearch->getAgentCompliance($agentId, 'hipaa', '30d'),
            'complianceGpg13'         => $this->openSearch->getAgentCompliance($agentId, 'gpg13', '30d'),
            'complianceTsc'           => $this->openSearch->getAgentCompliance($agentId, 'tsc', '30d'),
            'eventsEvolutionGdpr'     => $this->openSearch->getEventsCountEvolutionByCompliance($agentId, 'gdpr', '24h'),
            'eventsEvolutionPciDss'   => $this->openSearch->getEventsCountEvolutionByCompliance($agentId, 'pci_dss', '24h'),
            'eventsEvolutionNist'     => $this->openSearch->getEventsCountEvolutionByCompliance($agentId, 'nist_800_53', '24h'),
            'eventsEvolutionHipaa'    => $this->openSearch->getEventsCountEvolutionByCompliance($agentId, 'hipaa', '24h'),
            'eventsEvolutionGpg13'    => $this->openSearch->getEventsCountEvolutionByCompliance($agentId, 'gpg13', '24h'),
            'eventsEvolutionTsc'      => $this->openSearch->getEventsCountEvolutionByCompliance($agentId, 'tsc', '24h'),
            'mitreTactics'            => $this->openSearch->getMitreTactics($agentId, '24h'),
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
