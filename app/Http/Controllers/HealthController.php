<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    /**
     * Simple health check that doesn't require database
     */
    public function check(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'service' => 'money-wizardry'
        ]);
    }

    /**
     * Readiness check for startup probes
     */
    public function ready(): JsonResponse
    {
        // Very basic readiness check
        $ready = true;
        
        try {
            // Just check if Laravel is booted properly
            if (!app()->isBooted()) {
                $ready = false;
            }
        } catch (\Exception $e) {
            $ready = false;
        }

        if ($ready) {
            return response()->json([
                'status' => 'ready',
                'timestamp' => now()->toISOString()
            ]);
        } else {
            return response()->json([
                'status' => 'not_ready',
                'timestamp' => now()->toISOString()
            ], 503);
        }
    }
}
