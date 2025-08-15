<?php
/**
 * OPcache Preload Script for Laravel Octane
 * Preloads core Laravel files into memory for better performance
 */

if (function_exists('opcache_compile_file')) {
    // Preload Composer autoloader
    opcache_compile_file('/app/vendor/autoload.php');
    
    // Preload Laravel core files
    $files = array(
        '/app/vendor/laravel/framework/src/Illuminate/Foundation/Application.php',
        '/app/vendor/laravel/framework/src/Illuminate/Container/Container.php',
        '/app/vendor/laravel/framework/src/Illuminate/Support/ServiceProvider.php',
        '/app/vendor/laravel/framework/src/Illuminate/Http/Request.php',
        '/app/vendor/laravel/framework/src/Illuminate/Http/Response.php',
    );
    
    foreach ($files as $file) {

        if (file_exists($file)) {
            opcache_compile_file($file);
        }
        
    }
}
