<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Models\WazuhAgent;
use App\Models\LogActivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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

        return view('profile.index', compact('user', 'agents'));
    }

    public function logs()
    {
        try {
            $user    = Auth::user();
            $perPage = 10;
            $page    = max((int) request('page', 1), 1);
            $offset  = ($page - 1) * $perPage;

            $query = LogActivity::where('user_id', $user->id)->orderBy('created_at', 'desc');

            if ($search = request('search')) {
                $query->where('activity', 'like', '%' . $search . '%');
            }

            $total      = $query->count();
            $logs       = $query->skip($offset)->take($perPage)->get();
            $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 1;
            $from       = $total > 0 ? $offset + 1 : 0;
            $to         = min($offset + $perPage, $total);

            return response()->json([
                'logs' => $logs->map(function ($log) {
                    $ca = $log->created_at ? Carbon::parse($log->created_at) : null;
                    return [
                        'activity'             => $log->activity,
                        'created_at_human'     => $ca?->diffForHumans(),
                        'created_at_formatted' => $ca?->translatedFormat('d M Y H:i'),
                    ];
                }),
                'total'      => $total,
                'page'       => $page,
                'perPage'    => $perPage,
                'totalPages' => $totalPages,
                'from'       => $from,
                'to'         => $to,
            ]);
        } catch (\Exception $e) {
            Log::error('Profile logs error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat log aktivitas'], 500);
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();

        if (!Hash::check($request->validated()['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        DB::transaction(function () use ($user, $request) {
            $user->password = Hash::make($request->validated()['new_password']);
            $user->save();

            LogActivity::create([
                'user_id'  => $user->id,
                'activity' => 'Pengguna mengubah password',
            ]);
        });

        session()->flash('password_success', 'Password berhasil diubah.');

        return back();
    }
}
