<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use App\Services\Interfaces\IOpenSearchService;
use App\Services\Interfaces\IWazuhService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct(
        private IWazuhService $wazuhService,
        private IOpenSearchService $openSearch
    ) {}

    public function index()
    {
        try {
            $user     = auth()->user();
            $userRole = $user->peran ?? null;
            $userId   = $user->id_pengguna ?? auth()->id();
            $isAdmin  = $userRole === 'admin';

            $token      = $this->wazuhService->getToken();
            $agentStats = $this->getAgentStatsWithChange($token, $isAdmin, $userId);

            $accessibleAgentIds = null;
            if (!$isAdmin) {
                $accessibleAgentIds = Agent::where('id_pengguna', $userId)
                    ->pluck('id_agent')
                    ->map(fn($id) => (string) $id)
                    ->values()
                    ->toArray();
            }

            $alertTrend     = $this->openSearch->getAlertTrendLast7Days($accessibleAgentIds, $isAdmin);
            $alertSeverity  = $this->openSearch->getAlertSeverityDistribution($accessibleAgentIds, $isAdmin);
            $totalAlerts    = $this->openSearch->getTotalAlertCount($accessibleAgentIds, $isAdmin);
            $osDistribution = $this->openSearch->getOsDistribution($accessibleAgentIds, $isAdmin);
            $topRules       = $this->openSearch->getTopTriggeredRules(5, $accessibleAgentIds, $isAdmin);
            $topAgents      = $this->openSearch->getTopAgentsByAlerts(5, $accessibleAgentIds, $isAdmin);
            $customerStats  = $isAdmin ? $this->getCustomerStats() : null;

            return view('home.index', compact(
                'agentStats', 'alertTrend', 'alertSeverity', 'totalAlerts',
                'osDistribution', 'topRules', 'topAgents', 'customerStats'
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
            ]);
        }
    }

    private function getAgentStatsWithChange(?string $token, bool $isAdmin, $userId): array
    {
        $empty = ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0, 'change' => 0, 'changePercent' => 0, 'currentMonthNew' => 0, 'previousMonthNew' => 0];

        try {
            if (!$token) return $empty;

            if ($isAdmin) {
                $agentStats = $this->wazuhService->getAgentSummaryStatus($token);
            } else {
                $accessibleIds = Agent::where('id_pengguna', $userId)
                    ->pluck('id_agent')
                    ->map(fn($id) => (string) $id)
                    ->values()
                    ->toArray();

                $agentStats = ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0];

                if (!empty($accessibleIds)) {
                    $data = $this->wazuhService->getAgents($token, 0, 50000);
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
