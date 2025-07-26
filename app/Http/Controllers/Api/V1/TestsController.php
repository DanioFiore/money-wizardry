<?php

namespace App\Http\Controllers\Api\V1;

use App\ApiResponse;

class TestsController extends Controller
{
    public function test()
    {
        return ApiResponse::handle(function() {
            return 'test success';
        });
    }
}
