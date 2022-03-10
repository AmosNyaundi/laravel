<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BuyAirtimeController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\C2BController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('resp/stk', [BuyAirtimeController::class, 'callback']);
Route::post('resp/c2b', [C2BController::class, 'lipa']);
Route::post('reversal', [C2BController::class, 'reversal']);
Route::post('app/self', [BuyAirtimeController::class, 'self']);
Route::post('app/other', [BuyAirtimeController::class, 'other']);
Route::post('web/self', [BuyAirtimeController::class, 'webSelf']);
Route::post('web/other', [BuyAirtimeController::class, 'webOther']);
Route::post('feedback', [FeedbackController::class, 'index']);
