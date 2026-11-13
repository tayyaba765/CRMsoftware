<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ReqresController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    // Authorization routes to logout and get current user info 
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',     [AuthController::class, 'me']);

    Route::get   ('/users',        [UserController::class, 'index']);
    Route::post  ('/users',        [UserController::class, 'store']);
    Route::get   ('/users/{id}',   [UserController::class, 'show']);
    Route::patch   ('/users/{id}',   [UserController::class, 'update']);
    Route::delete('/users/{id}',   [UserController::class, 'destroy']);
});


Route::prefix('reqres')->group(function () {
    // Fetch live from API (no DB)
    Route::get('/users', [ReqresController::class, 'index']);

    // Fetch from API AND save to database
    Route::post('/users/sync', [ReqresController::class, 'sync']);

    // Read users that are already in the DB
    Route::get('/saved', [ReqresController::class, 'saved']);
});