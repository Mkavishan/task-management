<?php

use Illuminate\Support\Facades\Route;

Route::post('register', [\App\Http\Controllers\API\Auth\RegisterController::class, 'register'])->name('register');
Route::post('login', [\App\Http\Controllers\API\Auth\LoginController::class, 'login'])->name('login');

Route::resource('tasks', \App\Http\Controllers\API\TaskController::class)
    ->middleware(['auth:sanctum', 'throttle:api']);
