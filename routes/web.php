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

// Print Routes for Exam Documents (Naskah, Kisi-Kisi, Kartu Soal, Telaah, Kunci)
Route::prefix('prints/exam')->group(function () {
    Route::get('{id}/naskah', [\App\Http\Controllers\Kurikulum\KurikulumExamReviewController::class, 'printNaskah']);
    Route::get('{id}/kisi-kisi', [\App\Http\Controllers\Kurikulum\KurikulumExamReviewController::class, 'printKisiKisi']);
    Route::get('{id}/kartu-soal', [\App\Http\Controllers\Kurikulum\KurikulumExamReviewController::class, 'printKartuSoal']);
    Route::get('{id}/telaah', [\App\Http\Controllers\Kurikulum\KurikulumExamReviewController::class, 'printTelaah']);
    Route::get('{id}/kunci', [\App\Http\Controllers\Kurikulum\KurikulumExamReviewController::class, 'printKunci']);
});

Route::get('/', function () {
    return view('app');
});

Route::get('/{path?}', function () {
    return view('app');
})->where('path', '.*');
