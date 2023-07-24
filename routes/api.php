<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

Route::group(['middleware' => 'throttle:100,1'], function() {
  Route::post('register', [AuthController::class, 'register']);
  Route::post('login', [AuthController::class, 'login']);
  Route::post('test', [AuthController::class, 'me']);
  Route::post('candidates', [AuthController::class, 'createCandidate']);
  Route::get('candidates', [AuthController::class, 'getCandidates']);
  Route::get('candidates/{id}', [AuthController::class, 'getCandidatesBySearch']);
});

Route::get('/user', function (Request $request) {
  Route::get('/user', [UserController::class, 'index']);

    dd("Testing");
    return $request->user();
});
