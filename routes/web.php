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

// Debug Twitter configuration
Route::get('/debug-twitter', function () {
    try {
        $twitterConfig = config('twitter', []);
        
        return response()->json([
            // Basic config check
            'config_exists' => !empty($twitterConfig),
            'config_published' => file_exists(config_path('twitter.php')),
            
            // Environment variables
            'env_consumer_key' => env('TWITTER_CONSUMER_KEY') ? 'SET ('.strlen(env('TWITTER_CONSUMER_KEY')).' chars)' : 'NOT SET',
            'env_consumer_secret' => env('TWITTER_CONSUMER_SECRET') ? 'SET ('.strlen(env('TWITTER_CONSUMER_SECRET')).' chars)' : 'NOT SET',
            'env_api_version' => env('TWITTER_API_VERSION', 'NOT SET'),
            
            // Config values
            'config_consumer_key' => config('twitter.consumer_key') ? 'SET ('.strlen(config('twitter.consumer_key')).' chars)' : 'NOT SET',
            'config_consumer_secret' => config('twitter.consumer_secret') ? 'SET ('.strlen(config('twitter.consumer_secret')).' chars)' : 'NOT SET',
            'config_api_version' => config('twitter.api_version', 'NOT SET'),
            'config_debug' => config('twitter.debug', 'NOT SET'),
            'config_api_url' => config('twitter.api_url', 'NOT SET'),
            
            // Full config (masked sensitive data)
            'full_config' => array_map(function($value) {
                if (is_string($value) && strlen($value) > 10) {
                    return substr($value, 0, 4) . '...' . substr($value, -4);
                }
                return $value;
            }, $twitterConfig),
            
            // Route info
            'callback_url' => route('social.callback', ['service' => 'twitter']),
            'connect_url' => route('social.connect', ['service' => 'twitter']),
            
            // Package info
            'twitter_facade_exists' => class_exists('Atymic\\Twitter\\Facade\\Twitter'),
            'twitter_service_exists' => class_exists('App\\Services\\TwitterAccountService'),
            
            // Laravel config cache status
            'config_cached' => file_exists(base_path('bootstrap/cache/config.php')),
            
            // Test Twitter facade
            'twitter_facade_test' => function_exists('app') ? 'AVAILABLE' : 'NOT AVAILABLE',
            
            // App environment
            'app_env' => app()->environment(),
            'app_debug' => config('app.debug'),
            'app_url' => config('app.url'),
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Exception occurred during debug',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
})->middleware('auth');

// Debug Twitter service initialization
Route::get('/debug-twitter-service', function () {
    try {
        $steps = [];
        
        // Step 1: Check if user is authenticated
        $user = \Illuminate\Support\Facades\Auth::user();
        $steps['auth_check'] = $user ? "User authenticated (ID: {$user->id})" : "User not authenticated";
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated', 'steps' => $steps], 401);
        }
        
        // Step 2: Try to instantiate TwitterAccountService
        try {
            $twitterService = app(\App\Services\TwitterAccountService::class);
            $steps['service_instantiation'] = 'TwitterAccountService created successfully';
        } catch (\Exception $e) {
            $steps['service_instantiation'] = 'FAILED: ' . $e->getMessage();
            return response()->json(['error' => 'Service instantiation failed', 'steps' => $steps], 500);
        }
        
        // Step 3: Try to get callback URL
        try {
            $callbackUrl = route('social.callback', ['service' => 'twitter']);
            $steps['callback_url'] = "Callback URL: {$callbackUrl}";
        } catch (\Exception $e) {
            $steps['callback_url'] = 'FAILED: ' . $e->getMessage();
        }
        
        // Step 4: Try to access Twitter facade
        try {
            $twitter = \Atymic\Twitter\Facade\Twitter::class;
            $steps['twitter_facade'] = 'Twitter facade accessible';
        } catch (\Exception $e) {
            $steps['twitter_facade'] = 'FAILED: ' . $e->getMessage();
        }
        
        // Step 5: Try to call getRequestToken (this is where it likely fails)
        try {
            $token = \Atymic\Twitter\Facade\Twitter::getRequestToken($callbackUrl);
            $steps['request_token'] = $token ? 'Request token obtained successfully' : 'Request token returned null/false';
        } catch (\Exception $e) {
            $steps['request_token'] = 'FAILED: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')';
        }
        
        return response()->json([
            'success' => true,
            'steps' => $steps,
            'message' => 'Twitter service debug completed'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Unexpected error during Twitter service debug',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'steps' => $steps ?? [],
        ], 500);
    }
})->middleware('auth');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    $twoFactorRoute = Route::get('settings/two-factor', TwoFactor::class);

    if (Features::canManageTwoFactorAuthentication()
        && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')) {
        $twoFactorRoute->middleware(['password.confirm']);
    }

    $twoFactorRoute->name('two-factor.show');

    // Social Media Account OAuth Routes
    Route::prefix('social')->name('social.')->group(function () {
        Route::get('{service}/connect', [App\Http\Controllers\SocialAuthController::class, 'connect'])->name('connect');
        Route::get('{service}/callback', [App\Http\Controllers\SocialAuthController::class, 'callback'])->name('callback');
        Route::delete('{service}/accounts/{accountId}', [App\Http\Controllers\SocialAuthController::class, 'disconnect'])->name('disconnect');
    });
});

require __DIR__.'/auth.php';
