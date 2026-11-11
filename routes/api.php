<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

// Rotas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rotas protegidas pelo Sanctum (exigem o header Authorization: Bearer {token})
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // CRUD de eventos
    Route::apiResource('events', EventController::class);

    // Inscrições
    Route::post('/events/{event}/registrations', [RegistrationController::class, 'store']);
    Route::get('/registrations', [RegistrationController::class, 'index']);
    Route::delete('/registrations/{registration}', [RegistrationController::class, 'destroy']);
});
