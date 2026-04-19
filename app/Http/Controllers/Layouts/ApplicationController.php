<?php

namespace App\Http\Controllers\Layouts;

use App\Http\Controllers\Controller;

class ApplicationController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'ok' => true,
            'service' => 'ViperPro Backend API',
            'status' => 'running',
            'health' => '/healthz.txt',
            'admin' => '/admin',
            'api_base' => '/api',
        ], 200);
    }
}
