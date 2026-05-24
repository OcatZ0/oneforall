<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WazuhService
{
    private string $_host;
    private string $_user;
    private string $_password;

    public function __construct()
    {
        $this->_host     = config('wazuh.host');
        $this->_user     = config('wazuh.user');
        $this->_password = config('wazuh.password');
    }

    public function getToken(): ?string
    {
        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(2)
                ->timeout(2)
                ->withBasicAuth($this->_user, $this->_password)
                ->post("{$this->_host}/security/user/authenticate");

            if (!$response->successful()) {
                Log::warning('Wazuh token request failed: ' . $response->status());
                return null;
            }

            return $response->json('data.token');
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('Wazuh API connection timeout: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::warning('Wazuh API unreachable: ' . $e->getMessage());
            return null;
        }
    }

    public function getAgentSummaryStatus(string $token): array
    {
        $empty = ['total' => 0, 'active' => 0, 'disconnected' => 0, 'pending' => 0, 'never_connected' => 0];

        try {
            $summary = Http::withoutVerifying()
                ->connectTimeout(2)
                ->timeout(2)
                ->withToken($token)
                ->get("{$this->_host}/agents/summary/status")
                ->json('data.connection');

            return [
                'total'           => $summary['total'] ?? 0,
                'active'          => $summary['active'] ?? 0,
                'disconnected'    => $summary['disconnected'] ?? 0,
                'pending'         => $summary['pending'] ?? 0,
                'never_connected' => $summary['never_connected'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to fetch agent summary: ' . $e->getMessage());
            return $empty;
        }
    }

    public function getAgents(string $token, int $offset = 0, int $limit = 10, ?string $search = null, ?string $status = null): array
    {
        try {
            $params = ['offset' => $offset, 'limit' => $limit, 'sort' => 'id'];
            if ($search) $params['search'] = $search;
            if ($status && in_array($status, ['active', 'disconnected', 'pending', 'never_connected'])) {
                $params['status'] = $status;
            }

            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(3)
                ->withToken($token)
                ->get("{$this->_host}/agents", $params);

            if ($response->successful()) {
                $data = $response->json('data');
                return [
                    'agents' => $data['affected_items'] ?? [],
                    'total'  => $data['total_affected_items'] ?? 0,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch agents from Wazuh: ' . $e->getMessage());
        }

        return ['agents' => [], 'total' => 0];
    }

    public function getAgent(string $token, string $agentId): ?array
    {
        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(3)
                ->timeout(3)
                ->withToken($token)
                ->get("{$this->_host}/agents", ['agents_list' => $agentId]);

            if ($response->successful()) {
                $items = $response->json('data.affected_items');
                return !empty($items) ? $items[0] : null;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch agent from Wazuh: ' . $e->getMessage());
        }

        return null;
    }

    public function getSCAPolicies(string $token, string $agentId): array
    {
        try {
            $response = Http::withoutVerifying()
                ->connectTimeout(3)->timeout(5)
                ->withToken($token)
                ->get("{$this->_host}/sca/{$agentId}");

            if ($response->successful()) {
                return $response->json('data.affected_items') ?? [];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch SCA policies: ' . $e->getMessage());
        }
        return [];
    }

    public function getSCAChecks(string $token, string $agentId, string $policyId, int $limit = 10, int $offset = 0, ?string $result = null): array
    {
        try {
            $params = ['limit' => $limit, 'offset' => $offset];
            if ($result && in_array($result, ['passed', 'failed', 'not_applicable'])) {
                $params['result'] = $result;
            }

            $response = Http::withoutVerifying()
                ->connectTimeout(3)->timeout(5)
                ->withToken($token)
                ->get("{$this->_host}/sca/{$agentId}/checks/{$policyId}", $params);

            if ($response->successful()) {
                $data = $response->json('data');
                return [
                    'data'  => $data['affected_items'] ?? [],
                    'total' => $data['total_affected_items'] ?? 0,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch SCA checks: ' . $e->getMessage());
        }
        return ['data' => [], 'total' => 0];
    }

    public function getAgentsWithIPs(): array
    {
        try {
            $token = $this->getToken();
            if (!$token) return [];

            $response = Http::withoutVerifying()
                ->connectTimeout(2)
                ->timeout(2)
                ->withToken($token)
                ->get("{$this->_host}/agents", [
                    'limit'  => 500,
                    'select' => 'id,name,ip',
                    'q'      => 'id!=000',
                ]);

            if ($response->successful()) {
                return $response->json('data.affected_items') ?? [];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch agents with IPs: ' . $e->getMessage());
        }

        return [];
    }
}
