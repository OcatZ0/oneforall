<?php

namespace App\Services\Implementations;

use App\Models\Agent;
use App\Services\Interfaces\IAgentService;
use App\Services\Interfaces\IWazuhService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AgentService implements IAgentService
{
    private IWazuhService $_wazuhService;

    public function __construct(IWazuhService $wazuhService)
    {
        $this->_wazuhService = $wazuhService;
    }

    public function userHasAccessToAgent(string $agentId): bool
    {
        $user    = auth()->user();
        $agentId = (string) $agentId;

        if ($user->peran === 'admin') return true;

        $dbAgent = Agent::where('id_agent', $agentId)->first();
        if (!$dbAgent) {
            Log::warning('Agent not found in database', ['agent_id' => $agentId, 'user_id' => $user->id_pengguna]);
            return false;
        }

        $hasAccess = $dbAgent->id_pengguna === $user->id_pengguna;
        if (!$hasAccess) {
            Log::warning('Customer does not have access to agent', [
                'agent_id'       => $agentId,
                'user_id'        => $user->id_pengguna,
                'agent_owner_id' => $dbAgent->id_pengguna,
            ]);
        }

        return $hasAccess;
    }

    public function getAccessibleAgentIds(): array
    {
        $user = auth()->user();

        if ($user->peran === 'admin') {
            return Agent::pluck('id_agent')->toArray();
        }

        return Agent::where('id_pengguna', $user->id_pengguna)->pluck('id_agent')->toArray();
    }

    public function enrichAgentData(object $agent): object
    {
        $agentId = $agent->id_agent ?? null;
        if ($agentId) {
            $dbAgent = Agent::where('id_agent', $agentId)->with('user')->first();
            if ($dbAgent) {
                $agent->user = $dbAgent->user;
            }
        }

        return $agent;
    }

    public function validateAgentAssignment(array $agentIds, ?int $excludeUserId = null): ?string
    {
        if (empty($agentIds)) return null;

        $query = Agent::whereIn('id_agent', $agentIds)->whereNotNull('id_pengguna');
        if ($excludeUserId !== null) {
            $query->where('id_pengguna', '!=', $excludeUserId);
        }

        $conflicting = $query->with('user')->get();
        if ($conflicting->isNotEmpty()) {
            $names = $conflicting->map(fn($a) => "{$a->nama} (assigned to {$a->user->username})")->implode(', ');
            return "Gagal: Agent berikut sudah ditugaskan ke pengguna lain: {$names}";
        }

        return null;
    }

    public function getAvailableAgents(): array
    {
        $agentRecords   = Agent::with('user')->get();
        $wazuhAgents    = $this->_wazuhService->getAgentsWithIPs();
        $wazuhAgentsMap = [];
        foreach ($wazuhAgents as $wa) {
            $wazuhAgentsMap[(string) $wa['id']] = $wa;
        }

        return $agentRecords->map(function ($agent) use ($wazuhAgentsMap) {
            $agentId = (string) $agent->id_agent;
            return [
                'id'          => $agent->id_agent,
                'name'        => $agent->nama,
                'ip'          => $wazuhAgentsMap[$agentId]['ip'] ?? 'N/A',
                'assigned'    => !is_null($agent->id_pengguna),
                'assigned_to' => $agent->user?->username,
            ];
        })->toArray();
    }

    public function syncFromWazuh(): array
    {
        $token = $this->_wazuhService->getToken();
        if (!$token) {
            return ['success' => false, 'message' => 'Failed to authenticate with Wazuh API'];
        }

        $synced    = 0;
        $updated   = 0;
        $errors    = 0;
        $processed = 0;
        $total     = 0;
        $offset    = 0;
        $limit     = 100;
        $syncedIds = [];

        do {
            $data    = $this->_wazuhService->getAgents($token, $offset, $limit);
            $agents  = $data['agents'] ?? [];
            $total   = $data['total'] ?? 0;

            if (empty($agents)) break;

            foreach ($agents as $wa) {
                $agentId = $wa['id'] ?? null;
                if (!$agentId || $agentId === '000') continue;

                try {
                    $agentData = ['id_agent' => $agentId, 'nama' => $wa['name'] ?? 'Unknown'];
                    $existing  = Agent::where('id_agent', $agentId)->first();
                    $syncedIds[] = $agentId;

                    if ($existing) {
                        if ($existing->nama !== $agentData['nama']) {
                            $existing->update($agentData);
                        }
                        $updated++;
                    } else {
                        Agent::create(array_merge($agentData, [
                            'deskripsi'     => '',
                            'tanggal_dibuat' => Carbon::now(),
                        ]));
                        $synced++;
                    }

                    $processed++;
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Error syncing agent', ['agent_id' => $agentId, 'error' => $e->getMessage()]);
                }
            }

            $offset += $limit;
        } while ($processed < $total && count($agents) > 0);

        $deleted = Agent::whereNotIn('id_agent', $syncedIds)->delete();

        Log::info('Agent sync completed', [
            'synced_new'       => $synced,
            'updated_existing' => $updated,
            'deleted_obsolete' => $deleted,
            'errors'           => $errors,
            'total_processed'  => $processed,
            'total_in_wazuh'   => $total,
        ]);

        return [
            'success'          => true,
            'synced_new'       => $synced,
            'updated_existing' => $updated,
            'deleted_obsolete' => $deleted,
            'total_processed'  => $processed,
            'errors'           => $errors,
            'total_in_wazuh'   => $total,
        ];
    }

    public function mapWazuhAgent(array $wa, bool $full = false): object
    {
        $agent = (object) [
            'id_agent'     => $wa['id'] ?? null,
            'nama'         => $wa['name'] ?? 'Unknown',
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
}
