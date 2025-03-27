<?php

use Illuminate\Support\Facades\Route;

Route::resource('tasks', \App\Http\Controllers\API\TaskController::class)
    ->middleware('auth:sanctum');
