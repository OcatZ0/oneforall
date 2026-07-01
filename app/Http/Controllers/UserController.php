<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\WazuhAgent;
use App\Models\User;
use App\Services\WazuhService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private WazuhService $_wazuhService;

    public function __construct(WazuhService $_wazuhService)
    {
        $this->_wazuhService = $_wazuhService;
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && $request->input('role') !== '') {
            $query->where('role', $request->input('role'));
        }

        $query->orderBy('created_at', 'desc');

        ['perPage' => $perPage] = $this->paginateRequest();

        $users = $query->paginate($perPage);

        $userStats   = [
            'total'    => User::count(),
            'admin'    => User::where('role', 'admin')->count(),
            'customer' => User::where('role', 'customer')->count(),
        ];
        $savedLayout       = $this->getLayout('user');
        $savedLayoutMobile = $this->getLayoutMobile('user');

        return view('user.index', compact('users', 'userStats', 'savedLayout', 'savedLayoutMobile'));
    }

    public function create()
    {
        $availableAgents = $this->getAvailableAgents();
        return view('user.create', compact('availableAgents'));
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        if (!empty($validated['agents'])) {
            $assignmentError = $this->validateAgentAssignment($validated['agents']);
            if ($assignmentError) {
                return redirect()->back()->withInput()->with('error', $assignmentError);
            }
        }

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'username' => $validated['username'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => $validated['role'],
            ]);

            if (!empty($validated['agents'])) {
                foreach ($validated['agents'] as $agentId) {
                    WazuhAgent::where('agent_id', $agentId)->update(['user_id' => $user->id]);
                }
            }
        });

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

    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('user')->with('error', 'User tidak ditemukan.');
        }

        $validated = $request->validated();

        if (!empty($validated['agents'])) {
            $assignmentError = $this->validateAgentAssignment($validated['agents'], $user->id);
            if ($assignmentError) {
                return redirect()->back()->withInput()->with('error', $assignmentError);
            }
        }

        if ($validated['role'] !== 'admin' && $user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return redirect()->back()->withInput()->with('error', 'Tidak bisa menghapus akun admin terakhir.');
            }
        }

        DB::transaction(function () use ($user, $validated) {
            $user->update([
                'username' => $validated['username'],
                'email'    => $validated['email'],
                'role'     => $validated['role'],
            ]);

            $user->agents()->update(['user_id' => null]);

            if (!empty($validated['agents'])) {
                foreach ($validated['agents'] as $agentId) {
                    WazuhAgent::where('agent_id', $agentId)->update(['user_id' => $user->id]);
                }
            }
        });

        return redirect()->route('user')->with('success', "User '{$validated['username']}' berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return ApiResponse::error('User tidak ditemukan.', 404);
        }

        if ($user->id === auth()->user()->id) {
            return ApiResponse::error('Tidak dapat menghapus akun sendiri.', 403);
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return ApiResponse::error('Tidak bisa menghapus akun admin terakhir.', 403);
        }

        DB::transaction(function () use ($user) {
            $user->agents()->update(['user_id' => null]);
            $user->delete();
        });

        return ApiResponse::success([], "User '{$user->username}' berhasil dihapus.");
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function getAvailableAgents(): array
    {
        $dbAgents = WazuhAgent::with('user')->get()->keyBy('agent_id');
        if ($dbAgents->isEmpty()) return [];

        $token = $this->_wazuhService->getToken();
        if (!$token) {
            return $dbAgents->map(fn($db) => [
                'id'          => $db->agent_id,
                'name'        => $db->name,
                'ip'          => 'N/A',
                'assigned'    => !is_null($db->user_id),
                'assigned_to' => $db->user?->username,
            ])->values()->toArray();
        }

        $wazuhAgents = $this->_wazuhService->getAgents($token, 0, $dbAgents->count(), null, null, $dbAgents->keys()->toArray())['agents'] ?? [];
        $wazuhMap    = collect($wazuhAgents)->keyBy('id');

        return $dbAgents->map(fn($db) => [
            'id'          => $db->agent_id,
            'name'        => $db->name,
            'ip'          => $wazuhMap->get($db->agent_id)['ip'] ?? 'N/A',
            'assigned'    => !is_null($db->user_id),
            'assigned_to' => $db->user?->username,
        ])->values()->toArray();
    }

    private function validateAgentAssignment(array $agentIds, ?int $excludeUserId = null): ?string
    {
        if (empty($agentIds)) return null;

        $query = WazuhAgent::whereIn('agent_id', $agentIds)->whereNotNull('user_id');
        if ($excludeUserId !== null) {
            $query->where('user_id', '!=', $excludeUserId);
        }

        $conflicting = $query->with('user')->get();
        if ($conflicting->isNotEmpty()) {
            $names = $conflicting->map(fn($a) => "{$a->name} (assigned to {$a->user->username})")->implode(', ');
            return "Agent berikut sudah ditugaskan ke pengguna lain: {$names}";
        }

        return null;
    }
}
