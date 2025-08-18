<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Health check per Kubernetes
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString()
    ]);
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
