<?php

namespace App\Contracts;

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface SocialAccountService
{
    /**
     * Initiate the OAuth flow for connecting an account.
     */
    public function initiateOAuth(User $user): RedirectResponse;

    /**
     * Handle the OAuth callback and create/update the account.
     */
    public function handleCallback(Request $request, User $user): Account;

    /**
     * Refresh the access token if needed.
     */
    public function refreshToken(Account $account): void;

    /**
     * Post content to the social media platform.
     *
     * @param  array<string>  $media
     */
    public function post(Account $account, string $content, array $media = []): mixed;

    /**
     * Verify that the account credentials are still valid.
     */
    public function verifyCredentials(Account $account): bool;

    /**
     * Revoke access and disconnect the account.
     */
    public function disconnect(Account $account): void;
}
