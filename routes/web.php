<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ChefController;
use App\Http\Controllers\Admin\LearnController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\HomeController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/token/{id}', [HomeController::class, 'accessToken'])->name('authtoken');


if (App::environment('production')) {
    URL::forceScheme('https');
}

Route::get('/privacy-policy',function () {
    return view("admin.privacy-policy");
})->name('privacy'); 
Route::get('/terms',function () {
    return view("admin.terms");
})->name('terms');
Route::get('/', function () {
    return view("front.welcome");
});
Route::post('/contact', [NotificationController::class ,'contact'])->name('contact');

Auth::routes();

Route::get('/home', function () {
    return redirect("/admin");
});

Route::get('/forgot/password', [UserController::class, 'forgot_password'])->name('admin.forgot');
Route::post('/forgot/password/mail', [UserController::class, 'password_mail'])->name('admin.forgot.mail');
Route::post('admin/login', [UserController::class, 'admin_login'])->name('admin.login');

Route::name('admin.')->namespace('Admin')->group(function () {
    Route::group(['prefix' => 'admin', 'middleware' => ['admin.check']], function () {
        Route::get('/', [AdminController::class, 'index'])->name('home');
       
        // users  route
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::get('/password', [UserController::class, 'password'])->name('password');
        Route::post('/password/change', [UserController::class, 'change_password'])->name('password.update');
        Route::post('/profile/update', [UserController::class, 'update_profile'])->name('profile.update');
        Route::get('/users', [UserController::class, 'index'])->name('user');
        Route::get('/users/list', [UserController::class, 'users'])->name('users.list');
        Route::get('/get/user', [UserController::class, 'getUser'])->name('user.get');
        Route::get('/user/status/change', [UserController::class, 'changeStatus'])->name('user.status.change');
        Route::post('/user/store', [UserController::class, 'store'])->name('user.store');

        // app setting
        Route::get('setting', [UserController::class, 'app_setting'])->name('setting');
        Route::post('setting/update', [UserController::class, 'setting_update'])->name('setting.update');

         //chefs route
         Route::get('/chef', [ChefController::class, 'index'])->name('chef');
         Route::get('/chef/get', [ChefController::class, 'getChef'])->name('chef.get');
         Route::post('/chef/store', [ChefController::class, 'store'])->name('chef.store');
         Route::post('/chef/delete', [ChefController::class, 'delete'])->name('chef.delete');
         Route::get('/chef/list', [ChefController::class, 'chefs'])->name('chef.list');
         Route::get('/chef/status/change', [ChefController::class, 'changeStatus'])->name('chef.status.change');

          //learner route
          Route::get('/learner', [LearnController::class, 'index'])->name('learner');
          Route::get('/learner/get', [LearnController::class, 'getLearner'])->name('learner.get');
          Route::post('/learner/store', [LearnController::class, 'store'])->name('learner.store');
          Route::post('/learner/delete', [LearnController::class, 'delete'])->name('learner.delete');
          Route::get('/learner/list', [LearnController::class, 'learners'])->name('learner.list');
          Route::get('/learner/status/change', [LearnController::class, 'changeStatus'])->name('learner.status.change');

           //videos route
         Route::get('/video', [VideoController::class, 'index'])->name('video');
         Route::get('/video/get', [VideoController::class, 'getVideo'])->name('video.get');
         Route::post('/video/store', [VideoController::class, 'store'])->name('video.store');
         Route::post('/video/delete', [VideoController::class, 'delete'])->name('video.delete');
         Route::get('/video/list', [VideoController::class, 'videos'])->name('video.list');
         Route::get('/video/status/change', [VideoController::class, 'changeStatus'])->name('video.status.change');

         //notification
         Route::get('notification', [NotificationController::class, 'app_notification'])->name('notification');
         Route::post('notification/send', [ NotificationController::class ,'send_notification'])->name('notification.send');

         
    });
});

Route::get('logout', [LoginController::class, 'logout'])->name('logout');
