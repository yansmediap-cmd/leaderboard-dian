<?php

return [
    'cache_ttl_minutes' => env('LEADERBOARD_CACHE_TTL', 5),
    'polling_seconds' => env('LEADERBOARD_POLLING_SECONDS', 30),

    'security' => [
        'skip_ip_whitelist_in_local' => env('SKIP_IP_WHITELIST_IN_LOCAL', true),
    ],
];
