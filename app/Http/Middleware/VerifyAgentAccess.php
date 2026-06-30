<?php

namespace App\Http\Middleware;

use App\Models\WazuhAgent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyAgentAccess
{
    public function handle(Request $request, Closure $next): mixed
    {
        $agentId = $request->route('id');
        if ($agentId === null) {
            return $next($request);
        }

        $user    = auth()->user();
        $agentId = (string) $agentId;

        $dbAgent = WazuhAgent::where('agent_id', $agentId)->first();
        if (!$dbAgent) {
            abort(404, 'Agent tidak ditemukan');
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($dbAgent->user_id !== $user->id) {
            Log::warning('Customer does not have access to agent', ['agent_id' => $agentId, 'user_id' => $user->id]);
            abort(403, 'Anda tidak memiliki izin untuk melihat agent ini');
        }

        return $next($request);
    }
}
