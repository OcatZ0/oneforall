<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Admin sees all agents, customers see only their own
        if ($user->peran === 'admin') {
            $agents = Agent::all();
        } else {
            $agents = Agent::where('id_pengguna', $user->id_pengguna)->get();
        }

        // Fetch activity logs with pagination and search
        $search = request('search');
        $logsQuery = LogActivity::where('id_pengguna', $user->id_pengguna);

        if ($search) {
            $logsQuery->where('aktivitas', 'like', '%' . $search . '%');
        }

        $logs = $logsQuery->orderBy('tanggal', 'desc')->paginate(10);

        return view('profile.index', compact('user', 'agents', 'logs', 'search'));
    }
}
