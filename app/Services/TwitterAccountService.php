<?php

namespace App\Services;

use App\Contracts\SocialAccountService;
use App\Enums\SocialService;
use App\Models\Account;
use App\Models\Purge;
use App\Models\User;
use Atymic\Twitter\Facade\Twitter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class TwitterAccountService implements SocialAccountService
{
    /**
     * Initiate the OAuth flow for connecting a Twitter account.
     */
    public function initiateOAuth(User $user): RedirectResponse
    {
        try {
            $callbackUrl = route('social.callback', ['service' => 'twitter']);
            $token = Twitter::getRequestToken($callbackUrl);

            if (isset($token['oauth_token_secret'])) {
                // Store temporary OAuth data in session
                Session::put('oauth_state', 'start');
                Session::put('oauth_request_token', $token['oauth_token']);
                Session::put('oauth_request_token_secret', $token['oauth_token_secret']);
                Session::put('oauth_user_id', $user->id);

                $url = Twitter::getAuthenticateUrl($token['oauth_token']);

                return redirect()->away($url);
            }

            throw new \Exception('Failed to get request token from Twitter');
        } catch (\Exception $e) {
            Log::error('Twitter OAuth initiation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Failed to connect to Twitter. Please try again.');
        }
    }

    /**
     * Handle the OAuth callback and create/update the account.
     */
    public function handleCallback(Request $request, User $user): Account
    {
        if (! Session::has('oauth_request_token')) {
            throw new \Exception('Invalid OAuth session');
        }

        // Use stored temporary credentials to get access token
        $twitter = Twitter::usingCredentials(
            Session::get('oauth_request_token'),
            Session::get('oauth_request_token_secret')
        );

        $token = $twitter->getAccessToken($request->get('oauth_verifier'));

        if (! isset($token['oauth_token'], $token['oauth_token_secret'])) {
            throw new \Exception('Failed to get access token from Twitter');
        }

        // Get user credentials from Twitter
        $twitter = Twitter::usingCredentials($token['oauth_token'], $token['oauth_token_secret']);
        $credentials = $twitter->getCredentials([
            'include_email' => 'true',
            'skip_status' => 'true',
        ]);

        if (! is_object($credentials) || isset($credentials->error)) {
            throw new \Exception('Failed to get Twitter user credentials');
        }

        // Create or update the account
        $account = Account::updateOrCreate(
            [
                'user_id' => $user->id,
                'service' => SocialService::TWITTER,
                'service_user_id' => $credentials->id_str,
            ],
            [
                'username' => $credentials->screen_name,
                'access_token' => $token['oauth_token'],
                'access_token_secret' => $token['oauth_token_secret'],
                'is_active' => true,
                'metadata' => [
                    'name' => $credentials->name,
                    'profile_image_url' => $credentials->profile_image_url_https ?? null,
                    'followers_count' => $credentials->followers_count ?? 0,
                    'following_count' => $credentials->friends_count ?? 0,
                ],
                'last_synced_at' => now(),
            ]
        );

        // Clear OAuth session data
        Session::forget(['oauth_state', 'oauth_request_token', 'oauth_request_token_secret', 'oauth_user_id']);

        return $account;
    }

    /**
     * Refresh the access token if needed.
     * Note: Twitter OAuth 1.0a tokens don't expire, so this is a no-op.
     */
    public function refreshToken(Account $account): void
    {
        // Twitter OAuth 1.0a tokens don't expire
        // We can verify credentials instead
        $this->verifyCredentials($account);
    }

    /**
     * Post content to Twitter.
     */
    public function post(Account $account, string $content, array $media = []): mixed
    {
        $twitter = Twitter::usingCredentials(
            $account->access_token,
            $account->access_token_secret
        );

        $parameters = [
            'status' => $content,
            'response_format' => 'json',
        ];

        // Handle media uploads if provided
        if (! empty($media)) {
            $mediaIds = [];
            foreach ($media as $mediaPath) {
                $uploadedMedia = $twitter->uploadMedia(['media' => file_get_contents($mediaPath)]);
                if (isset($uploadedMedia->media_id_string)) {
                    $mediaIds[] = $uploadedMedia->media_id_string;
                }
            }

            if (! empty($mediaIds)) {
                $parameters['media_ids'] = implode(',', $mediaIds);
            }
        }

        $response = $twitter->postTweet($parameters);

        // Update last synced timestamp
        $account->update(['last_synced_at' => now()]);

        return $response;
    }

    /**
     * Verify that the account credentials are still valid.
     */
    public function verifyCredentials(Account $account): bool
    {
        try {
            $twitter = Twitter::usingCredentials(
                $account->access_token,
                $account->access_token_secret
            );

            $credentials = $twitter->getCredentials(['skip_status' => 'true']);

            if (is_object($credentials) && ! isset($credentials->error)) {
                // Update metadata with fresh data
                $account->update([
                    'metadata' => [
                        'name' => $credentials->name,
                        'profile_image_url' => $credentials->profile_image_url_https ?? null,
                        'followers_count' => $credentials->followers_count ?? 0,
                        'following_count' => $credentials->friends_count ?? 0,
                    ],
                    'last_synced_at' => now(),
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Twitter credentials verification failed', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete a tweet from Twitter.
     * Returns true if deleted successfully or if tweet doesn't exist (404).
     */
    public function deleteTweet(Purge $purge, Account $account): bool
    {
        try {
            $twitter = Twitter::usingCredentials(
                $account->access_token,
                $account->access_token_secret
            );

            // Attempt to delete the tweet
            $response = $twitter->destroyTweet($purge->post_id);

            // If we get a valid response, the tweet was deleted
            if (is_object($response) && ! isset($response->error)) {
                Log::info('Tweet deleted successfully', [
                    'post_id' => $purge->post_id,
                    'account' => $account->username,
                ]);

                return true;
            }

            // Check if it's an error response
            if (isset($response->error)) {
                Log::warning('Twitter API error during delete', [
                    'post_id' => $purge->post_id,
                    'error' => $response->error,
                ]);
            }

            return false;
        } catch (\Atymic\Twitter\Exception\ClientException $e) {
            // Check if it's a 404 - tweet doesn't exist (already deleted or never existed)
            if ($e->getCode() === 404 || str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'No status found')) {
                Log::info('Tweet not found (404) - marking as purged', [
                    'post_id' => $purge->post_id,
                    'account' => $account->username,
                ]);

                return true;
            }

            Log::error('Twitter API exception during delete', [
                'post_id' => $purge->post_id,
                'account' => $account->username,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Unexpected error deleting tweet', [
                'post_id' => $purge->post_id,
                'account' => $account->username,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Revoke access and disconnect the account.
     */
    public function disconnect(Account $account): void
    {
        // Twitter doesn't have a revoke endpoint in OAuth 1.0a
        // Users must revoke access manually from their Twitter settings
        // We'll just deactivate the account in our system
        $account->update([
            'is_active' => false,
            'access_token' => null,
            'access_token_secret' => null,
        ]);
    }
}
