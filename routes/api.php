<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::post('/tasks/analyze', [TaskController::class, 'analyzeTask']);
Route::get('/tasks', [TaskController::class, 'apiIndex']);
