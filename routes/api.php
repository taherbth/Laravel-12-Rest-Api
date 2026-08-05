<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController; 
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CommonResourcesController;
use App\Http\Controllers\Api\CustomerController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::post('register', [AuthController::class, 'register']);
// Route::post('login', [AuthController::class, 'login']);
// Route::get('/common-resources', [CommonResourcesController::class, 'getCommonResources']);
// Route::middleware('auth:api')->group(function () {
//     Route::apiResource('products', ProductController::class);
//     Route::apiResource('customer', CustomerController::class);
//     Route::post('customer/save_customer', [CustomerController::class,'saveCustomer']);
// });

// Wrap all endpoints in a 'v1' prefix group
Route::prefix('v1')->group(function () {
    // Public Routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('/common-resources', [CommonResourcesController::class, 'getCommonResources']);

    // Protected CRUD Routes
    Route::middleware('auth:api')->group(function () {
        Route::apiResource('products', ProductController::class);
        // Use plural naming conventions ('customers') for standard RESTful APIs
        Route::apiResource('customers', CustomerController::class);
        Route::post('customers/remove_customer', [CustomerController::class, 'removeCustomer']);
    });
    
});