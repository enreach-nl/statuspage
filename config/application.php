<?php

return [

    'gtm_name' => env('APPLICATION_GTM_NAME'),
    'ip_whitelist' => env('APPLICATION_IP_WHITELIST', '127.0.0.1'),

    'feed' => [
        'rss_incidents' => env('APPLICATION_FEED_RSS_INCIDENTS', false),
        'rss_status' => env('APPLICATION_FEED_RSS_STATUS', true),
    ]

];
