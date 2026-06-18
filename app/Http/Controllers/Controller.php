<?php

namespace App\Http\Controllers;

use App\Models\WazuhAgent;

abstract class Controller
{
    protected function getAccessibleAgentIds(): array
    {
        $user = auth()->user();
        if (!$user) return [];

        $query = $user->role === 'admin'
            ? WazuhAgent::query()
            : WazuhAgent::where('user_id', $user->id);

        return $query->pluck('agent_id')->map(fn($id) => (string) $id)->toArray();
    }
}
