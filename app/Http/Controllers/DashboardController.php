<?php

namespace App\Http\Controllers;

use App\Models\Agent;
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

    public function __construct()
    {
        $this->_wazuhService = new WazuhService();
        $this->_openSearch   = new OpenSearchService();
    }

    public function index()
    {
        try {
            $user     = auth()->user();
            $userRole = $user->peran ?? null;
            $userId   = $user->id_pengguna ?? auth()->id();
            $isAdmin  = $userRole === 'admin';

            $token      = $this->_wazuhService->getToken();
            $agentStats = $this->getAgentStatsWithChange($token, $isAdmin, $userId);

            $accessibleAgentIds = null;
            if (!$isAdmin) {
                $accessibleAgentIds = Agent::where('id_pengguna', $userId)
                    ->pluck('id_agent')
                    ->map(fn($id) => (string) $id)
                    ->values()
                    ->toArray();
            }

            $alertTrend     = $this->_openSearch->getAlertTrendLast7Days($accessibleAgentIds, $isAdmin);
            $alertSeverity  = $this->_openSearch->getAlertSeverityDistribution($accessibleAgentIds, $isAdmin);
            $totalAlerts    = $this->_openSearch->getTotalAlertCount($accessibleAgentIds, $isAdmin);
            $osDistribution = $this->_openSearch->getOsDistribution($accessibleAgentIds, $isAdmin);
            $topRules       = $this->_openSearch->getTopTriggeredRules(5, $accessibleAgentIds, $isAdmin);
            $topAgents      = $this->_openSearch->getTopAgentsByAlerts(5, $accessibleAgentIds, $isAdmin);
            $customerStats  = $isAdmin ? $this->getCustomerStats() : null;
            $savedLayout    = DashboardLayout::where('id_pengguna', $userId)->where('page', 'home')->value('layout');

            return view('home.index', compact(
                'agentStats', 'alertTrend', 'alertSeverity', 'totalAlerts',
                'osDistribution', 'topRules', 'topAgents', 'customerStats', 'savedLayout'
            ));
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return view('home.index', [
                'agentStats'     => ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0, 'change' => 0, 'changePercent' => 0],
                'alertTrend'     => [],
                'alertSeverity'  => [],
                'totalAlerts'    => 0,
                'osDistribution' => [],
                'topRules'       => [],
                'topAgents'      => [],
                'customerStats'  => null,
                'savedLayout'    => null,
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
            ['id_pengguna' => auth()->user()->id_pengguna, 'page' => $validated['page']],
            ['layout'      => $validated['layout']]
        );

        return response()->json(['success' => true]);
    }

    private function getAgentStatsWithChange(?string $token, bool $isAdmin, $userId): array
    {
        $empty = ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0, 'change' => 0, 'changePercent' => 0, 'currentMonthNew' => 0, 'previousMonthNew' => 0];

        try {
            if (!$token) return $empty;

            if ($isAdmin) {
                $agentStats = $this->_wazuhService->getAgentSummaryStatus($token);
            } else {
                $accessibleIds = Agent::where('id_pengguna', $userId)
                    ->pluck('id_agent')
                    ->map(fn($id) => (string) $id)
                    ->values()
                    ->toArray();

                $agentStats = ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0];

                if (!empty($accessibleIds)) {
                    $data = $this->_wazuhService->getAgents($token, 0, 50000);
                    foreach ($data['agents'] as $agent) {
                        $agentId = (string) ($agent['id'] ?? null);
                        if (in_array($agentId, $accessibleIds, true)) {
                            $agentStats['total']++;
                            $status = $agent['status'] ?? 'unknown';
                            if (isset($agentStats[$status])) $agentStats[$status]++;
                        }
                    }
                }
            }

            $previousMonthEnd   = Carbon::now()->subMonth()->endOfMonth();
            $totalPreviousMonth = $isAdmin
                ? Agent::where('tanggal_dibuat', '<=', $previousMonthEnd)->count()
                : Agent::where('id_pengguna', $userId)->where('tanggal_dibuat', '<=', $previousMonthEnd)->count();

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
            $totalCustomers     = User::where('peran', 'customer')->count();
            $previousMonthEnd   = Carbon::now()->subMonth()->endOfMonth();
            $totalPreviousMonth = User::where('peran', 'customer')->where('tanggal_dibuat', '<=', $previousMonthEnd)->count();
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
