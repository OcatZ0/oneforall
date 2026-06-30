<?php

namespace App\Http\Controllers;

use App\Models\LogActivity;
use App\Models\User;
use Carbon\Carbon;
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
            $savedLayout       = $this->getLayout('activity-log');
            $savedLayoutMobile = $this->getLayoutMobile('activity-log');

            return view('activity-log.index', compact('logs', 'users', 'search', 'userId', 'dateFrom', 'dateTo', 'perPage', 'savedLayout', 'savedLayoutMobile'));
        } catch (\Exception $e) {
            Log::error('Activity log error: ' . $e->getMessage());
            return view('activity-log.index', [
                'logs'              => collect(),
                'users'             => collect(),
                'search'            => null,
                'userId'            => null,
                'dateFrom'          => null,
                'dateTo'            => null,
                'perPage'           => 25,
                'savedLayout'       => null,
                'savedLayoutMobile' => null,
            ]);
        }
    }

    public function search()
    {
        try {
            $options = config('dashboard.activity_log.per_page_options', [25, 50, 100]);
            $default = config('dashboard.activity_log.default_per_page', 25);
            $perPage = in_array((int) request('per_page', $default), $options) ? (int) request('per_page', $default) : $default;
            $page    = max((int) request('page', 1), 1);
            $offset  = ($page - 1) * $perPage;

            $query = LogActivity::with('user')->orderBy('created_at', 'desc');

            if ($search = request('search')) {
                $query->where('activity', 'like', '%' . $search . '%');
            }
            if ($userId = request('user_id')) {
                $query->where('user_id', $userId);
            }
            if ($dateFrom = request('date_from')) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo = request('date_to')) {
                $query->whereDate('created_at', '<=', $dateTo);
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
                        'activity'            => $log->activity,
                        'username'            => $log->user?->username,
                        'role'                => $log->user?->role,
                        'created_at_human'    => $ca?->diffForHumans(),
                        'created_at_formatted'=> $ca?->format('d M Y H:i:s'),
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
            Log::error('Activity log search error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat log aktivitas'], 500);
        }
    }
}
