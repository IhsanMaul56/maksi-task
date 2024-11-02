<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'index']);
});
