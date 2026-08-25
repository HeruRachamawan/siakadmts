<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\WebsiteController;
use App\Http\Controllers\Public\PpdbPublicController;

Route::prefix('public')->group(function () {
    Route::get('/', [WebsiteController::class, 'index']);
    Route::get('/posts', [WebsiteController::class, 'posts']);
    Route::get('/posts/{slug}', [WebsiteController::class, 'post']);
    Route::get('/galleries', [WebsiteController::class, 'galleries']);
    
    // Public PPDB Routes
    Route::get('/ppdb/info', [PpdbPublicController::class, 'getInfo']);
    Route::post('/ppdb/register', [PpdbPublicController::class, 'register']);
    Route::get('/ppdb/status/{keyword}', [PpdbPublicController::class, 'checkStatus']);
});
