<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SocialLoginController;

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


Route::group(['prefix' => '/auth', 'namespace' => 'Auth', ['middleware' => 'throttle:20,5']], function () {

    Route::get('/register', 'Auth\RegisterController@register');
    Route::get('/login', 'Auth\LoginController@login');

    Route::get('/login/{service}', 'SocialLoginController@redirect');
    Route::get('/login/{service}/callback', 'SocialLoginController@callback');

    //Route::get('/login/{service}', [SocialLoginController::class, 'redirect']);
   // Route::get('/login/{service}/callback', [SocialLoginController::class, 'callback']);
});

Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('/me', 'MeController@index');

    Route::get('/auth/logout', 'MeController@logout');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
