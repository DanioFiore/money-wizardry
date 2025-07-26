<?php

use Illuminate\Support\Facades\Route;

// api versioning
Route::prefix('v1')->group(base_path('routes/api_v1.php'));