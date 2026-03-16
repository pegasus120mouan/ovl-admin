<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Authentification
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/resend-pin', [AuthController::class, 'resendPin']);
    
    // Routes protégées (token requis)
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
