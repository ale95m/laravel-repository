<?php

use Easy\Http\Controllers\AuthController;
use Easy\Http\Controllers\FileController;
use Easy\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

if (config('easy.use_auth')) {
    Route::prefix('auth')->group(function () {
        Route::post('password/reset', [AuthController::class,'reset']);
        Route::post('password/forgot', [AuthController::class,'forgot']);
        Route::get('password/restore', [AuthController::class,'restorePassword'])->name('password.reset');
        Route::post('login', [AuthController::class, 'login'])->name('auth.login');
        Route::middleware(['auth:api'])->group(function () {
            Route::get('user', function (Request $request) {
                $user = $request->user()->load(config('easy.auth_user_relations'));
                return config('easy.json_numeric_check')
                    ? json_encode($user, JSON_NUMERIC_CHECK)
                    : json_encode($user);
            })->name('auth.user');
            Route::get('logout', [AuthController::class, 'logout'])->name('auth.logout');
        });
    });
    if (config('easy.email_verification')) {
        Route::prefix('emailVerify')->middleware(['auth:api'])->group(function () {
            Route::get('{user}', [VerificationController::class, 'verify'])->name('verification.verify');
            Route::get('', [VerificationController::class, 'resend'])->name('verification.resend');
        });
    }
}
if (config('easy.use_file_routes')) {
    Route::group(['middleware' => config('easy.get_file_middleware')], function () {
        Route::get((config('easy.get_file_prefix')) . '/{file}', [FileController::class, 'get',])->name('easy.getFile');
    });
}
