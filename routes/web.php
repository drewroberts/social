<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('terms', function () {
    return view('notices.terms');
})->name('terms');

Route::get('privacy', function () {
    return view('notices.privacy');
})->name('privacy');

Route::get('register/denied', function () {
    return view('auth.register-denied');
})->name('register.denied');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // Social Media Account OAuth Routes
    Route::prefix('social')->name('social.')->group(function () {
        Route::get('{service}/connect', [App\Http\Controllers\SocialAuthController::class, 'connect'])->name('connect');
        Route::get('{service}/callback', [App\Http\Controllers\SocialAuthController::class, 'callback'])->name('callback');
        Route::delete('{service}/accounts/{accountId}', [App\Http\Controllers\SocialAuthController::class, 'disconnect'])->name('disconnect');
    });
});

require __DIR__.'/auth.php';
