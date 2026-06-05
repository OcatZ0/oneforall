<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password'      => 'required',
            'new_password'          => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->kata_sandi)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->kata_sandi = Hash::make($request->new_password);
        $user->save();

        LogActivity::create([
            'id_pengguna' => $user->id_pengguna,
            'aktivitas'   => 'Pengguna mengubah password',
        ]);

        session()->flash('password_success', 'Password berhasil diubah.');

        return back();
    }
}
