<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Agent;
use App\Services\OpenSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Search by username or email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        // Filter by role
        if ($request->filled('role') && $request->input('role') !== '') {
            $query->where('peran', $request->input('role'));
        }

        // Sort by latest created
        $query->orderBy('tanggal_dibuat', 'desc');

        // Paginate
        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50])) {
            $perPage = 10;
        }
        $users = $query->paginate($perPage);

        return view('user.index', compact('users'));
    }

    public function create()
    {
        $availableAgents = $this->getAvailableAgents();
        return view('user.create', compact('availableAgents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:50|unique:pengguna,username',
            'email' => 'required|email|unique:pengguna,email',
            'password' => 'required|string|min:6',
            'peran' => 'required|in:admin,customer',
            'agents' => 'array',
            'agents.*' => 'string',
        ]);

        // Validate agents before assignment
        if (!empty($validated['agents'])) {
            $assignmentError = $this->validateAgentAssignment($validated['agents']);
            if ($assignmentError) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $assignmentError);
            }
        }

        // Create user
        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'kata_sandi' => Hash::make($validated['password']),
            'peran' => $validated['peran'],
        ]);

        // Assign agents if provided
        if (!empty($validated['agents'])) {
            foreach ($validated['agents'] as $agentId) {
                // Update existing agent record to assign to this user
                Agent::where('id_agent', $agentId)->update([
                    'id_pengguna' => $user->id_pengguna,
                ]);
            }
        }

        return redirect()->route('user')->with('success', "User '{$validated['username']}' berhasil ditambahkan.");
    }

    public function edit($id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('user')->with('error', 'User tidak ditemukan.');
        }

        $availableAgents = $this->getAvailableAgents();
        $userAgentIds = $user->agents()->pluck('id_agent')->toArray();

        return view('user.edit-user', compact('user', 'availableAgents', 'userAgentIds'));
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('user')->with('error', 'User tidak ditemukan.');
        }

        $validated = $request->validate([
            'username' => "required|string|min:3|max:50|unique:pengguna,username,{$id},id_pengguna",
            'email' => "required|email|unique:pengguna,email,{$id},id_pengguna",
            'peran' => 'required|in:admin,customer',
            'agents' => 'array',
            'agents.*' => 'string',
        ]);

        // Validate agents before assignment (exclude current user from check)
        if (!empty($validated['agents'])) {
            $assignmentError = $this->validateAgentAssignment($validated['agents'], $user->id_pengguna);
            if ($assignmentError) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $assignmentError);
            }
        }

        // Update user
        $user->update([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'peran' => $validated['peran'],
        ]);

        // Update agent assignments
        // First, unassign all agents currently assigned to this user
        $user->agents()->update(['id_pengguna' => null]);

        // Then assign new agents
        if (!empty($validated['agents'])) {
            foreach ($validated['agents'] as $agentId) {
                // Update existing agent record to assign to this user
                Agent::where('id_agent', $agentId)->update([
                    'id_pengguna' => $user->id_pengguna,
                ]);
            }
        }

        return redirect()->route('user')->with('success', "User '{$validated['username']}' berhasil diperbarui.");
    }

    /**
     * Get all agents with assignment status from database
     * Enriched with real IP addresses from Wazuh API
     */
    private function getAvailableAgents()
    {
        // Get all agents from database
        $agentRecords = Agent::with('user')->get();

        // Fetch real agent data from Wazuh API
        $wazuhAgents = $this->getWazuhAgentsWithIPs();
        $wazuhAgentsMap = [];
        
        if (!empty($wazuhAgents)) {
            foreach ($wazuhAgents as $wazuhAgent) {
                $wazuhAgentsMap[(string)$wazuhAgent['id']] = $wazuhAgent;
            }
        }

        $agents = [];
        foreach ($agentRecords as $agent) {
            $agentId = (string)$agent->id_agent;
            
            // Get real IP from Wazuh API, fallback to N/A if not found
            $ip = $wazuhAgentsMap[$agentId]['ip'] ?? 'N/A';
            
            $agents[] = [
                'id' => $agent->id_agent,
                'name' => $agent->nama,
                'ip' => $ip,
                'assigned' => !is_null($agent->id_pengguna),
                'assigned_to' => $agent->user ? $agent->user->username : null,
            ];
        }

        return $agents;
    }

    /**
     * Fetch agent information including IP addresses from Wazuh API
     * Excludes agent 000 (manager)
     * 
     * @return array Array of agents with id, name, and ip
     */
    private function getWazuhAgentsWithIPs()
    {
        try {
            $wazuhHost = env('WAZUH_HOST', 'https://192.168.200.150:55000');
            $wazuhUser = env('WAZUH_USER', 'admin');
            $wazuhPassword = env('WAZUH_PASSWORD', 'Admin123.');

            // Get Wazuh API token
            $tokenResponse = Http::withoutVerifying()
                ->connectTimeout(2)
                ->timeout(2)
                ->withBasicAuth($wazuhUser, $wazuhPassword)
                ->post("{$wazuhHost}/security/user/authenticate");

            if (!$tokenResponse->successful()) {
                Log::warning('Failed to authenticate with Wazuh API: ' . $tokenResponse->status());
                return [];
            }

            $token = $tokenResponse->json('data.token');

            // Get all agents from Wazuh API, excluding manager (000)
            $agentsResponse = Http::withoutVerifying()
                ->connectTimeout(2)
                ->timeout(2)
                ->withToken($token)
                ->get("{$wazuhHost}/agents", [
                    'limit' => 500,
                    'select' => 'id,name,ip',
                    'q' => 'id!=000'  // Exclude manager agent
                ]);

            if (!$agentsResponse->successful()) {
                Log::warning('Wazuh agents API request failed: ' . $agentsResponse->status());
                return [];
            }

            $agents = $agentsResponse->json('data.affected_items') ?? [];
            
            Log::info('Wazuh agents fetched successfully', [
                'total_count' => count($agents),
            ]);
            
            return $agents;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('Wazuh API connection timeout or unreachable: ' . $e->getMessage());
            return [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch agents from Wazuh API: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate that agents being assigned are not already assigned to other customers
     * 
     * @param array $agentIds Agent IDs to validate
     * @param int|null $excludeUserId User ID to exclude from check (for update operations)
     * @return string|null Error message if validation fails, null if valid
     */
    private function validateAgentAssignment($agentIds, $excludeUserId = null)
    {
        if (empty($agentIds)) {
            return null;
        }

        // Find agents that are assigned to other users
        $query = Agent::whereIn('id_agent', $agentIds)
            ->whereNotNull('id_pengguna');

        // Exclude current user if updating
        if ($excludeUserId !== null) {
            $query->where('id_pengguna', '!=', $excludeUserId);
        }

        $conflictingAgents = $query->with('user')->get();

        if ($conflictingAgents->isNotEmpty()) {
            $agentNames = $conflictingAgents->map(function ($agent) {
                return "{$agent->nama} (assigned to {$agent->user->username})";
            })->implode(', ');

            return "Gagal: Agent berikut sudah ditugaskan ke pengguna lain: {$agentNames}";
        }

        return null;
    }
}
