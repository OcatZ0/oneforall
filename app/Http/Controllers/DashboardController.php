<?php

namespace App\Http\Controllers;

use App\Enums\AgentStatus;
use App\Helpers\ApiResponse;
use App\Models\WazuhAgent;
use App\Models\DashboardLayout;
use App\Models\User;
use App\Services\OpenSearchService;
use App\Services\WazuhService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    private WazuhService $_wazuhService;
    private OpenSearchService $_openSearch;

    public function __construct(WazuhService $_wazuhService, OpenSearchService $_openSearch)
    {
        $this->_wazuhService = $_wazuhService;
        $this->_openSearch   = $_openSearch;
    }

    public function index()
    {
        try {
            $user     = auth()->user();
            $userRole = $user->role ?? null;
            $userId   = $user->id ?? auth()->id();
            $isAdmin  = $userRole === 'admin';

            $token      = $this->_wazuhService->getToken();
            $agentStats = $this->getAgentStatsWithChange($token, $isAdmin, $userId);

            $accessibleAgentIds = $this->getAccessibleAgentIds();

            $alertTrend     = $this->_openSearch->getAlertTrendLast7Days($accessibleAgentIds);
            $alertSeverity  = $this->_openSearch->getAlertSeverityDistribution($accessibleAgentIds);
            $totalAlerts    = $this->_openSearch->getTotalAlertCount($accessibleAgentIds);
            $osDistribution = $this->_openSearch->getOsDistribution($accessibleAgentIds);
            $topRules       = $this->_openSearch->getTopTriggeredRules(5, $accessibleAgentIds);
            $topAgents      = $this->_openSearch->getTopAgentsByAlerts(5, $accessibleAgentIds);
            $customerStats  = $isAdmin ? $this->getCustomerStats() : null;
            $savedLayout       = DashboardLayout::where('user_id', $userId)->where('page', 'home')->value('layout');
            $savedLayoutMobile = DashboardLayout::where('user_id', $userId)->where('page', 'home-mobile')->value('layout');

            return view('home.index', compact(
                'agentStats', 'alertTrend', 'alertSeverity', 'totalAlerts',
                'osDistribution', 'topRules', 'topAgents', 'customerStats', 'savedLayout', 'savedLayoutMobile'
            ));
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return view('home.index', [
                'agentStats'     => ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0, 'change' => 0, 'changePercent' => 0],
                'alertTrend'     => [],
                'alertSeverity'  => ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0],
                'totalAlerts'    => 0,
                'osDistribution' => [],
                'topRules'       => [],
                'topAgents'      => [],
                'customerStats'  => null,
                'savedLayout'       => null,
                'savedLayoutMobile' => null,
            ]);
        }
    }

    public function saveLayout(Request $request)
    {
        $validated = $request->validate([
            'layout' => 'required|array',
            'page'   => 'required|string|max:50',
        ]);

        DashboardLayout::updateOrCreate(
            ['user_id' => auth()->user()->id, 'page' => $validated['page']],
            ['layout'  => $validated['layout']]
        );

        return ApiResponse::success();
    }

    private function getCurrentAgentCounts(?string $token, array $dbAgentIds): array
    {
        $counts = ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0];
        if (empty($dbAgentIds) || !$token) return $counts;

        $data = $this->_wazuhService->getAgents($token, 0, count($dbAgentIds), null, null, $dbAgentIds);
        foreach ($data['agents'] as $agent) {
            if (($agent['id'] ?? '') === AgentStatus::Master->value) continue;
            $counts['total']++;
            $status = $agent['status'] ?? 'unknown';
            if (isset($counts[$status])) $counts[$status]++;
        }

        return $counts;
    }

    private function getAgentStatsWithChange(?string $token, bool $isAdmin, $userId): array
    {
        $empty = ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0, 'change' => 0, 'changePercent' => 0, 'currentMonthNew' => 0, 'previousMonthNew' => 0];
        if (!$token) return $empty;

        try {
            $agentStats = $this->getCurrentAgentCounts($token, $this->getAccessibleAgentIds());

            $previousMonthEnd   = Carbon::now()->subMonth()->endOfMonth();
            $totalPreviousMonth = $isAdmin
                ? WazuhAgent::where('created_at', '<=', $previousMonthEnd)->count()
                : WazuhAgent::where('user_id', $userId)->where('created_at', '<=', $previousMonthEnd)->count();

            $change        = $agentStats['total'] - $totalPreviousMonth;
            $changePercent = $totalPreviousMonth > 0 ? round(($change / $totalPreviousMonth) * 100, 1) : 0;

            return array_merge($agentStats, [
                'change'           => $change,
                'changePercent'    => $changePercent,
                'currentMonthNew'  => $change,
                'previousMonthNew' => $totalPreviousMonth,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to fetch agent stats with change: ' . $e->getMessage());
            return $empty;
        }
    }

    private function getCustomerStats(): array
    {
        try {
            $totalCustomers     = User::where('role', 'customer')->count();
            $previousMonthEnd   = Carbon::now()->subMonth()->endOfMonth();
            $totalPreviousMonth = User::where('role', 'customer')->where('created_at', '<=', $previousMonthEnd)->count();
            $change             = $totalCustomers - $totalPreviousMonth;
            $changePercent      = $totalPreviousMonth > 0 ? round(($change / $totalPreviousMonth) * 100, 1) : 0;

            return [
                'total'            => $totalCustomers,
                'change'           => $change,
                'changePercent'    => $changePercent,
                'currentMonthNew'  => $change,
                'previousMonthNew' => $totalPreviousMonth,
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to fetch customer stats: ' . $e->getMessage());
            return ['total' => 0, 'change' => 0, 'changePercent' => 0, 'currentMonthNew' => 0, 'previousMonthNew' => 0];
        }
    }
}
