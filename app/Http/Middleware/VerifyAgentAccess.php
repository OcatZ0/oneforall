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
            abort(404, 'Agent not found');
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($dbAgent->user_id !== $user->id) {
            Log::warning('Customer does not have access to agent', ['agent_id' => $agentId, 'user_id' => $user->id]);
            abort(403, 'You do not have permission to view this agent');
        }

        return $next($request);
    }
}
