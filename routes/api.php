<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\PowerNodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/nodes', [PowerNodeController::class, 'index']);

Route::get('/node_relations', [PowerNodeController::class, 'nodeRelations']);

Route::put('/node/update/{powerNode}', [PowerNodeController::class, 'update']);
