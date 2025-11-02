<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TestDatabaseController;

Route::get('connection', [TestDatabaseController::class, 'testConnection']);
Route::get('products', [TestDatabaseController::class, 'getProducts']);
Route::post('products', [TestDatabaseController::class, 'createProduct']);

