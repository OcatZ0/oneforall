<?php

namespace App\Http\Controllers;

use App\Models\WazuhAgent;

abstract class Controller
{
    protected function paginateRequest(int $defaultPerPage = null): array
    {
        $options = config('dashboard.pagination.per_page_options', [10, 25, 50]);
        $default = $defaultPerPage ?? config('dashboard.pagination.default_per_page', 10);
        $perPage = in_array((int) request('per_page', $default), $options)
            ? (int) request('per_page', $default)
            : $default;
        $page   = max((int) request('page', 1), 1);
        $offset = ($page - 1) * $perPage;
        return compact('perPage', 'page', 'offset');
    }

    protected function getAccessibleAgentIds(): array
    {
        $user = auth()->user();
        if (!$user) return [];

        $query = $user->role === 'admin'
            ? WazuhAgent::query()
            : WazuhAgent::where('user_id', $user->id);

        return $query->pluck('agent_id')->map(fn($id) => (string) $id)->toArray();
    }

    protected function getLayout(string $page): array|null
    {
        return \App\Models\DashboardLayout::where('user_id', auth()->id())
                                           ->where('page', $page)
                                           ->value('layout');
    }

    protected function getLayoutMobile(string $page): array|null
    {
        return \App\Models\DashboardLayout::where('user_id', auth()->id())
                                           ->where('page', $page . '-mobile')
                                           ->value('layout');
    }
}
