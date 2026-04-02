<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    private $wazuhBase = 'https://192.168.200.150:55000';
    private $wazuhUser = 'admin';
    private $wazuhPass = 'Admin123.';

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

        return view('home.index', compact('agentStats'));
    }
}