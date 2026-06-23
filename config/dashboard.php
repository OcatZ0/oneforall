<?php

return [
    'http' => [
        'timeout'         => (int) env('HTTP_REQUEST_TIMEOUT', 3),
        'connect_timeout' => (int) env('HTTP_CONNECT_TIMEOUT', 3),
    ],
    'cache' => [
        'token_ttl' => (int) env('CACHE_TOKEN_TTL', 800),
    ],
    'pagination' => [
        'per_page_options' => [10, 25, 50],
        'default_per_page' => 10,
    ],
    'time_ranges'      => ['15m', '30m', '1h', '24h', '7d', '30d', '90d', '1y', 'today', 'week'],
    'compliance_types' => ['pci_dss', 'gdpr', 'hipaa', 'nist_800_53', 'tsc'],
    'severity_levels'  => ['Critical', 'High', 'Medium', 'Low'],
    'activity_log'     => [
        'per_page_options' => [25, 50, 100],
        'default_per_page' => 25,
    ],
];
