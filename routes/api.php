<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->middleware(['api'])->group(function (){
    Route::post('/register/{type?}', [AuthController::class, 'register']);
    Route::post('/login/{type?}', [AuthController::class, 'login']);
    Route::post('/logout/{type?}', [AuthController::class, 'logout']);
});
