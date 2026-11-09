<?php

use Illuminate\Support\Facades\Route;
use App\Models\Blog;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogController;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    
    Route::get('profile', [AuthController::class, 'profile']);
    
    Route::get('/blogs', [BlogController::class, 'browse']);
    Route::get('/blog/{id}', [BlogController::class, 'read']);   
    Route::put('/blog/{id}', [BlogController::class, 'edit']);
    Route::post('/blogs', [BlogController::class, 'add']);
    Route::delete('/blog/{id}', [BlogController::class, 'delete']);
});
