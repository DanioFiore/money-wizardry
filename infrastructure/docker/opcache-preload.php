<?php
/**
 * OPcache Preload Script for Laravel Octane
 * 
 * Note: Laravel Octane with FrankenPHP already handles efficient class loading.
 * Manual preloading can cause "class already in use" errors.
 * This script is disabled to prevent conflicts.
 */

// OPcache preloading disabled for Laravel Octane compatibility
// FrankenPHP + Octane provides efficient class loading without manual preloading

if (false && function_exists('opcache_compile_file')) {
    // This block is intentionally disabled
    // to prevent class redeclaration errors
    
    // Laravel Octane already optimizes class loading
    // Manual preloading is not needed and can cause issues
}
