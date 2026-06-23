<?php

namespace App\Helpers;

class AgentHelper
{
    public static function getOSIcon(?string $os): string
    {
        if (!$os) return 'mdi-help-circle-outline';
        $os = strtolower($os);
        if (str_contains($os, 'windows')) return 'mdi-microsoft-windows';
        if (str_contains($os, 'ubuntu') || str_contains($os, 'debian') || str_contains($os, 'linux')
            || str_contains($os, 'centos') || str_contains($os, 'rhel') || str_contains($os, 'fedora')) {
            return 'mdi-linux';
        }
        if (str_contains($os, 'mac') || str_contains($os, 'darwin')) return 'mdi-apple';
        return 'mdi-help-circle-outline';
    }

    public static function getStatusBadgeColor(?string $status): string
    {
        return match ($status) {
            'active'          => 'success',
            'disconnected'    => 'danger',
            'pending'         => 'warning',
            'never_connected' => 'secondary',
            default           => 'secondary',
        };
    }

    public static function formatStatus(?string $status): string
    {
        return match ($status) {
            'active'          => 'Aktif',
            'disconnected'    => 'Terputus',
            'pending'         => 'Menunggu',
            'never_connected' => 'Tidak Pernah Terhubung',
            default           => ucfirst($status ?? ''),
        };
    }
}
