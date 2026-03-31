<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'peran' => 'required|in:customer',
            'agents' => 'array',
            'agents.*' => 'string',
        ]);

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
            'peran' => 'required|in:customer',
            'agents' => 'array',
            'agents.*' => 'string',
        ]);

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
     * Enriched with fake Wazuh data (IP, status, OS, version)
     */
    private function getAvailableAgents()
    {
        // Get all agents from database
        $agentRecords = Agent::with('user')->get();

        $agents = [];
        foreach ($agentRecords as $agent) {
            $agents[] = [
                'id' => $agent->id_agent,
                'name' => $agent->nama,
                'ip' => $this->generateFakeIPForAgent($agent->id_agent),
                'assigned' => !is_null($agent->id_pengguna),
                'assigned_to' => $agent->user ? $agent->user->username : null,
            ];
        }

        return $agents;
    }

    /**
     * Generate fake IP based on agent ID
     */
    private function generateFakeIPForAgent($agentId)
    {
        $id = intval($agentId);
        return "192.168.1." . (10 + $id);
    }

    /**
     * Generate fake agent status
     */
    private function generateFakeStatus()
    {
        $statuses = ['active', 'disconnected', 'pending', 'never_connected'];
        return $statuses[array_rand($statuses)];
    }

    /**
     * Generate fake OS
     */
    private function generateFakeOS()
    {
        $osList = [
            'Microsoft Windows Server 2022 Datacenter 10.0.20348.469',
            'Ubuntu 22.04 LTS',
            'CentOS 7',
            'Debian 11',
            'Red Hat Enterprise Linux 8.5',
            'Amazon Linux 2',
        ];
        return $osList[array_rand($osList)];
    }
}
