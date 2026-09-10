<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content Source
    |--------------------------------------------------------------------------
    |
    | Where the public site reads its content from.
    |
    |   static   — resources/content/*.php, the files in the repository
    |   database — the CMS tables, edited from /admin
    |
    | The default is "database": the CMS exists, and a workspace whose edits
    | the site ignores is worse than no workspace at all. The files remain the
    | seed and the fallback — DatabaseContent reads them for the handful of
    | singleton values that have no row yet, so an unseeded database renders
    | rather than fatals.
    |
    | The test suite pins this back to "static" in phpunit.xml. Its public
    | smoke tests run against an empty database on purpose, and the database
    | path has its own coverage in DatabaseContentIntegrationTest.
    |
    */

    'content_source' => env('FORMIVA_CONTENT', 'database'),

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
        'enabled' => filter_var(env('FORMIVA_CONTENT_CACHE', false), FILTER_VALIDATE_BOOLEAN),
        'ttl' => env('FORMIVA_CONTENT_CACHE_TTL', 3600),
    ],

    'admin' => [
        'login_throttle' => 'admin-login',
    ],

];
