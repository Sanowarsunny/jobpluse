<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('pages.home');
});
//pages route login
Route::get('/userLogin',[UserController::class,'LoginPage'])->name('login');
Route::get('/logout',[UserController::class,'UserLogout'])->middleware('auth:sanctum');
Route::get('/userProfile',[UserController::class,'Profile']);
Route::get('/userRegistration',[UserController::class,'RegistrationPage']);
Route::get('/sendOTP',[UserController::class,'SendOtpPage']);
Route::get('/verifyOTP',[UserController::class,'VerifyOTPPage']);
Route::get('/resetPassword',[UserController::class,'ResetPasswordPage']);