<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Demo\CategoriesDemoApiController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1/demo/admin')->group(function () {
    Route::get('/categories/page-data', [CategoriesDemoApiController::class, 'pageData']);
    Route::post('/categories', [CategoriesDemoApiController::class, 'store']);
    Route::put('/categories/{categoryId}', [CategoriesDemoApiController::class, 'update'])->whereNumber('categoryId');
    Route::delete('/categories/{categoryId}', [CategoriesDemoApiController::class, 'destroy'])->whereNumber('categoryId');
    Route::post('/categories/ai-description', [CategoriesDemoApiController::class, 'generateDescription']);
    Route::post('/categories/setup/commit', [CategoriesDemoApiController::class, 'commitSetup']);
});
