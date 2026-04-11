<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Services\OpenSearchService;
use App\Models\User;
use App\Models\Agent;
use Carbon\Carbon;

class DashboardController extends Controller
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

    private function getAgentStatsWithChange($token, $userRole, $userId = null)
    {
        try {
            // Get current agent stats
            $agentStats = [
                'total'           => 0,
                'active'          => 0,
                'disconnected'    => 0,
                'pending'         => 0,
                'never_connected' => 0,
            ];

            if (!$token) {
                return array_merge($agentStats, ['change' => 0, 'changePercent' => 0]);
            }

            // Fetch current stats
            if ($userRole === 'admin') {
                $summary = Http::withoutVerifying()
                    ->connectTimeout(2)
                    ->timeout(2)
                    ->withToken($token)
                    ->get("{$this->wazuhBase}/agents/summary/status")
                    ->json('data.connection');

                $agentStats = [
                    'total'           => $summary['total'] ?? 0,
                    'active'          => $summary['active'] ?? 0,
                    'disconnected'    => $summary['disconnected'] ?? 0,
                    'pending'         => $summary['pending'] ?? 0,
                    'never_connected' => $summary['never_connected'] ?? 0,
                ];
            } else {
                // For customers, get stats only for assigned agents
                $accessibleIds = Agent::where('id_pengguna', $userId)
                    ->pluck('id_agent')
                    ->toArray();
                
                if (!empty($accessibleIds)) {
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
                                $agentStats['total']++;
                                $status = $agent['status'] ?? 'unknown';
                                if (isset($agentStats[$status])) {
                                    $agentStats[$status]++;
                                }
                            }
                        }
                    }
                }
            }

            // Calculate change from database records
            $now = Carbon::now();
            $currentMonthStart = $now->copy()->startOfMonth();
            $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
            $previousMonthEnd = $now->copy()->subMonth()->endOfMonth();

            // Filter change calculation by user role
            if ($userRole === 'admin') {
                // Admin counts all agents
                $currentMonthAgents = Agent::where('tanggal_dibuat', '>=', $currentMonthStart)->count();
                $previousMonthAgents = Agent::whereBetween('tanggal_dibuat', [$previousMonthStart, $previousMonthEnd])->count();
            } else {
                // Customer counts only their assigned agents
                $currentMonthAgents = Agent::where('id_pengguna', $userId)
                    ->where('tanggal_dibuat', '>=', $currentMonthStart)
                    ->count();
                $previousMonthAgents = Agent::where('id_pengguna', $userId)
                    ->whereBetween('tanggal_dibuat', [$previousMonthStart, $previousMonthEnd])
                    ->count();
            }

            $change = $currentMonthAgents - $previousMonthAgents;
            $changePercent = $previousMonthAgents > 0 
                ? round(($change / $previousMonthAgents) * 100, 1)
                : 0;

            return array_merge($agentStats, [
                'change' => $change,
                'changePercent' => $changePercent,
                'currentMonthNew' => $currentMonthAgents,
                'previousMonthNew' => $previousMonthAgents,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch agent stats with change: ' . $e->getMessage());
            return [
                'total'           => 0,
                'active'          => 0,
                'disconnected'    => 0,
                'pending'         => 0,
                'never_connected' => 0,
                'change'          => 0,
                'changePercent'   => 0,
                'currentMonthNew' => 0,
                'previousMonthNew' => 0,
            ];
        }
    }

    private function getCustomerStats()
    {
        try {
            // Total customers (users with role 'customer')
            $totalCustomers = User::where('peran', 'customer')->count();

            // Calculate change from previous month
            $now = Carbon::now();
            $currentMonthStart = $now->copy()->startOfMonth();
            $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
            $previousMonthEnd = $now->copy()->subMonth()->endOfMonth();

            $currentMonthNewCustomers = User::where('peran', 'customer')
                ->where('tanggal_dibuat', '>=', $currentMonthStart)
                ->count();

            $previousMonthNewCustomers = User::where('peran', 'customer')
                ->whereBetween('tanggal_dibuat', [$previousMonthStart, $previousMonthEnd])
                ->count();

            // Calculate change (difference)
            $change = $currentMonthNewCustomers - $previousMonthNewCustomers;
            $changePercent = $previousMonthNewCustomers > 0 
                ? round(($change / $previousMonthNewCustomers) * 100, 1)
                : 0;

            return [
                'total' => $totalCustomers,
                'change' => $change,
                'changePercent' => $changePercent,
                'currentMonthNew' => $currentMonthNewCustomers,
                'previousMonthNew' => $previousMonthNewCustomers,
            ];
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch customer stats: ' . $e->getMessage());
            return [
                'total' => 0,
                'change' => 0,
                'changePercent' => 0,
                'currentMonthNew' => 0,
                'previousMonthNew' => 0,
            ];
        }
    }

    public function index()
    {
        try {
            $user = auth()->user();
            $userRole = $user->peran ?? null;
            $userId = $user->id_pengguna ?? auth()->id();

            \Log::info('Dashboard request', [
                'user_id' => $userId,
                'user_role' => $userRole,
            ]);

            $token = $this->getToken();

            // Get agent statistics with change calculation
            $agentStats = $this->getAgentStatsWithChange($token, $userRole, $userId);

            // Get accessible agent IDs for filtering customer data
            $accessibleAgentIds = null;
            if ($userRole !== 'admin') {
                $accessibleAgentIds = Agent::where('id_pengguna', $userId)
                    ->pluck('id_agent')
                    ->toArray();
                
                \Log::info('Dashboard customer - accessible agents', [
                    'user_id' => $userId,
                    'agent_ids' => $accessibleAgentIds,
                    'count' => count($accessibleAgentIds),
                ]);
            } else {
                \Log::info('Dashboard admin - all agents visible');
            }

            // Fetch alert data from OpenSearch (filtered by accessible agents for customers)
            $alertTrend = $this->openSearch->getAlertTrendLast7Days($accessibleAgentIds);
            $alertSeverity = $this->openSearch->getAlertSeverityDistribution($accessibleAgentIds);
            $totalAlerts = $this->openSearch->getTotalAlertCount($accessibleAgentIds);
            $osDistribution = $this->openSearch->getOsDistribution($accessibleAgentIds);
            $topRules = $this->openSearch->getTopTriggeredRules(5, $accessibleAgentIds);
            $topAgents = $this->openSearch->getTopAgentsByAlerts(5, $accessibleAgentIds);

            // Fetch customer statistics (only for admin)
            $customerStats = $userRole === 'admin' ? $this->getCustomerStats() : null;

            return view('home.index', compact('agentStats', 'alertTrend', 'alertSeverity', 'totalAlerts', 'osDistribution', 'topRules', 'topAgents', 'customerStats'));
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage());
            return view('home.index', [
                'agentStats' => ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0, 'change' => 0, 'changePercent' => 0],
                'alertTrend' => [],
                'alertSeverity' => [],
                'totalAlerts' => 0,
                'osDistribution' => [],
                'topRules' => [],
                'topAgents' => [],
                'customerStats' => null,
            ]);
        }
    }
}