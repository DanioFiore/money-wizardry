<?php

use Laravel\Octane\Events\TaskReceived;
use Laravel\Octane\Events\TickReceived;
use Laravel\Octane\Events\RequestHandled;
use Laravel\Octane\Events\TaskTerminated;
use Laravel\Octane\Events\TickTerminated;
use Laravel\Octane\Events\WorkerStarting;
use Laravel\Octane\Events\WorkerStopping;
use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\RequestTerminated;
use Laravel\Octane\Listeners\FlushLogContext;
use Laravel\Octane\Listeners\ReportException;
use Laravel\Octane\Events\WorkerErrorOccurred;
use Laravel\Octane\Listeners\StopWorkerIfNecessary;
use Laravel\Octane\Listeners\EnsureUploadedFilesAreValid;
use Laravel\Octane\Listeners\EnsureUploadedFilesCanBeMoved;

return [
    /*
    |--------------------------------------------------------------------------
    | Octane Server - Cloud Run Optimized
    |--------------------------------------------------------------------------
    | Using FrankenPHP for optimal performance on Cloud Run
    */
    'server' => env('OCTANE_SERVER', 'frankenphp'),

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS - Cloud Run handles HTTPS termination
    |--------------------------------------------------------------------------
    */
    'https' => env('OCTANE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Octane Listeners - Cloud Run Optimized
    |--------------------------------------------------------------------------
    | Minimal listeners for better performance on serverless
    */
    'listeners' => [
        WorkerErrorOccurred::class => [
            ReportException::class,
            // Removed StopWorkerIfNecessary for Cloud Run stability
        ],
        WorkerStarting::class => [
            // Minimal startup for faster cold starts
        ],
        WorkerStopping::class => [],
        RequestReceived::class => [],
        RequestHandled::class => [],
        RequestTerminated::class => [
            // Keep log context flushing for Cloud Logging
            FlushLogContext::class,
        ],
        TaskReceived::class => [],
        TaskTerminated::class => [
            FlushLogContext::class,
        ],
        TickReceived::class => [],
        TickTerminated::class => [
            FlushLogContext::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Warm / Flush Bindings - Cloud Run Optimized
    |--------------------------------------------------------------------------
    | Minimal warming for faster cold starts, essential services only
    */
    'warm' => [
        'config',
        'log',
        'router',
        // Removed database warming for Cloud Run compatibility
        // 'auth', 'cache', 'cache.store', 'db', 'queue', 'request', 'session', 'session.store', 'view'
    ],

    'flush' => [
        // Minimal flushing for Cloud Run
        'auth',
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane Cache Table - Cloud Run Optimized
    |--------------------------------------------------------------------------
    | Reduced cache size for memory efficiency
    */
    'cache' => [
        'rows' => env('OCTANE_CACHE_ROWS', 500), // Reduced from 1000 for Cloud Run
    ],

    /*
    |--------------------------------------------------------------------------
    | File Watching - Disabled for Cloud Run
    |--------------------------------------------------------------------------
    | File watching is disabled in production/Cloud Run for performance
    */
    'watch' => env('OCTANE_WATCH', false) ? [
        'app',
        'config',
        'resources/views',
        'routes',
    ] : [],

    /*
    |--------------------------------------------------------------------------
    | Garbage Collection - Cloud Run Optimized
    |--------------------------------------------------------------------------
    | Aggressive garbage collection for memory management on Cloud Run
    */
    'garbage_collection' => [
        'enabled' => env('OCTANE_GC_ENABLED', true),
        'app_requests' => env('OCTANE_GC_APP_REQUESTS', 500), // More frequent for Cloud Run
        'task_requests' => env('OCTANE_GC_TASK_REQUESTS', 100), // Reduced for better memory management
    ],

    /*
    |--------------------------------------------------------------------------
    | Maximum Execution Time - Cloud Run Limit
    |--------------------------------------------------------------------------
    | Aligned with Cloud Run timeout limits
    */
    'max_execution_time' => env('OCTANE_MAX_EXECUTION_TIME', 30),

    /*
    |--------------------------------------------------------------------------
    | Cloud Run Specific Optimizations
    |--------------------------------------------------------------------------
    */
    'cloud_run' => [
        'memory_limit' => env('PHP_MEMORY_LIMIT', '512M'),
        'worker_memory_limit' => env('OCTANE_WORKER_MEMORY_LIMIT', '256M'),
        'enable_request_pooling' => env('OCTANE_ENABLE_REQUEST_POOLING', false),
        'max_concurrent_requests' => env('OCTANE_MAX_CONCURRENT_REQUESTS', 100),
    ],
];
