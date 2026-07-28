<?php

use App\Http\Controllers\PowerNodeController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/tickets', [TicketController::class, 'index']);
Route::get('/nodes', [PowerNodeController::class, 'index']);

Route::get('/node_relations', [PowerNodeController::class, 'nodeRelations']);

Route::put('/node/update/{powerNode}', [PowerNodeController::class, 'update']);

Route::put('/tickets/{ticket}', [TicketController::class, 'update']);

Route::get('/node/{id}', [PowerNodeController::class, 'show']);
Route::get('/user/{id}', [UserController::class, 'show']);
