<?php

namespace App\Services;

use App\Enums\SocialService;
use App\Models\Account;
use App\Models\Purge;
use Illuminate\Support\Facades\Log;

class PurgeService
{
    public function __construct(
        protected TwitterAccountService $twitterService
    ) {}

    /**
     * Get the default Twitter account for purging.
     * Looks for account with username 'drewroberts'.
     */
    public function getDefaultAccount(): ?Account
    {
        return Account::where('service', SocialService::TWITTER)
            ->where('username', 'drewroberts')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Process a single purge request.
     */
    public function processPurge(Purge $purge): bool
    {
        // Don't process if already requested or purged
        if ($purge->requested_at || $purge->purged_at) {
            Log::warning('Purge already processed', ['purge_id' => $purge->id]);
            return false;
        }

        // Don't process if marked as saved
        if ($purge->save) {
            Log::info('Purge skipped - marked as saved', ['purge_id' => $purge->id]);
            return false;
        }

        // Get the account to use
        $account = $purge->account ?? $this->getDefaultAccount();

        if (!$account) {
            Log::error('No Twitter account available for purge', ['purge_id' => $purge->id]);
            return false;
        }

        // Mark as requested before attempting
        $purge->update(['requested_at' => now()]);

        try {
            // Attempt to delete the tweet
            $deleted = $this->twitterService->deleteTweet($purge, $account);

            if ($deleted) {
                // Mark as purged
                $purge->update(['purged_at' => now()]);
                
                Log::info('Tweet purged successfully', [
                    'purge_id' => $purge->id,
                    'post_id' => $purge->post_id,
                    'account' => $account->username,
                ]);

                return true;
            }

            Log::warning('Failed to delete tweet', [
                'purge_id' => $purge->id,
                'post_id' => $purge->post_id,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error processing purge', [
                'purge_id' => $purge->id,
                'post_id' => $purge->post_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get the next pending purge to process.
     * Returns the oldest tweet (by posted_at) that needs to be deleted.
     */
    public function getNextPendingPurge(): ?Purge
    {
        return Purge::pending()
            ->orderBy('posted_at', 'asc')
            ->first();
    }

    /**
     * Get statistics about purge progress.
     */
    public function getStats(): array
    {
        return [
            'total' => Purge::count(),
            'pending' => Purge::pending()->count(),
            'requested' => Purge::requested()->count(),
            'purged' => Purge::purged()->count(),
            'saved' => Purge::where('save', true)->count(),
        ];
    }
}
