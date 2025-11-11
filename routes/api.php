<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ContactSettingController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\OrderOnlineController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PublicRatingController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Models\OrderOnline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('user')->group(function(){
    Route::controller(UserController::class)->group(function(){

        Route::middleware('guest')->group(function(){

            Route::post('/register','register') ;
            Route::post('/checkCode' ,'checkCode') ;
            Route::post('/resendCode','resendCode') ;

            Route::post('/login','login') ;

            Route::post('/forgot_password','forgot_password') ;
            Route::post('/checkRestCode','checkRestCode') ;
            Route::post('/resendResetCode','resendResetCode') ;

            Route::post('/resetPassword','resetPassword') ;

        }) ;



        Route::middleware('auth:sanctum')->group(function(){

            Route::post('/profile','updateProfile');
            Route::get('/show','show');
            Route::post('/logout', 'logout') ;

         }) ;

    }) ;

}) ;
Route::prefix('order')->middleware('auth:sanctum')->group(function(){
    Route::controller(OrderItemController::class)->group(function(){

        Route::post('/store','store') ;

    }) ;

}) ;

Route::get('/meal/get-all-count',[MealController::class ,'getAllMealsCount']) ;
Route::get('/user/get-all-count',[UserController::class ,'getAllUsersCount']) ;

Route::prefix('category')->group(function(){
    Route::controller(CategoryController::class)->group(function(){

        Route::get('/list','index') ;

    }) ;
}) ;

Route::prefix('offer')->group(function(){
    Route::controller(OfferController::class)->group(function(){

        Route::get('/list','index') ;

    }) ;
}) ;

Route::get('/meals/by-rating', [MealController::class, 'getMealsByRating']);

Route::get('/rating', [PublicRatingController::class, 'index']);

Route::get('/meals/{meal}/ratings', [RatingController::class, 'getMealRatings']);

Route::get('/branches', [BranchController::class, 'index']);


Route::prefix('posts')->group(function () {

    Route::get('/news', [PostController::class, 'news']);

    Route::get('/articles', [PostController::class, 'articles']);

    Route::get('/{id}', [PostController::class, 'show']);

}) ;

Route::prefix('contact-settings')->group(function () {
    Route::get('/', [ContactSettingController::class, 'index']);
}) ;

Route::get('/kitchens', [KitchenController::class, 'index']);


Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('complaints')->group(function () {
        Route::post('/', [ComplaintController::class, 'store']);
    }) ;

    Route::post('/meals/{meal}/rate', [RatingController::class, 'storeOrUpdate']);
    // Route::put('meals/rating/update', [RatingController::class, 'storeOrUpdate']);
    Route::post('/rating', [PublicRatingController::class, 'store']);

    Route::get('/cart', [OrderOnlineController::class, 'getCart']);

    Route::delete('/cart/item/{itemId}', [OrderItemController::class, 'removeItem']);

    Route::put('orders/{id}', [OrderOnlineController::class, 'update']);
    Route::put('orders/custom/{id}', [OrderOnlineController::class, 'custom_update']);
    Route::delete('/orders/{id}', [OrderOnlineController::class, 'destroy']);

    Route::get('/my-orders', [OrderOnlineController::class, 'myOrders']);

    Route::get('/meals/{id}', [MealController::class, 'getMealById']);


    Route::prefix('reservations')->group(function () {

        Route::post('/check-availability', [ReservationController::class, 'checkAvailability']);

        // إنشاء حجز جديد
        Route::post('/', [ReservationController::class, 'store']);


        Route::get('/my-reservations', [ReservationController::class, 'getUserReservations']);

        Route::put('/{id}/cancel', [ReservationController::class, 'cancelReservation']);


    }) ;

});



