<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Health check per Kubernetes e Cloud Run (senza dipendenze esterne)
Route::get('/health', function () {
    // Per Cloud Run: health check super veloce senza database
    if (env('SKIP_DATABASE_HEALTH_CHECK', false) || env('CLOUD_RUN_SERVICE')) {
        return response()->json([
            'status' => 'ok',
            'service' => 'money-wizardry',
            'timestamp' => now()->toISOString()
        ]);
    }
    
    // Per Kubernetes: include database check
    try {
        // Quick database ping with timeout
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'ok',
            'service' => 'money-wizardry',
            'database' => 'connected',
            'timestamp' => now()->toISOString()
        ]);
    } catch (Exception $e) {
        // Return ok anyway for health check, log the issue
        Log::warning('Database connection issue during health check: ' . $e->getMessage());
        return response()->json([
            'status' => 'ok',
            'service' => 'money-wizardry',
            'database' => 'disconnected',
            'timestamp' => now()->toISOString()
        ]);
    }
});

Route::get('/ready', function () {
    try {
        DB::connection()->getPdo();
        
        return response()->json([
            'status' => 'ok',
            'database' => 'connected'
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'ko',
            'error' => $e->getMessage()
        ], 503);
    }
});
