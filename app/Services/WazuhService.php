<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
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

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withoutVerifying()
            ->connectTimeout(config('dashboard.http.connect_timeout'))
            ->timeout(config('dashboard.http.timeout'));
    }

    public function getToken(): ?string
    {
        $cached = Cache::get('wazuh_api_token');
        if ($cached) return $cached;

        try {
            $response = $this->http()
                ->withBasicAuth($this->_user, $this->_password)
                ->post("{$this->_host}/security/user/authenticate");

            if (!$response->successful()) {
                Log::warning('Wazuh token request failed: ' . $response->status());
                return null;
            }

            $token = $response->json('data.token');
            if ($token) Cache::put('wazuh_api_token', $token, config('dashboard.cache.token_ttl'));
            return $token;
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
            $summary = $this->http()
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

    public function getAgents(string $token, int $offset = 0, int $limit = 10, ?string $search = null, ?string $status = null, ?array $agentIds = null): array
    {
        try {
            $params = ['offset' => $offset, 'limit' => $limit, 'sort' => 'id'];
            if ($search) $params['search'] = $search;
            if ($status && in_array($status, ['active', 'disconnected', 'pending', 'never_connected'])) {
                $params['status'] = $status;
            }
            if (!empty($agentIds)) {
                $params['agents_list'] = implode(',', $agentIds);
            }

            $response = $this->http()
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
            $response = $this->http()
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
            $response = $this->http()
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

            $response = $this->http()
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

    public function getVulnerabilities(string $token, string $agentId, int $limit = 10, int $offset = 0, ?string $severity = null): array
    {
        try {
            $params = ['limit' => $limit, 'offset' => $offset];
            if ($severity) $params['severity'] = $severity;

            $response = $this->http()
                ->withToken($token)
                ->get("{$this->_host}/vulnerability/{$agentId}", $params);

            if ($response->successful()) {
                $data = $response->json('data');
                return ['data' => $data['affected_items'] ?? [], 'total' => $data['total_affected_items'] ?? 0];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch vulnerabilities: ' . $e->getMessage());
        }
        return ['data' => [], 'total' => 0];
    }

    public function getVulnerabilityCounts(string $token, string $agentId): array
    {
        $severities = config('dashboard.severity_levels', ['Critical', 'High', 'Medium', 'Low']);

        try {
            $responses = Http::pool(fn ($pool) => array_map(
                fn ($sev) => $pool->as($sev)
                    ->withoutVerifying()
                    ->connectTimeout(config('dashboard.http.connect_timeout'))
                    ->timeout(config('dashboard.http.timeout'))
                    ->withToken($token)
                    ->get("{$this->_host}/vulnerability/{$agentId}", ['severity' => $sev, 'limit' => 1]),
                $severities
            ));

            $counts = [];
            foreach ($severities as $sev) {
                $counts[$sev] = ($responses[$sev] instanceof \Illuminate\Http\Client\Response && $responses[$sev]->successful())
                    ? ($responses[$sev]->json('data.total_affected_items') ?? 0)
                    : 0;
            }
            return $counts;
        } catch (\Exception $e) {
            Log::warning('Failed to fetch vulnerability counts: ' . $e->getMessage());
            return array_fill_keys($severities, 0);
        }
    }

    public function getVulnerabilitiesLastScan(string $token, string $agentId): ?array
    {
        try {
            $response = $this->http()
                ->withToken($token)
                ->get("{$this->_host}/vulnerability/{$agentId}/last_scan");

            if ($response->successful()) {
                $items = $response->json('data.affected_items');
                return !empty($items) ? $items[0] : null;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch vulnerability last scan: ' . $e->getMessage());
        }
        return null;
    }

    public function getAgentsWithIPs(): array
    {
        try {
            $token = $this->getToken();
            if (!$token) return [];

            $response = $this->http()
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

    public function getInventoryHardware(string $token, string $agentId): ?array
    {
        try {
            $response = $this->http()
                ->withToken($token)->get("{$this->_host}/syscollector/{$agentId}/hardware");
            if ($response->successful()) {
                $items = $response->json('data.affected_items');
                return !empty($items) ? $items[0] : null;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch inventory hardware: ' . $e->getMessage());
        }
        return null;
    }

    public function getInventoryOS(string $token, string $agentId): ?array
    {
        try {
            $response = $this->http()
                ->withToken($token)->get("{$this->_host}/syscollector/{$agentId}/os");
            if ($response->successful()) {
                $items = $response->json('data.affected_items');
                return !empty($items) ? $items[0] : null;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch inventory OS: ' . $e->getMessage());
        }
        return null;
    }

    public function getInventoryNetInterfaces(string $token, string $agentId, int $limit = 10, int $offset = 0, ?string $search = null): array
    {
        return $this->_getSyscollector($token, $agentId, 'netiface', $limit, $offset, $search);
    }

    public function getInventoryNetPorts(string $token, string $agentId, int $limit = 10, int $offset = 0, ?string $search = null): array
    {
        return $this->_getSyscollector($token, $agentId, 'ports', $limit, $offset, $search);
    }

    public function getInventoryNetAddr(string $token, string $agentId, int $limit = 10, int $offset = 0, ?string $search = null): array
    {
        return $this->_getSyscollector($token, $agentId, 'netaddr', $limit, $offset, $search);
    }

    public function getInventoryHotfixes(string $token, string $agentId, int $limit = 10, int $offset = 0, ?string $search = null): array
    {
        return $this->_getSyscollector($token, $agentId, 'hotfixes', $limit, $offset, $search);
    }

    public function getInventoryPackages(string $token, string $agentId, int $limit = 10, int $offset = 0, ?string $search = null): array
    {
        return $this->_getSyscollector($token, $agentId, 'packages', $limit, $offset, $search);
    }

    public function getInventoryProcesses(string $token, string $agentId, int $limit = 10, int $offset = 0, ?string $search = null): array
    {
        return $this->_getSyscollector($token, $agentId, 'processes', $limit, $offset, $search);
    }

    public function getAgentOsList(string $token, array $agentIds): array
    {
        try {
            $response = $this->http()
                ->withToken($token)
                ->get("{$this->_host}/agents", [
                    'limit'       => 500,
                    'select'      => 'id,name,os.name',
                    'agents_list' => implode(',', $agentIds),
                ]);

            if ($response->successful()) {
                return $response->json('data.affected_items') ?? [];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch agent OS list: ' . $e->getMessage());
        }

        return [];
    }

    private function _getSyscollector(string $token, string $agentId, string $endpoint, int $limit, int $offset, ?string $search): array
    {
        try {
            $params = ['limit' => $limit, 'offset' => $offset];
            if ($search) $params['search'] = $search;

            $response = $this->http()
                ->withToken($token)->get("{$this->_host}/syscollector/{$agentId}/{$endpoint}", $params);

            if ($response->successful()) {
                $data = $response->json('data');
                return ['data' => $data['affected_items'] ?? [], 'total' => $data['total_affected_items'] ?? 0];
            }
        } catch (\Exception $e) {
            Log::warning("Failed to fetch syscollector/{$endpoint}: " . $e->getMessage());
        }
        return ['data' => [], 'total' => 0];
    }
}
