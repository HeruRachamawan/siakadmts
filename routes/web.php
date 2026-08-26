<?php

use Illuminate\Support\Facades\Route;

Route::get('/sync-deploy/{key}', function ($key) {
    if ($key !== 'masnomo2026siakad') {
        abort(403);
    }
    $output = [];
    exec('git fetch origin main && git reset --hard origin/main && php artisan optimize:clear 2>&1', $output, $code);
    return response()->json([
        'code' => $code,
        'output' => $output
    ]);
});

Route::get('/', function () {
    return view('app');
});

Route::get('/{path?}', function () {
    return view('app');
})->where('path', '.*');
