<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use Illuminate\Http\Request;

// USE THIS CONTROLLER FOR TESTING
class TestsController extends Controller
{
    public function test()
    {
        return ApiResponse::handle(function() use ($id) {
            return 'test success';
        });
    }
}
