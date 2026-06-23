<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Models\WazuhAgent;
use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $agents = WazuhAgent::orderBy('name')->limit(100)->get();
        } else {
            $agents = WazuhAgent::where('user_id', $user->id)->get();
        }

        // Fetch activity logs with pagination and search
        $search = request('search');
        $logsQuery = LogActivity::where('user_id', $user->id);

        if ($search) {
            $logsQuery->where('activity', 'like', '%' . $search . '%');
        }

        $logs = $logsQuery->orderBy('created_at', 'desc')->paginate(10);

        return view('profile.index', compact('user', 'agents', 'logs', 'search'));
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();

        if (!Hash::check($request->validated()['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->password = Hash::make($request->validated()['new_password']);
        $user->save();

        LogActivity::create([
            'user_id'  => $user->id,
            'activity' => 'Pengguna mengubah password',
        ]);

        session()->flash('password_success', 'Password berhasil diubah.');

        return back();
    }
}
