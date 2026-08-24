<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\WebsiteController;

Route::prefix('public')->group(function () {
    Route::get('/', [WebsiteController::class, 'index']);
    Route::get('/posts', [WebsiteController::class, 'posts']);
    Route::get('/posts/{slug}', [WebsiteController::class, 'post']);
    Route::get('/galleries', [WebsiteController::class, 'galleries']);
});
