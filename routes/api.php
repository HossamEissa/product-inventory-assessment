<?php

use App\Http\Controllers\API\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {

    Route::get('/products/low-stock', [ProductController::class, 'lowStock']);

    Route::apiResource('products', ProductController::class);

    Route::post('/products/{id}/stock', [ProductController::class, 'adjustStock']);

});
