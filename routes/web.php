<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'VibeFlow API',
        'version' => '1.0',
        'docs' => '/api/boards',
    ]);
});
