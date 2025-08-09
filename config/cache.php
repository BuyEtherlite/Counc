<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache store that gets used while
    | using route cache:clear or other cache related commands.
    |
    | Supported: "file", "database", "memcached", "redis", "dynamodb", "store"
    |
    */

    'default' => env('CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may configure many cache stores that you wish to use for
    | your application. To get more information about each store,
    | which the "Taylor Otwell" tutorial or each cache store's documentation.
    |
    */

    'stores' => [

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'key' => '_laravel_database_cache_',
            'lock_connection' => 'redis',
        ],

        'memcached' => [
            'driver' => 'memcached',
            'repository' => 'Memcached\Memcached',
            'connections' => [
                'main' => [
                    'servers' => [
                        'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                        'port' => env('MEMCACHED_PORT', 11211),
                        'weight' => 100,
                    ],
                ],
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],

        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => env('AWS_DYNAMODB_TABLE'),
            'endpoint' => env('AWS_DYNAMODB_ENDPOINT'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing a multi-tenant cache, you might want to have
    | a specific prefix. Each cache key can be prefixed automatically
    | by the application. Once these keys are generated, they will
    | be stored in the cache store so that it's completely isolated.
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_cache'),

];