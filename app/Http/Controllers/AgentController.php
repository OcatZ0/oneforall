<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Build query
        $query = Agent::with('user');

        // Admin sees all agents, customers see only their own
        if ($user->peran !== 'admin') {
            $query->where('id_pengguna', $user->id_pengguna);
        }

        // Search by agent name or ID
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('nama', 'like', "%{$search}%")
                  ->orWhere('id_agent', 'like', "%{$search}%");
        }

        // Sort by latest created
        $query->orderBy('tanggal_dibuat', 'desc');

        // Paginate
        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50])) {
            $perPage = 10;
        }
        $agents = $query->paginate($perPage);

        // Enrich agents with fake Wazuh data
        $agents->getCollection()->transform(function ($agent) use ($request) {
            $fakeData = $this->generateFakeWazuhData($agent->id_agent);

            $agent->status = $fakeData['status'];
            $agent->ip = $agent->deskripsi ? $this->extractIPFromDescription($agent->deskripsi) : $fakeData['ip'];
            $agent->os = $fakeData['os'];
            $agent->version = $fakeData['version'];
            $agent->cluster_node = $fakeData['cluster_node'];

            return $agent;
        });

        // Filter by status after enrichment
        if ($request->filled('status') && $request->input('status') !== '') {
            $status = $request->input('status');
            $filtered = $agents->getCollection()->filter(function ($agent) use ($status) {
                return $agent->status === $status;
            });
            // Create a new LengthAwarePaginator with filtered items
            $agents = new \Illuminate\Pagination\LengthAwarePaginator(
                $filtered->values(),
                count($filtered),
                $perPage,
                $agents->currentPage(),
                [
                    'path' => $agents->path(),
                    'query' => $request->query(),
                ]
            );
        }

        // Calculate agent statistics
        $stats = $this->calculateAgentStats($agents);

        return view('agent.index', compact('agents', 'stats'));
    }

    /**
     * Generate fake Wazuh API data for an agent
     */
    private function generateFakeWazuhData($agentId)
    {
        $statuses = ['active', 'disconnected', 'pending', 'never_connected'];
        $osOptions = [
            'Microsoft Windows Server 2022 Datacenter 10.0.20348.469',
            'Ubuntu 22.04 LTS',
            'CentOS 7',
            'Debian 11',
            'Red Hat Enterprise Linux 8.5',
            'Amazon Linux 2',
        ];
        $clusterNodes = ['node01', 'node02', 'node03', 'node01'];
        $versions = ['v4.7.5', 'v4.7.4', 'v4.7.3', 'v4.6.0'];

        // Use agent ID as seed for consistent data
        $id = intval($agentId);
        $seed = $id;

        return [
            'status' => $statuses[$seed % count($statuses)],
            'ip' => '192.168.' . (($seed / 10) + 1) . '.' . (10 + $seed),
            'os' => $osOptions[$seed % count($osOptions)],
            'version' => $versions[$seed % count($versions)],
            'cluster_node' => $clusterNodes[$seed % count($clusterNodes)],
        ];
    }

    /**
     * Extract IP from description or generate fake one
     */
    private function extractIPFromDescription($description)
    {
        if (preg_match('/(\d+\.\d+\.\d+\.\d+)/', $description, $matches)) {
            return $matches[1];
        }
        return '192.168.1.100';
    }

    /**
     * Calculate agent statistics
     */
    private function calculateAgentStats($agents)
    {
        $stats = [
            'total' => $agents->total(),
            'active' => 0,
            'disconnected' => 0,
            'pending' => 0,
            'never_connected' => 0,
        ];

        foreach ($agents as $agent) {
            switch ($agent->status) {
                case 'active':
                    $stats['active']++;
                    break;
                case 'disconnected':
                    $stats['disconnected']++;
                    break;
                case 'pending':
                    $stats['pending']++;
                    break;
                case 'never_connected':
                    $stats['never_connected']++;
                    break;
            }
        }

        return $stats;
    }

    /**
     * Get status badge color
     */
    public static function getStatusBadgeColor($status)
    {
        return match ($status) {
            'active' => 'success',
            'disconnected' => 'danger',
            'pending' => 'warning',
            'never_connected' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get OS icon
     */
    public static function getOSIcon($os)
    {
        if (strpos($os, 'Windows') !== false) {
            return 'mdi-microsoft-windows';
        } elseif (strpos($os, 'Ubuntu') !== false || strpos($os, 'Debian') !== false) {
            return 'mdi-linux';
        } elseif (strpos($os, 'CentOS') !== false || strpos($os, 'Red Hat') !== false) {
            return 'mdi-linux';
        } elseif (strpos($os, 'Amazon') !== false) {
            return 'mdi-amazon';
        }
        return 'mdi-server';
    }

    /**
     * Format status string (never_connected -> Never Connected)
     */
    public static function formatStatus($status)
    {
        return match ($status) {
            'active' => 'Active',
            'disconnected' => 'Disconnected',
            'pending' => 'Pending',
            'never_connected' => 'Never Connected',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
