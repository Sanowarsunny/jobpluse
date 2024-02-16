<?php

use App\Http\Controllers\UserController;
use App\Http\Middleware\TokenVerificationMiddleware;
use Illuminate\Support\Facades\Route;

//pages route
Route::get('/userLogin',[UserController::class,'LoginPage']);
Route::get('/userRegistration',[UserController::class,'RegistrationPage']);
Route::get('/sendOTP',[UserController::class,'SendOtpPage']);
Route::get('/verifyOtp',[UserController::class,'VerifyOTPPage']);
Route::get('/resetPassword',[UserController::class,'ResetPasswordPage'])->middleware([TokenVerificationMiddleware::class]);

// User Logout
Route::get('/logout',[UserController::class,'UserLogout']);

//backend
Route::post('/user-Registration',[UserController::class,'userRegistration']);
Route::post('/user-Login',[UserController::class,'userLogin']);
Route::post('/send-OTP',[UserController::class,'sendOTPCode']);
Route::post('/verify-OTP',[UserController::class,'verifyOTP']);
Route::post('/reset-password',[UserController::class,'resetPassword'])->middleware([TokenVerificationMiddleware::class]);

