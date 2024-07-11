<?php

use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;


Route::controller(PostController::class)->group(function () {
    Route::group(['prefix' => 'posts'], function(){
        Route::get('/', 'index');
        Route::post('/', 'store');
    });
});

Route::controller(ArticleController::class)->group(function () {
    Route::group(['prefix' => 'articles'], function(){
        Route::get('/', 'index');
        Route::post('/', 'store');

        Route::get('/search', 'search');
    });
});


Route::group([
//    'prefix' => LocalizationService::locale(),
    'middleware' => ['auth:api']
], function() {

    Route::controller(AuthController::class)->group(function () {
        Route::post('register', 'register')->name('register');
        Route::post('login', 'login')->name('login');
        Route::post('logout', 'logout');
        Route::post('refresh', 'refresh');
    });

    Route::controller(ProfileController::class)->group(function () {
        Route::post('forgot-password', 'forgotPassword')->name('password.forgot');
        Route::post('reset-password', 'resetPassword')->name('password.reset');
        Route::post('set-password', 'setPassword')->name('password.set');
        Route::get('set-email', 'setEmail');

        Route::group(['prefix' => 'profile'], function(){
            Route::put('/password', 'updatePassword');
            Route::get('/', 'show');

            Route::delete('/avatar', 'deleteAvatar');
            Route::post('/avatar', 'updateAvatar');
            Route::get('/avatar', 'showAvatar');
        });

    });

});
