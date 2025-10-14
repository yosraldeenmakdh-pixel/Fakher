<?php

use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('user')->middleware('guest')->group(function(){
    Route::controller(UserController::class)->group(function(){
        Route::post('/register','register') ;
        Route::post('/checkCode' ,'checkCode') ;
        Route::post('/resendCode','resendCode') ;

        Route::post('/login','login') ;

        Route::post('/forgot_password','forgot_password') ;
        Route::post('/checkRestCode','checkRestCode') ;
        Route::post('/resendResetCode','resendResetCode') ;

        Route::post('/resetPassword','resetPassword') ;

    }) ;

}) ;
Route::prefix('order')->middleware('auth:sanctum')->group(function(){
    Route::controller(OrderItemController::class)->group(function(){

        Route::post('/store','store') ;

    }) ;

}) ;
