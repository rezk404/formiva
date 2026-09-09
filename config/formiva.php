<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content Source
    |--------------------------------------------------------------------------
    |
    | Determines where public content is loaded from. The current frontend
    | reads from StaticContent via ContentRepository. A future phase will
    | introduce DatabaseContent and switch this value to "database".
    |
    | Supported: static, database
    |
    */

    'content_source' => env('FORMIVA_CONTENT', 'static'),

    /*
    |--------------------------------------------------------------------------
    | Content Cache
    |--------------------------------------------------------------------------
    |
    | Optional caching layer for content resolved through ContentRepository.
    | Disabled by default until a database-backed source warrants it.
    |
    */

    'content_cache' => [
        'enabled' => env('FORMIVA_CONTENT_CACHE', false),
        'ttl' => env('FORMIVA_CONTENT_CACHE_TTL', 3600),
    ],

];
