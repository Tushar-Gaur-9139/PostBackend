<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::get('/post', [PostController::class, 'index']);
Route::post('/post', [PostController::class, 'create']);
Route::delete('/post/{id}', [PostController::class, 'destroy']);
