<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('user')->group(function(){
    Route::controller(UserController::class)->group(function(){
        Route::post('/register','register') ;
        // Route::post('/login','login') ;
        // Route::post('/forgot_password','forgot_password') ;
        // Route::post('/check','check') ;
        // Route::post('/reset_password','reset_password') ;

        // Route::middleware(['auth:sanctum','IsFreelancer'])->group(function(){
        //     Route::post('/activation','activation') ;
        // }) ;
    }) ;

}) ;
