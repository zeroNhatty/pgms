<?php

use App\Http\Controllers\PowerNodesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/nodes', [PowerNodesController::class, 'index']);
