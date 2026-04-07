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
        $response = Http::withoutVerifying()
            ->withBasicAuth($this->wazuhUser, $this->wazuhPass)
            ->post("{$this->wazuhBase}/security/user/authenticate");

        return $response->json('data.token');
    }

    public function index()
    {
        $token = $this->getToken();

        $summary = Http::withoutVerifying()
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

        // Fetch alert data from OpenSearch
        $alertTrend = $this->openSearch->getAlertTrendLast7Days();
        $alertSeverity = $this->openSearch->getAlertSeverityDistribution();
        $totalAlerts = $this->openSearch->getTotalAlertCount();
        
        // Fetch additional analytics
        $osDistribution = $this->openSearch->getOsDistribution();
        $topRules = $this->openSearch->getTopTriggeredRules(5);
        $topAgents = $this->openSearch->getTopAgentsByAlerts(5);

        return view('home.index', compact('agentStats', 'alertTrend', 'alertSeverity', 'totalAlerts', 'osDistribution', 'topRules', 'topAgents'));
    }
}