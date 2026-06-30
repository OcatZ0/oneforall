<?php

namespace App\Http\Controllers;

use App\Enums\AgentStatus;
use App\Helpers\ApiResponse;
use App\Models\WazuhAgent;
use App\Services\OpenSearchService;
use App\Services\WazuhService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    private WazuhService $_wazuhService;
    private OpenSearchService $_openSearch;

    public function __construct(WazuhService $_wazuhService, OpenSearchService $_openSearch)
    {
        $this->_wazuhService = $_wazuhService;
        $this->_openSearch   = $_openSearch;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function validatedTimeRange(?string $timeRange = null): string
    {
        return in_array($timeRange, config('dashboard.time_ranges')) ? $timeRange : '24h';
    }

    // ── Public actions ────────────────────────────────────────────────────────

    public function index()
    {
        try {
            $user    = auth()->user();
            $token   = $this->_wazuhService->getToken();
            $perPage = request('per_page', 10);
            $page    = max(request('page', 1), 1);
            $offset  = ($page - 1) * $perPage;

            $dbAgentIds = $this->getAccessibleAgentIds();
            $stats      = $this->buildFilteredStats($token, $dbAgentIds);

            if (empty($dbAgentIds)) {
                $agents            = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage, $page, ['path' => route('agent'), 'query' => request()->query()]);
                $savedLayout       = $this->getLayout('agent');
                $savedLayoutMobile = $this->getLayoutMobile('agent');
                return view('agent.index', ['agents' => $agents, 'stats' => $stats, 'evolutionLabels' => '[]', 'evolutionData' => '[]', 'savedLayout' => $savedLayout, 'savedLayoutMobile' => $savedLayoutMobile]);
            }

            $wazuhData  = $this->_wazuhService->getAgents($token ?? '', $offset, $perPage, request('search'), request('status'), $dbAgentIds);
            $rawAgents  = collect($wazuhData['agents'])->reject(fn($a) => ($a['id'] ?? '') === AgentStatus::Master->value);
            $agentMap   = $this->buildAgentMap($rawAgents->pluck('id')->filter()->all());
            $agentsList = $rawAgents->map(fn($a) => $this->enrichAgentData($this->mapWazuhAgent($a), $agentMap));

            $agents = new \Illuminate\Pagination\LengthAwarePaginator(
                items: $agentsList->values(),
                total: $wazuhData['total'],
                perPage: $perPage,
                currentPage: $page,
                options: ['path' => route('agent'), 'query' => request()->query()]
            );

            $sessionKey = 'agent_evolution_base_time_' . floor(date('n'));
            $baseTime   = session($sessionKey) ?? tap(Carbon::now(), fn($t) => session([$sessionKey => $t]));
            $evolution  = $this->getAgentEvolution('24h', $dbAgentIds, $baseTime);
            $evolutionLabels    = json_encode($evolution['labels'] ?? []);
            $evolutionData      = json_encode($evolution['data']['active'] ?? $evolution['data'] ?? []);

            $savedLayout       = $this->getLayout('agent');
            $savedLayoutMobile = $this->getLayoutMobile('agent');

            return view('agent.index', compact('agents', 'stats', 'evolutionLabels', 'evolutionData', 'savedLayout', 'savedLayoutMobile'));
        } catch (\Exception $e) {
            Log::error('Agent index error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->indexErrorView();
        }
    }

    public function getChartData()
    {
        try {
            $timeRange  = request('time_range', '24h');
            $dbAgentIds = $this->getAccessibleAgentIds();
            $sessionKey = 'agent_evolution_base_time_' . floor(date('n'));
            $baseTime   = session($sessionKey) ?? tap(Carbon::now(), fn($t) => session([$sessionKey => $t]));
            $evolution  = $this->getAgentEvolution($timeRange, $dbAgentIds, $baseTime);

            return ApiResponse::success(['labels' => $evolution['labels'] ?? [], 'data' => $evolution['data'] ?? []]);
        } catch (\Exception $e) {
            Log::error('Get chart data error', ['error' => $e->getMessage()]);
            return ApiResponse::error('Gagal memuat data grafik', 500);
        }
    }

    public function getDetailChartData($id)
    {
        try {
            $agentId        = (string) $id;
            $timeRange      = request('time_range', '24h');
            $complianceType = in_array(request('compliance_type'), config('dashboard.compliance_types'))
                ? request('compliance_type')
                : 'gdpr';

            return ApiResponse::success([
                'events_evolution'            => $this->_openSearch->getEventsCountEvolution($agentId, $timeRange),
                'compliance_data'             => $this->_openSearch->getAgentCompliance($agentId, $complianceType, $timeRange),
                'events_compliance_evolution' => $this->_openSearch->getEventsCountEvolutionByCompliance($agentId, $complianceType, $timeRange),
                'mitre_tactics'               => $this->_openSearch->getMitreTactics($agentId, $timeRange),
            ]);
        } catch (\Exception $e) {
            Log::error('Get detail chart data error', ['error' => $e->getMessage()]);
            return ApiResponse::error('Gagal memuat data grafik', 500);
        }
    }

    public function detail($id)
    {
        try {
            $token = $this->_wazuhService->getToken();
            if (!$token) {
                return view('agent.detail', ['agent' => null, 'error' => 'Gagal melakukan autentikasi ke Wazuh API']);
            }

            $wa = $this->_wazuhService->getAgent($token, $id);
            if (!$wa) {
                return view('agent.detail', ['agent' => null, 'error' => 'Agent tidak ditemukan']);
            }

            $agent = $this->enrichAgentData($this->mapWazuhAgent($wa, true));

            $savedLayout       = $this->getLayout('agent-detail');
            $savedLayoutMobile = $this->getLayoutMobile('agent-detail');

            return view('agent.detail', array_merge(compact('agent', 'savedLayout', 'savedLayoutMobile'), $this->buildDetailData($agent->agent_id)));
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Agent detail timeout: ' . $e->getMessage());
            return view('agent.detail', ['agent' => null, 'error' => 'Koneksi timeout saat mengambil detail agent']);
        } catch (\Exception $e) {
            Log::error('Agent detail error: ' . $e->getMessage());
            return view('agent.detail', ['agent' => null, 'error' => 'Gagal memuat detail agent']);
        }
    }

    public function securityEvents($id)
    {
        try {
            $agent = $this->resolveAgent($id);
            if (!$agent) {
                return view('agent.security-events', ['agent' => null, 'error' => 'Agent tidak ditemukan atau API tidak tersedia']);
            }

            $timeRange    = request('time_range', '24h');
            ['perPage' => $perPage, 'page' => $page, 'offset' => $offset] = $this->paginateRequest();
            $groupsPerPage = in_array((int) request('groups_per_page', 10), [10, 25, 50]) ? (int) request('groups_per_page', 10) : 10;
            $groupsPage    = max((int) request('groups_page', 1), 1);
            $groupsOffset  = ($groupsPage - 1) * $groupsPerPage;

            $savedLayout       = $this->getLayout('security-events');
            $savedLayoutMobile = $this->getLayoutMobile('security-events');

            $alertsResult = $this->_openSearch->getRecentAlerts($id, $timeRange, $perPage, $offset);
            $groupsResult = $this->_openSearch->getGroupsSummary($id, $timeRange, $groupsPerPage, $groupsOffset);

            return view('agent.security-events', array_merge(compact('agent', 'timeRange', 'savedLayout', 'savedLayoutMobile', 'page', 'perPage', 'groupsPage', 'groupsPerPage'), [
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
            return view('agent.security-events', ['agent' => null, 'error' => 'Gagal memuat data security events']);
        }
    }

    public function getSeAlerts($id)
    {
        $timeRange = request('time_range', '24h');
        $perPage   = in_array((int) request('per_page', 10), [10, 25, 50]) ? (int) request('per_page', 10) : 10;
        $page      = max((int) request('page', 1), 1);
        $result    = $this->_openSearch->getRecentAlerts($id, $timeRange, $perPage, ($page - 1) * $perPage);
        return ApiResponse::paginated($result['data'], $result['total'], $page, $perPage);
    }

    public function getSeGroups($id)
    {
        $timeRange = request('time_range', '24h');
        $perPage   = in_array((int) request('per_page', 10), [10, 25, 50]) ? (int) request('per_page', 10) : 10;
        $page      = max((int) request('page', 1), 1);
        $result    = $this->_openSearch->getGroupsSummary($id, $timeRange, $perPage, ($page - 1) * $perPage);
        return ApiResponse::paginated($result['data'], $result['total'], $page, $perPage);
    }

    public function getIntegrityEvents($id)
    {
        $timeRange = request('time_range', '24h');
        $perPage   = in_array((int) request('per_page', 10), [10, 25, 50]) ? (int) request('per_page', 10) : 10;
        $page      = max((int) request('page', 1), 1);
        $result    = $this->_openSearch->getFimEventsPaginated($id, $timeRange, $perPage, ($page - 1) * $perPage);
        return ApiResponse::paginated($result['data'], $result['total'], $page, $perPage);
    }

    public function getSeChartData($id)
    {
        $timeRange = request('time_range', '24h');
        try {
            return ApiResponse::success([
                'metrics'                => $this->_openSearch->getSecurityEventsMetrics($id, 'now-' . $timeRange),
                'alertGroupsEvolution'   => $this->_openSearch->getAlertGroupsEvolution($id, $timeRange),
                'alertsEvolutionByLevel' => $this->_openSearch->getAlertsEvolutionByLevel($id, $timeRange),
                'topAlerts'              => $this->_openSearch->getTopAlerts($id, $timeRange, 5),
                'topRuleGroups'          => $this->_openSearch->getTopRuleGroups($id, $timeRange, 5),
                'topPCIDSS'              => $this->_openSearch->getTopPCIDSS($id, $timeRange, 5),
            ]);
        } catch (\Exception $e) {
            Log::error('SE chart data error: ' . $e->getMessage());
            return ApiResponse::error('Gagal memuat data', 500);
        }
    }

    public function getFimChartData($id)
    {
        $timeRange = request('time_range', '24h');
        try {
            return ApiResponse::success([
                'fimSummary'     => $this->_openSearch->getFimSummary($id, $timeRange),
                'fimEvolution'   => $this->_openSearch->getFimEvolution($id, $timeRange),
                'fimTopRules'    => $this->_openSearch->getFimTopRules($id, $timeRange, 5),
                'fimTopModified' => $this->_openSearch->getFimTopFiles($id, $timeRange, 'modified', 5),
                'fimTopDeleted'  => $this->_openSearch->getFimTopFiles($id, $timeRange, 'deleted', 5),
                'fimTopAdded'    => $this->_openSearch->getFimTopFiles($id, $timeRange, 'added', 5),
            ]);
        } catch (\Exception $e) {
            Log::error('FIM chart data error: ' . $e->getMessage());
            return ApiResponse::error('Gagal memuat data', 500);
        }
    }

    public function getScaChecksJson($id)
    {
        try {
            $token          = $this->_wazuhService->getToken();
            $policies       = $token ? $this->_wazuhService->getSCAPolicies($token, $id) : [];
            $policyId       = request('policy_id', $policies[0]['policy_id'] ?? null);
            $selectedPolicy = collect($policies)->firstWhere('policy_id', $policyId);
            $resultFilter   = in_array(request('result'), ['passed', 'failed', 'not_applicable']) ? request('result') : null;
            ['perPage' => $perPage, 'page' => $page] = $this->paginateRequest();
            $checksResult   = ($token && $policyId)
                ? $this->_wazuhService->getSCAChecks($token, $id, $policyId, $perPage, ($page - 1) * $perPage, $resultFilter)
                : ['data' => [], 'total' => 0];
            return ApiResponse::success([
                'checks'         => $checksResult['data'],
                'total'          => $checksResult['total'],
                'page'           => $page,
                'perPage'        => $perPage,
                'selectedPolicy' => $selectedPolicy,
                'policyId'       => $policyId,
                'resultFilter'   => $resultFilter,
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Gagal memuat data SCA checks', 500);
        }
    }

    public function integrityMonitoring($id)
    {
        try {
            $agent = $this->resolveAgent($id);
            if (!$agent) {
                return view('agent.integrity-monitoring', ['agent' => null, 'error' => 'Agent tidak ditemukan atau API tidak tersedia']);
            }

            $timeRange = request('time_range', '24h');
            ['perPage' => $perPage, 'page' => $page, 'offset' => $offset] = $this->paginateRequest();

            $savedLayout       = $this->getLayout('integrity-monitoring');
            $savedLayoutMobile = $this->getLayoutMobile('integrity-monitoring');

            $eventsResult = $this->_openSearch->getFimEventsPaginated($id, $timeRange, $perPage, $offset);

            return view('agent.integrity-monitoring', array_merge(compact('agent', 'timeRange', 'savedLayout', 'savedLayoutMobile', 'page', 'perPage'), [
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
            return view('agent.integrity-monitoring', ['agent' => null, 'error' => 'Gagal memuat data integrity monitoring']);
        }
    }

    public function sca($id)
    {
        try {
            $agent = $this->resolveAgent($id);
            if (!$agent) {
                return view('agent.sca', ['agent' => null, 'error' => 'Agent tidak ditemukan atau API tidak tersedia']);
            }

            $token    = $this->_wazuhService->getToken();
            $policies = $token ? $this->_wazuhService->getSCAPolicies($token, $id) : [];

            $policyId       = request('policy_id', $policies[0]['policy_id'] ?? null);
            $selectedPolicy = collect($policies)->firstWhere('policy_id', $policyId);
            $resultFilter   = in_array(request('result'), ['passed', 'failed', 'not_applicable']) ? request('result') : null;
            ['perPage' => $perPage, 'page' => $page, 'offset' => $offset] = $this->paginateRequest();

            $checksResult = ($token && $policyId)
                ? $this->_wazuhService->getSCAChecks($token, $id, $policyId, $perPage, $offset, $resultFilter)
                : ['data' => [], 'total' => 0];

            $totalPass = (int) array_sum(array_column($policies, 'pass'));
            $totalFail = (int) array_sum(array_column($policies, 'fail'));
            $totalNA   = (int) array_sum(array_column($policies, 'not_applicable'));
            $avgScore  = count($policies) > 0
                ? (int) round(array_sum(array_column($policies, 'score')) / count($policies))
                : 0;

            $savedLayout       = $this->getLayout('sca');
            $savedLayoutMobile = $this->getLayoutMobile('sca');

            return view('agent.sca', array_merge(
                compact('agent', 'policies', 'selectedPolicy', 'policyId', 'resultFilter', 'page', 'perPage', 'savedLayout', 'savedLayoutMobile'),
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
            return view('agent.sca', ['agent' => null, 'error' => 'Gagal memuat data SCA']);
        }
    }

    public function vulnerabilities($id)
    {
        try {
            $agent = $this->resolveAgent($id);
            if (!$agent) {
                return view('agent.vulnerabilities', ['agent' => null, 'error' => 'Agent tidak ditemukan atau API tidak tersedia']);
            }

            $token    = $this->_wazuhService->getToken();
            $severity = in_array(request('severity'), ['Critical', 'High', 'Medium', 'Low']) ? request('severity') : null;
            ['perPage' => $perPage, 'page' => $page, 'offset' => $offset] = $this->paginateRequest();

            $vulnResult = $token
                ? $this->_wazuhService->getVulnerabilities($token, $id, $perPage, $offset, $severity)
                : ['data' => [], 'total' => 0];

            $severityCounts = $token
                ? $this->_wazuhService->getVulnerabilityCounts($token, $id)
                : ['Critical' => 0, 'High' => 0, 'Medium' => 0, 'Low' => 0];

            $summaryBatch = $token
                ? $this->_wazuhService->getVulnerabilities($token, $id, 100, 0, null)['data']
                : [];

            $lastScan = $token ? $this->_wazuhService->getVulnerabilitiesLastScan($token, $id) : null;

            $savedLayout       = $this->getLayout('vulnerabilities');
            $savedLayoutMobile = $this->getLayoutMobile('vulnerabilities');

            return view('agent.vulnerabilities', compact('agent', 'page', 'perPage', 'severity', 'severityCounts', 'summaryBatch', 'lastScan', 'savedLayout', 'savedLayoutMobile') + [
                'vulnerabilities' => $vulnResult['data'],
                'totalVulns'      => $vulnResult['total'],
            ]);
        } catch (\Exception $e) {
            Log::error('Vulnerabilities error: ' . $e->getMessage());
            return view('agent.vulnerabilities', ['agent' => null, 'error' => 'Gagal memuat data vulnerabilities']);
        }
    }

    public function getMitreAlertsJson($id)
    {
        $timeRange = $this->validatedTimeRange(request('time_range'));
        ['perPage' => $perPage, 'page' => $page] = $this->paginateRequest();
        $result      = $this->_openSearch->getMitreAlerts($id, $timeRange, $perPage, ($page - 1) * $perPage);
        return ApiResponse::paginated($result['data'], $result['total'], $page, $perPage);
    }

    public function mitreAttack($id)
    {
        try {
            $agent = $this->resolveAgent($id);
            if (!$agent) {
                return view('agent.mitre-attack', ['agent' => null, 'error' => 'Agent tidak ditemukan atau API tidak tersedia']);
            }

            $timeRange = $this->validatedTimeRange(request('time_range'));
            ['perPage' => $perPage, 'page' => $page, 'offset' => $offset] = $this->paginateRequest();

            $alertsResult = $this->_openSearch->getMitreAlerts($id, $timeRange, $perPage, $offset);

            $savedLayout       = $this->getLayout('mitre-attack');
            $savedLayoutMobile = $this->getLayoutMobile('mitre-attack');

            return view('agent.mitre-attack', compact('agent', 'timeRange', 'page', 'perPage', 'savedLayout', 'savedLayoutMobile') + [
                'tactics'          => $this->_openSearch->getMitreTactics($id, $timeRange),
                'techniques'       => $this->_openSearch->getMitreTechniques($id, $timeRange),
                'evolution'        => $this->_openSearch->getMitreEvolution($id, $timeRange),
                'attacksByTactic'  => $this->_openSearch->getMitreAttacksByTactic($id, $timeRange),
                'ruleLevelCounts'  => $this->_openSearch->getMitreRuleLevelCounts($id, $timeRange),
                'alerts'           => $alertsResult['data'],
                'totalAlerts'      => $alertsResult['total'],
            ]);
        } catch (\Exception $e) {
            Log::error('MITRE ATT&CK error: ' . $e->getMessage());
            return view('agent.mitre-attack', ['agent' => null, 'error' => 'Gagal memuat data MITRE ATT&CK']);
        }
    }

    public function compliance($id)
    {
        try {
            $agent = $this->resolveAgent($id);
            if (!$agent) {
                return view('agent.compliance', ['agent' => null, 'error' => 'Agent tidak ditemukan atau API tidak tersedia']);
            }

            $complianceType = in_array(request('compliance_type'), config('dashboard.compliance_types')) ? request('compliance_type') : 'gdpr';
            $timeRange      = $this->validatedTimeRange(request('time_range'));

            $allCompliance = $this->_openSearch->getAgentCompliance($id, $complianceType, $timeRange);

            $savedLayout       = $this->getLayout('compliance');
            $savedLayoutMobile = $this->getLayoutMobile('compliance');

            return view('agent.compliance', compact('agent', 'complianceType', 'timeRange', 'allCompliance', 'savedLayout', 'savedLayoutMobile') + [
                'topRuleGroups'  => $this->_openSearch->getTopRuleGroups($id, $timeRange, 5, $complianceType),
                'topRules'       => $this->_openSearch->getTopAlerts($id, $timeRange, 5, $complianceType),
                'top5Compliance' => array_slice($allCompliance, 0, 5),
                'ruleLevelDist'  => $this->_openSearch->getComplianceRuleLevelDistribution($id, $complianceType, $timeRange),
            ]);
        } catch (\Exception $e) {
            Log::error('Compliance error: ' . $e->getMessage());
            return view('agent.compliance', ['agent' => null, 'error' => 'Gagal memuat data compliance']);
        }
    }

    public function inventoryData($id)
    {
        try {
            $agent = $this->resolveAgent($id);
            if (!$agent) {
                return view('agent.inventory-data', ['agent' => null, 'error' => 'Agent tidak ditemukan atau API tidak tersedia']);
            }

            $token    = $this->_wazuhService->getToken();
            $hardware = $token ? $this->_wazuhService->getInventoryHardware($token, $id) : null;
            $osInfo   = $token ? $this->_wazuhService->getInventoryOS($token, $id)       : null;

            $savedLayout       = $this->getLayout('inventory-data');
            $savedLayoutMobile = $this->getLayoutMobile('inventory-data');

            return view('agent.inventory-data', compact('agent', 'hardware', 'osInfo', 'savedLayout', 'savedLayoutMobile'));
        } catch (\Exception $e) {
            Log::error('Inventory data error: ' . $e->getMessage());
            return view('agent.inventory-data', ['agent' => null, 'error' => 'Gagal memuat data inventory']);
        }
    }

    public function getInventoryJson($id, $type)
    {
        $allowed = ['netiface', 'ports', 'netaddr', 'hotfixes', 'packages', 'processes'];
        if (!in_array($type, $allowed)) {
            return ApiResponse::error('Tipe tidak valid', 400);
        }

        $token = $this->_wazuhService->getToken();
        if (!$token) {
            return ApiResponse::error('Gagal melakukan autentikasi ke Wazuh', 503);
        }

        ['perPage' => $perPage, 'page' => $page, 'offset' => $offset] = $this->paginateRequest();
        $search  = request('search') ?: null;

        $result = match($type) {
            'netiface'  => $this->_wazuhService->getInventoryNetInterfaces($token, $id, $perPage, $offset, $search),
            'ports'     => $this->_wazuhService->getInventoryNetPorts($token, $id, $perPage, $offset, $search),
            'netaddr'   => $this->_wazuhService->getInventoryNetAddr($token, $id, $perPage, $offset, $search),
            'hotfixes'  => $this->_wazuhService->getInventoryHotfixes($token, $id, $perPage, $offset, $search),
            'packages'  => $this->_wazuhService->getInventoryPackages($token, $id, $perPage, $offset, $search),
            'processes' => $this->_wazuhService->getInventoryProcesses($token, $id, $perPage, $offset, $search),
        };

        return ApiResponse::paginated($result['data'], $result['total'], $page, $perPage);
    }

    public function syncAgentsFromWazuh()
    {
        try {
            if (!auth()->check()) {
                return ApiResponse::error('Tidak diizinkan: Silakan login terlebih dahulu', 401);
            }
            if (auth()->user()->role !== 'admin') {
                return ApiResponse::error('Tidak diizinkan: Hanya admin yang dapat sinkronisasi agent', 403);
            }

            $result = $this->syncFromWazuh();

            return $result['success']
                ? ApiResponse::success($result, 'Sinkronisasi agent berhasil')
                : ApiResponse::error($result['message'] ?? 'Sinkronisasi gagal', 500);
        } catch (\Exception $e) {
            Log::error('Agent sync error', ['error' => $e->getMessage()]);
            return ApiResponse::error('Sinkronisasi gagal: ' . $e->getMessage(), 500);
        }
    }

    public function search()
    {
        try {
            ['perPage' => $perPage, 'page' => $page, 'offset' => $offset] = $this->paginateRequest();

            $token      = $this->_wazuhService->getToken();
            $dbAgentIds = $this->getAccessibleAgentIds();

            if (empty($dbAgentIds)) {
                return ApiResponse::success(['agents' => [], 'total' => 0, 'page' => $page, 'perPage' => $perPage, 'totalPages' => 1, 'from' => 0, 'to' => 0]);
            }

            $wazuhData  = $this->_wazuhService->getAgents($token ?? '', $offset, $perPage, request('search'), request('status'), $dbAgentIds);
            $rawAgents  = collect($wazuhData['agents'])->reject(fn($a) => ($a['id'] ?? '') === AgentStatus::Master->value);
            $agentMap   = $this->buildAgentMap($rawAgents->pluck('id')->filter()->all());
            $agentsList = $rawAgents->map(fn($a) => $this->enrichAgentData($this->mapWazuhAgent($a), $agentMap))->values();

            $total      = $wazuhData['total'];
            $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 1;
            $from       = $total > 0 ? $offset + 1 : 0;
            $to         = min($offset + $perPage, $total);

            return ApiResponse::success([
                'agents'     => $agentsList->map(fn($a) => [
                    'agent_id'     => $a->agent_id,
                    'name'         => $a->name,
                    'ip'           => $a->ip,
                    'os'           => $a->os,
                    'version'      => $a->version,
                    'status'       => $a->status,
                    'cluster_node' => $a->cluster_node,
                    'user'         => $a->user ? ['username' => $a->user->username] : null,
                ]),
                'total'      => $total,
                'page'       => $page,
                'perPage'    => $perPage,
                'totalPages' => $totalPages,
                'from'       => $from,
                'to'         => $to,
            ]);
        } catch (\Exception $e) {
            Log::error('Agent search error', ['error' => $e->getMessage()]);
            return ApiResponse::error('Gagal mencari agent', 500);
        }
    }

    private function buildAgentMap(array $agentIds): array
    {
        if (empty($agentIds)) return [];
        return WazuhAgent::with('user')->whereIn('agent_id', $agentIds)->get()->keyBy('agent_id')->all();
    }

    private function enrichAgentData(object $agent, array $dbAgentMap = []): object
    {
        $agentId = $agent->agent_id ?? null;
        if (!$agentId) return $agent;

        $dbAgent = $dbAgentMap[$agentId] ?? WazuhAgent::where('agent_id', $agentId)->with('user')->first();
        if ($dbAgent) $agent->user = $dbAgent->user;
        return $agent;
    }

    private function mapWazuhAgent(array $wa, bool $full = false): object
    {
        $agent = (object) [
            'agent_id'     => $wa['id'] ?? null,
            'name'         => $wa['name'] ?? 'Unknown',
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
        if (!$token) return ['success' => false, 'message' => 'Gagal melakukan autentikasi ke Wazuh API'];

        // Phase 1: Fetch all agent data from Wazuh API (outside transaction)
        $allWazuhAgents = [];
        $offset = 0;
        $limit  = 100;

        do {
            $data   = $this->_wazuhService->getAgents($token, $offset, $limit);
            $batch  = $data['agents'] ?? [];
            if (empty($batch)) break;
            array_push($allWazuhAgents, ...$batch);
            $offset += $limit;
        } while (count($allWazuhAgents) < ($data['total'] ?? 0));

        // Phase 2: All DB writes in a single transaction
        $synced = $updated = $deleted = 0;

        DB::transaction(function () use ($allWazuhAgents, &$synced, &$updated, &$deleted) {
            $syncedIds = [];

            foreach ($allWazuhAgents as $wa) {
                $agentId = $wa['id'] ?? null;
                if (!$agentId || $agentId === AgentStatus::Master->value) continue;

                $agentData   = ['agent_id' => $agentId, 'name' => $wa['name'] ?? 'Unknown'];
                $syncedIds[] = $agentId;
                $existing    = WazuhAgent::where('agent_id', $agentId)->first();

                if ($existing) {
                    if ($existing->name !== $agentData['name']) $existing->update($agentData);
                    $updated++;
                } else {
                    WazuhAgent::create(array_merge($agentData, ['description' => '', 'created_at' => Carbon::now()]));
                    $synced++;
                }
            }

            $deleted = !empty($syncedIds)
                ? WazuhAgent::whereNotIn('agent_id', $syncedIds)->delete()
                : 0;
        });

        $total     = count($allWazuhAgents);
        $processed = $synced + $updated;

        Log::info('Agent sync completed', compact('synced', 'updated', 'deleted', 'processed', 'total'));

        return ['success' => true, 'synced_new' => $synced, 'updated_existing' => $updated, 'deleted_obsolete' => $deleted, 'total_processed' => $processed, 'errors' => 0, 'total_in_wazuh' => $total];
    }

    private function buildFilteredStats(?string $token, array $dbAgentIds): array
    {
        $empty = ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0];
        if (!$token || empty($dbAgentIds)) return $empty;

        $stats = $empty;
        try {
            $data = $this->_wazuhService->getAgents($token, 0, count($dbAgentIds), null, null, $dbAgentIds);
            foreach ($data['agents'] as $a) {
                if (($a['id'] ?? '') === AgentStatus::Master->value) continue;
                $stats['total']++;
                $status = $a['status'] ?? 'unknown';
                if (isset($stats[$status])) $stats[$status]++;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch filtered agent stats: ' . $e->getMessage());
        }

        return $stats;
    }

    private function getAgentEvolution(string $timeRange = '24h', ?array $agentIds = null, ?Carbon $baseTime = null): array
    {
        try {
            return $this->_openSearch->getAgentEvolutionByTimeRange($timeRange, $agentIds, $baseTime);
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

    private function buildDetailData(string $agentId): array
    {
        return [
            'alertStats' => $this->_openSearch->getAgentAlertStats($agentId),
            'fimEvents'  => $this->_openSearch->getFimEvents($agentId, 5),
        ];
    }

    private function indexErrorView(): \Illuminate\View\View
    {
        $agents = new \Illuminate\Pagination\LengthAwarePaginator(
            items: [], total: 0, perPage: 10, currentPage: 1,
            options: ['path' => route('agent'), 'query' => request()->query()]
        );

        return view('agent.index', [
            'agents'            => $agents,
            'stats'             => ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0],
            'evolutionLabels'   => json_encode([]),
            'evolutionData'     => json_encode([]),
            'savedLayout'       => null,
            'savedLayoutMobile' => null,
        ]);
    }
}
