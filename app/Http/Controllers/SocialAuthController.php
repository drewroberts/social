<?php

namespace App\Http\Controllers;

use App\Enums\SocialService;
use App\Services\TwitterAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends Controller
{
    /**
     * Initiate OAuth flow for a social service.
     */
    public function connect(string $service)
    {
        $user = Auth::user();

        try {
            $serviceEnum = SocialService::from($service);
        } catch (\ValueError $e) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Invalid social service');
        }

        return match($serviceEnum) {
            SocialService::TWITTER => app(TwitterAccountService::class)->initiateOAuth($user),
            SocialService::FACEBOOK => redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Facebook integration coming soon'),
            SocialService::TELEGRAM => redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Telegram integration coming soon'),
        };
    }

    /**
     * Handle OAuth callback from social service.
     */
    public function callback(Request $request, string $service)
    {
        $user = Auth::user();

        // Check for OAuth errors
        if ($request->has('denied') || $request->has('error')) {
            Log::warning('Social OAuth denied', [
                'service' => $service,
                'user_id' => $user->id,
                'denied' => $request->get('denied'),
                'error' => $request->get('error'),
            ]);

            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Authorization was cancelled or denied.');
        }

        try {
            $serviceEnum = SocialService::from($service);
        } catch (\ValueError $e) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Invalid social service');
        }

        try {
            $account = match($serviceEnum) {
                SocialService::TWITTER => app(TwitterAccountService::class)->handleCallback($request, $user),
                SocialService::FACEBOOK => throw new \Exception('Facebook integration coming soon'),
                SocialService::TELEGRAM => throw new \Exception('Telegram integration coming soon'),
            };

            return redirect()->route('filament.admin.pages.dashboard')
                ->with('success', "Successfully connected {$account->username} on {$serviceEnum->label()}!");
        } catch (\Exception $e) {
            Log::error('Social OAuth callback failed', [
                'service' => $service,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Failed to connect account: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect a social account.
     */
    public function disconnect(string $service, int $accountId)
    {
        $user = Auth::user();
        $account = $user->accounts()->findOrFail($accountId);

        try {
            $serviceEnum = SocialService::from($service);
            
            $serviceInstance = match($serviceEnum) {
                SocialService::TWITTER => app(TwitterAccountService::class),
                SocialService::FACEBOOK => throw new \Exception('Facebook integration coming soon'),
                SocialService::TELEGRAM => throw new \Exception('Telegram integration coming soon'),
            };

            $serviceInstance->disconnect($account);

            return redirect()->route('filament.admin.pages.dashboard')
                ->with('success', "Successfully disconnected {$account->username}");
        } catch (\Exception $e) {
            Log::error('Social account disconnect failed', [
                'service' => $service,
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Failed to disconnect account');
        }
    }
}
