<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StatisticsController;

Route::get('/', [StatisticsController::class, 'index']);
