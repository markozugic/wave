<?php

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AudioConvertController;

Route::get('/', function () {
    return view('welcome');
});


Route::withoutMiddleware([
    ValidateCsrfToken::class,
])->group(function () {

    Route::post('/audio/convert', [AudioConvertController::class, 'store'])->name('audio.convert.store');
    // Result page showing a link to download the converted file
    Route::get('/audio/convert/result/{filename}', [AudioConvertController::class, 'result'])
        ->where('filename', '[A-Za-z0-9\-]+\.(mp3|aac|ogg|flac)')
        ->name('audio.convert.result');
    // Download the converted file by filename kept under storage/app/tmp/audio
    Route::get('/audio/convert/download/{filename}', [AudioConvertController::class, 'download'])
        ->where('filename', '[A-Za-z0-9\-]+\.(mp3|aac|ogg|flac)')
        ->name('audio.convert.download');

});

// Audio conversion routes
Route::get('/audio/convert', [AudioConvertController::class, 'create'])->name('audio.convert.create');

