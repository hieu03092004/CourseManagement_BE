<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MyAccountController;

Route::get('/', [MyAccountController::class, 'index']);
Route::patch('/', [MyAccountController::class, 'update']);

