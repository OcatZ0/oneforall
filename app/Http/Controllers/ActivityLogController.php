<?php

namespace App\Http\Controllers;

use App\Models\DashboardLayout;
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

            $query = LogActivity::with('user')->orderBy('created_at', 'desc');

            if ($search) {
                $query->where('activity', 'like', '%' . $search . '%');
            }
            if ($userId) {
                $query->where('user_id', $userId);
            }
            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            $logs        = $query->paginate($perPage)->appends(request()->query());
            $users       = User::orderBy('username')->get(['id', 'username']);
            $savedLayout = DashboardLayout::where('user_id', auth()->user()->id)
                                          ->where('page', 'activity-log')
                                          ->value('layout');

            return view('activity-log.index', compact('logs', 'users', 'search', 'userId', 'dateFrom', 'dateTo', 'perPage', 'savedLayout'));
        } catch (\Exception $e) {
            Log::error('Activity log error: ' . $e->getMessage());
            return view('activity-log.index', [
                'logs'        => collect(),
                'users'      => collect(),
                'search'      => null,
                'userId'      => null,
                'dateFrom'    => null,
                'dateTo'      => null,
                'perPage'     => 25,
                'savedLayout' => null,
            ]);
        }
    }
}
