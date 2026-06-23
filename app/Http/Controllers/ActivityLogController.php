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
            $options  = config('dashboard.activity_log.per_page_options', [25, 50, 100]);
            $default  = config('dashboard.activity_log.default_per_page', 25);
            $perPage  = in_array((int) request('per_page', $default), $options) ? (int) request('per_page', $default) : $default;

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
            $savedLayout = $this->getLayout('activity-log');

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
