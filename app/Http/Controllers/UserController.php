<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\DashboardLayout;
use App\Models\User;
use App\Services\WazuhService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private WazuhService $_wazuhService;

    public function __construct()
    {
        $this->_wazuhService = new WazuhService();
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        if ($request->filled('role') && $request->input('role') !== '') {
            $query->where('peran', $request->input('role'));
        }

        $query->orderBy('tanggal_dibuat', 'desc');

        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50])) {
            $perPage = 10;
        }

        $users = $query->paginate($perPage);

        $userStats   = [
            'total'    => User::count(),
            'admin'    => User::where('peran', 'admin')->count(),
            'customer' => User::where('peran', 'customer')->count(),
        ];
        $savedLayout = DashboardLayout::where('id_pengguna', auth()->user()->id_pengguna)
                                      ->where('halaman', 'user')
                                      ->value('tata_letak');

        return view('user.index', compact('users', 'userStats', 'savedLayout'));
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
            'email'    => 'required|email|unique:pengguna,email',
            'password' => 'required|string|min:6',
            'peran'    => 'required|in:admin,customer',
            'agents'   => 'array',
            'agents.*' => 'string',
        ]);

        if (!empty($validated['agents'])) {
            $assignmentError = $this->validateAgentAssignment($validated['agents']);
            if ($assignmentError) {
                return redirect()->back()->withInput()->with('error', $assignmentError);
            }
        }

        $user = User::create([
            'username'   => $validated['username'],
            'email'      => $validated['email'],
            'kata_sandi' => Hash::make($validated['password']),
            'peran'      => $validated['peran'],
        ]);

        if (!empty($validated['agents'])) {
            foreach ($validated['agents'] as $agentId) {
                Agent::where('id_agent', $agentId)->update(['id_pengguna' => $user->id_pengguna]);
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
        $userAgentIds    = $user->agents()->pluck('id_agent')->toArray();

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
            'email'    => "required|email|unique:pengguna,email,{$id},id_pengguna",
            'peran'    => 'required|in:admin,customer',
            'agents'   => 'array',
            'agents.*' => 'string',
        ]);

        if (!empty($validated['agents'])) {
            $assignmentError = $this->validateAgentAssignment($validated['agents'], $user->id_pengguna);
            if ($assignmentError) {
                return redirect()->back()->withInput()->with('error', $assignmentError);
            }
        }

        $user->update([
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'peran'    => $validated['peran'],
        ]);

        $user->agents()->update(['id_pengguna' => null]);

        if (!empty($validated['agents'])) {
            foreach ($validated['agents'] as $agentId) {
                Agent::where('id_agent', $agentId)->update(['id_pengguna' => $user->id_pengguna]);
            }
        }

        return redirect()->route('user')->with('success', "User '{$validated['username']}' berhasil diperbarui.");
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function getAvailableAgents(): array
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

    private function validateAgentAssignment(array $agentIds, ?int $excludeUserId = null): ?string
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
}
