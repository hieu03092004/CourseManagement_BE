<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SettingController;

Route::get('general', [SettingController::class, 'general']);
Route::patch('general', [SettingController::class, 'update']);

