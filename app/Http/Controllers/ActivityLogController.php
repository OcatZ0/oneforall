<?php

namespace App\Http\Controllers;

use App\Models\LogActivity;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ActivityLogController extends Controller
{
    public function index()
    {
        try {
            $search   = request('search');
            $userId   = request('user_id');
            $dateFrom = request('date_from');
            $dateTo   = request('date_to');
            $perPage  = in_array((int) request('per_page', 25), [25, 50, 100]) ? (int) request('per_page', 25) : 25;

            $query = LogActivity::with('user')->orderBy('tanggal', 'desc');

            if ($search) {
                $query->where('aktivitas', 'like', '%' . $search . '%');
            }
            if ($userId) {
                $query->where('id_pengguna', $userId);
            }
            if ($dateFrom) {
                $query->whereDate('tanggal', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('tanggal', '<=', $dateTo);
            }

            $logs  = $query->paginate($perPage)->appends(request()->query());
            $users = User::orderBy('username')->get(['id_pengguna', 'username']);

            return view('activity-log.index', compact('logs', 'users', 'search', 'userId', 'dateFrom', 'dateTo', 'perPage'));
        } catch (\Exception $e) {
            Log::error('Activity log error: ' . $e->getMessage());
            return view('activity-log.index', [
                'logs'     => collect(),
                'users'    => collect(),
                'search'   => null,
                'userId'   => null,
                'dateFrom' => null,
                'dateTo'   => null,
                'perPage'  => 25,
            ]);
        }
    }
}
