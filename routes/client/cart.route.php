<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\CartController;

Route::get('/{id}', [CartController::class, 'getCart']);

