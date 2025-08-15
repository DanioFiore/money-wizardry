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
    | Octane Server
    |--------------------------------------------------------------------------
    */
    'server' => env('OCTANE_SERVER', 'frankenphp'),

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS
    |--------------------------------------------------------------------------
    */
    'https' => env('OCTANE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Octane Listeners
    |--------------------------------------------------------------------------
    */
    'listeners' => [
        WorkerErrorOccurred::class => [
            ReportException::class,
            StopWorkerIfNecessary::class,
        ],
        WorkerStarting::class => [
            EnsureUploadedFilesAreValid::class,
            EnsureUploadedFilesCanBeMoved::class,
        ],
        WorkerStopping::class => [],
        RequestReceived::class => [],
        RequestHandled::class => [],
        RequestTerminated::class => [
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
    | Warm / Flush Bindings
    |--------------------------------------------------------------------------
    */
    'warm' => [
        'auth',
        'cache',
        'cache.store',
        'config',
        'db',
        'log',
        'queue',
        'request',
        'router',
        'session',
        'session.store',
        'view',
    ],

    'flush' => [
        'auth',
        'session',
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane Cache Table
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'rows' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Watching
    |--------------------------------------------------------------------------
    */
    'watch' => [
        'app',
        'config',
        'resources/views',
        'routes',
    ],

    /*
    |--------------------------------------------------------------------------
    | Garbage Collection
    |--------------------------------------------------------------------------
    */
    'garbage_collection' => [
        'enabled' => env('OCTANE_GC_ENABLED', true),
        'app_requests' => env('OCTANE_GC_APP_REQUESTS', 10000),
        'task_requests' => env('OCTANE_GC_TASK_REQUESTS', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maximum Execution Time
    |--------------------------------------------------------------------------
    */
    'max_execution_time' => 30,
];
