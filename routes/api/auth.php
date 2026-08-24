<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));
Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/password-reset-request', [\App\Http\Controllers\Auth\PasswordController::class, 'requestReset']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::post('/change-password', [\App\Http\Controllers\Auth\PasswordController::class, 'changePassword'])->middleware('auth:sanctum');
Route::get('/my-password-reset-requests', [\App\Http\Controllers\Auth\PasswordController::class, 'getMyResetRequests'])->middleware('auth:sanctum');
