<?php
/**
 * OPcache Preload Script for Laravel Octane - Cloud Run Optimized
 * 
 * Cloud Run + Laravel Octane + FrankenPHP Optimization Strategy:
 * - OPcache preloading is DISABLED for serverless compatibility
 * - FrankenPHP handles efficient class loading automatically
 * - Manual preloading causes conflicts and slower cold starts
 * - Cloud Run benefits more from JIT compilation than preloading
 */

// Skip preloading entirely for Cloud Run environment
if (getenv('CLOUD_RUN_SERVICE') || getenv('K_SERVICE')) {
    // Cloud Run detected - skip all preloading for optimal performance
    return;
}

// OPcache preloading disabled for Laravel Octane + FrankenPHP compatibility
// This prevents "class already in use" errors and improves cold start times

if (false && function_exists('opcache_compile_file')) {
    // This block is intentionally disabled for Cloud Run optimization:
    // 1. Prevents class redeclaration errors
    // 2. Reduces memory footprint
    // 3. Faster cold starts
    // 4. Better compatibility with serverless environment
    // 5. FrankenPHP + JIT provides better performance than manual preloading
}

// Cloud Run Performance Notes:
// - JIT compilation is more effective than preloading on serverless
// - Memory constraints make preloading counterproductive
// - Cold start time is more important than steady-state performance
// - FrankenPHP worker mode already optimizes class loading
