<?php

use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\FoodMethodController;
use App\Http\Controllers\API\FoodCategoryController;
use App\Http\Controllers\API\VideoController;
use App\Http\Controllers\API\FavouriteController;
use App\Http\Controllers\API\FamiliarController;
use App\Http\Controllers\API\NotificationController;
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


Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::get('countries', [CountryController::class, 'list']);
Route::post('username/check', [UserController::class, 'check_username']);
Route::post('forgot/password', [UserController::class,'forgot_password']);
Route::get('familiar/list', [FamiliarController::class, 'familier_list']);
Route::post('otp/send', [UserController::class, 'send_otp']);
Route::post('otp/verify', [UserController::class, 'verify_otp']);
Route::get('app/version', [UserController::class, 'appVersion']);

Route::group(['middleware' => ['apilogs','auth:api']], function () {
    // profile
    Route::get('profile', [UserController::class, 'me']);
    Route::post('profile/edit', [UserController::class, 'edit_profile']);
    Route::post('change/password', [UserController::class, 'change_password']);
    Route::get('chefs/detail', [UserController::class, 'chefs_detail']);
    Route::post('social/register', [UserController::class, 'social_register']);
    Route::post('profile/edit/image', [UserController::class, 'edit_profile_image']);

    //food Methods
    Route::get('food/method/list', [FoodMethodController::class, 'food_methods_list']);

    //food category
    Route::get('food/categories/list', [FoodCategoryController::class, 'food_categories_list']);

    //video
    Route::post('video/add', [VideoController::class, 'add_video']);
    Route::get('videos/list', [VideoController::class, 'videos_list']);
    Route::get('video/detail', [VideoController::class, 'video_detail']);
    Route::post('video/like', [VideoController::class, 'video_like']);
    Route::post('video/comment', [VideoController::class, 'video_comment']);
    Route::get('video/commnet/list', [VideoController::class, 'get_commnet']);
    Route::get('videos/get', [VideoController::class, 'videos_get']);
    Route::get('videos/list/learner', [VideoController::class, 'videos_list_learner']);
    Route::post('video/view', [VideoController::class, 'video_view']);

    //learner home
    Route::get('home', [VideoController::class, 'home']);
    Route::post('chef/subscribe', [UserController::class, 'subscribe']);

    //favourites
    Route::post('favourite/add', [FavouriteController::class, 'add_favoirite']);
    Route::post('favourite/remove', [FavouriteController::class, 'remove_favourite']);
    Route::get('favourite/list', [FavouriteController::class, 'favourite_list']);

   //notification
   Route::get('notifications', [NotificationController::class,'notifications']);
   Route::get('read/notifications', [NotificationController::class,'read_notifications']);
   
    //logout
    Route::get('logout', [UserController::class, 'logout']);
});

Route::get('logout/all', [UserController::class, 'allLogout']);
