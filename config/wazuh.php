<?php

return [
    'host'         => env('WAZUH_HOST'),
    'user'         => env('WAZUH_USER'),
    'password'     => env('WAZUH_PASSWORD', ''),
    'manager_name' => env('WAZUH_MANAGER_NAME', 'ofa'),
];
