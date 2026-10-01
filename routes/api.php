<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\TablerosController;
use App\Http\Controllers\UsersController;

Route::post('/login/{collaborator_number}', [AuthController::class, 'logincollaborator']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::resource('/roles', RolesController::class);
    Route::resource('/usuarios', UsersController::class);
    Route::resource('/logs', LogsController::class);
    Route::resource('/categorias', CategoriesController::class);
    Route::resource('/tableros', TablerosController::class);

    Route::get('/indexLimited', [UsersController::class, 'indexLimited']);
});
