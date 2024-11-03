<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Middleware\AdminMiddleware;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['guest'])->group(function () {
    /**
     * route "/login"
     * @method "POST"
     */
    Route::post('/login', LoginController::class)->name('login');
    
    /**
     * route "/user"
     * @method "GET"
     */
    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });
});

/**
 * route "/logout"
 * @method "POST"
 */
Route::post('/logout', LogoutController::class)->name('logout');

Route::middleware(['auth:api'])->group(function () {
    Route::prefix('/admin')->middleware(['admin'])->group(function () {
        //CRUD product
        Route::apiResource('/product', ProductController::class);
        
        //Restore product
        Route::put('/product/{id}/restore', [ProductController::class, 'restore']);
    });

    Route::prefix('/user')->middleware(['user'])->group(function () {
        Route::get('/product', [ProductController::class, 'index']);
        Route::get('/product/{id}', [ProductController::class, 'show']);
    });
});