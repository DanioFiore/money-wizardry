<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

Route::get('/', function () {
    return view('welcome');
});

// Ultra-fast health checks for Cloud Run startup probes
Route::get('/health', [HealthController::class, 'check']);
Route::get('/ready', [HealthController::class, 'ready']);

// Database-dependent health check (for when app is fully running)
Route::get('/health/db', function () {
    try {
        DB::connection()->getPdo();
        
        return response()->json([
            'status' => 'ok',
            'database' => 'connected',
            'timestamp' => now()->toISOString()
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'database' => 'disconnected',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString()
        ], 503);
    }
});
