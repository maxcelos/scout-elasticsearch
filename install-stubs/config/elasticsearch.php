<?php

return [

    /*
    |--------------------------------------------------------------------------------
    | ElasticSearch config
    |--------------------------------------------------------------------------------
    |
    |
    */
    'routes' => [
        'prefix' => 'api',
        'global_search_url' => 'g-search'
    ],

    'config' => [
        'host' => env('ES_HOST', 'localhost'),
        'port' => env('ES_PORT', '9200'),
        'scheme' => env('ES_SCHEME', 'http'),
        'user' => env('ES_USER', 'user'),
        'pass' => env('ES_PASS', 'pass')
    ]
];
