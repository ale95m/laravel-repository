<?php

use Easy\Http\Controllers\AuthController;
use Easy\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware(['auth:api'])->group(function () {
        Route::get('user', function (Request $request) {
            return $request->user();
        })->name('auth.user');
        Route::get('logout', [AuthController::class, 'logout']);
    });
});

Route::prefix(['middleware' => config('easy.get_file_middleware')])->group(function () {
    Route::get( (config('easy.get_file_prefix')).'/{file}', [FileController::class, 'get'])->name('easy.getFile');
});