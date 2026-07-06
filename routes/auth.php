<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Here are all the authentication related routes including login, register,
| password reset, email verification, and logout functionality.
|
*/

// Guest routes (login, register, password reset)
Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'auth::login')->name('login');
    Route::livewire('/register', 'auth::register')->name('register');
    Route::livewire('/password/reset', 'auth::password-reset-email')->name('password.request');
    Route::livewire('/password/reset/{token}', 'auth::password-reset')->name('password.reset');

    Route::get('auth/{provider}/redirect', [\App\Http\Controllers\SocialiteController::class, 'loginSocial'])->name('socialite.auth');
    Route::get('auth/{provider}/callback', [\App\Http\Controllers\SocialiteController::class, 'callbackSocial'])->name('socialite.callback');

});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Email verification
    Route::livewire('/email/verify', 'auth::verify')->middleware('throttle:6,1')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', EmailVerificationController::class)->middleware('signed')->name('verification.verify');

    // Password confirmation
    Route::livewire('/password/confirm', 'auth::password-confirm')->name('password.confirm');

    // Logout
    Route::post('/logout', LogoutController::class)->name('logout');
});