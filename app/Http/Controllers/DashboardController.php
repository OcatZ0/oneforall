<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Services\OpenSearchService;

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

    public function index()
    {
        try {
            $token = $this->getToken();

            // Provide fallback data if token is null
            $agentStats = [
                'total'           => 0,
                'active'          => 0,
                'disconnected'    => 0,
                'pending'         => 0,
                'never_connected' => 0,
            ];

            if ($token) {
                try {
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
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    \Log::warning('Failed to fetch agent summary (timeout): ' . $e->getMessage());
                } catch (\Exception $e) {
                    \Log::warning('Failed to fetch agent summary: ' . $e->getMessage());
                }
            }

            // Fetch alert data from OpenSearch (has built-in fallback)
            $alertTrend = $this->openSearch->getAlertTrendLast7Days();
            $alertSeverity = $this->openSearch->getAlertSeverityDistribution();
            $totalAlerts = $this->openSearch->getTotalAlertCount();
            
            // Fetch additional analytics (has built-in fallback)
            $osDistribution = $this->openSearch->getOsDistribution();
            $topRules = $this->openSearch->getTopTriggeredRules(5);
            $topAgents = $this->openSearch->getTopAgentsByAlerts(5);

            return view('home.index', compact('agentStats', 'alertTrend', 'alertSeverity', 'totalAlerts', 'osDistribution', 'topRules', 'topAgents'));
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage());
            return view('home.index', [
                'agentStats' => ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0],
                'alertTrend' => $this->openSearch->getAlertTrendLast7Days(),
                'alertSeverity' => $this->openSearch->getAlertSeverityDistribution(),
                'totalAlerts' => $this->openSearch->getTotalAlertCount(),
                'osDistribution' => $this->openSearch->getOsDistribution(),
                'topRules' => $this->openSearch->getTopTriggeredRules(5),
                'topAgents' => $this->openSearch->getTopAgentsByAlerts(5),
            ]);
        }
    }
}