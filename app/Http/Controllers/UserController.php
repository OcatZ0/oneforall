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
            $query->where('role', $request->input('role'));
        }

        $query->orderBy('created_at', 'desc');

        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50])) {
            $perPage = 10;
        }

        $users = $query->paginate($perPage);

        $userStats   = [
            'total'    => User::count(),
            'admin'    => User::where('role', 'admin')->count(),
            'customer' => User::where('role', 'customer')->count(),
        ];
        $savedLayout = DashboardLayout::where('user_id', auth()->user()->id)
                                      ->where('page', 'user')
                                      ->value('layout');

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
            'username' => 'required|string|min:3|max:50|unique:users,username',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,customer',
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
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        if (!empty($validated['agents'])) {
            foreach ($validated['agents'] as $agentId) {
                Agent::where('agent_id', $agentId)->update(['user_id' => $user->id]);
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
        $userAgentIds    = $user->agents()->pluck('agent_id')->toArray();

        return view('user.edit-user', compact('user', 'availableAgents', 'userAgentIds'));
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('user')->with('error', 'User tidak ditemukan.');
        }

        $validated = $request->validate([
            'username' => "required|string|min:3|max:50|unique:users,username,{$id},id",
            'email'    => "required|email|unique:users,email,{$id},id",
            'role'     => 'required|in:admin,customer',
            'agents'   => 'array',
            'agents.*' => 'string',
        ]);

        if (!empty($validated['agents'])) {
            $assignmentError = $this->validateAgentAssignment($validated['agents'], $user->id);
            if ($assignmentError) {
                return redirect()->back()->withInput()->with('error', $assignmentError);
            }
        }

        $user->update([
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'role'     => $validated['role'],
        ]);

        $user->agents()->update(['user_id' => null]);

        if (!empty($validated['agents'])) {
            foreach ($validated['agents'] as $agentId) {
                Agent::where('agent_id', $agentId)->update(['user_id' => $user->id]);
            }
        }

        return redirect()->route('user')->with('success', "User '{$validated['username']}' berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
        }

        if ($user->id === auth()->user()->id) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus akun sendiri.'], 403);
        }

        $user->agents()->update(['user_id' => null]);
        $user->delete();

        return response()->json(['success' => true, 'message' => "User '{$user->username}' berhasil dihapus."]);
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
            $agentId = (string) $agent->agent_id;
            return [
                'id'          => $agent->agent_id,
                'name'        => $agent->name,
                'ip'          => $wazuhAgentsMap[$agentId]['ip'] ?? 'N/A',
                'assigned'    => !is_null($agent->user_id),
                'assigned_to' => $agent->user?->username,
            ];
        })->toArray();
    }

    private function validateAgentAssignment(array $agentIds, ?int $excludeUserId = null): ?string
    {
        if (empty($agentIds)) return null;

        $query = Agent::whereIn('agent_id', $agentIds)->whereNotNull('user_id');
        if ($excludeUserId !== null) {
            $query->where('user_id', '!=', $excludeUserId);
        }

        $conflicting = $query->with('user')->get();
        if ($conflicting->isNotEmpty()) {
            $names = $conflicting->map(fn($a) => "{$a->name} (assigned to {$a->user->username})")->implode(', ');
            return "Gagal: Agent berikut sudah ditugaskan ke pengguna lain: {$names}";
        }

        return null;
    }
}
