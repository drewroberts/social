<?php

use App\Enums\SocialService;
use App\Models\Account;
use App\Models\Purge;
use App\Services\PurgeService;
use App\Services\TwitterAccountService;
use Illuminate\Support\Facades\Log;

describe('PurgeService - Default Account', function () {
    it('finds default account by username', function () {
        $defaultAccount = Account::factory()->create([
            'service' => SocialService::TWITTER,
            'username' => 'drewroberts',
            'is_active' => true,
        ]);

        $otherAccount = Account::factory()->create([
            'service' => SocialService::TWITTER,
            'username' => 'other',
            'is_active' => true,
        ]);

        $service = app(PurgeService::class);
        $found = $service->getDefaultAccount();

        expect($found)
            ->toBeInstanceOf(Account::class)
            ->id->toBe($defaultAccount->id);
    });

    it('returns null when default account does not exist', function () {
        // Create an account with ID=2 (not the default ID=1)
        Account::factory()->create([
            'id' => 2,
            'service' => SocialService::TWITTER,
            'username' => 'other',
            'is_active' => true,
        ]);

        $service = app(PurgeService::class);
        $found = $service->getDefaultAccount();

        expect($found)->toBeNull();
    });

    it('only finds active accounts', function () {
        Account::factory()->create([
            'service' => SocialService::TWITTER,
            'username' => 'drewroberts',
            'is_active' => false,
        ]);

        $service = app(PurgeService::class);
        $found = $service->getDefaultAccount();

        expect($found)->toBeNull();
    });

    it('only finds Twitter accounts', function () {
        Account::factory()->create([
            'service' => SocialService::FACEBOOK,
            'username' => 'drewroberts',
            'is_active' => true,
        ]);

        $service = app(PurgeService::class);
        $found = $service->getDefaultAccount();

        expect($found)->toBeNull();
    });
});

describe('PurgeService - Next Pending Purge', function () {
    it('returns oldest pending purge', function () {
        $oldest = Purge::factory()->create([
            'posted_at' => now()->subDays(10),
            'save' => false,
            'requested_at' => null,
        ]);

        Purge::factory()->create([
            'posted_at' => now()->subDays(5),
            'save' => false,
            'requested_at' => null,
        ]);

        Purge::factory()->create([
            'posted_at' => now()->subDays(1),
            'save' => false,
            'requested_at' => null,
        ]);

        $service = app(PurgeService::class);
        $next = $service->getNextPendingPurge();

        expect($next)
            ->toBeInstanceOf(Purge::class)
            ->id->toBe($oldest->id);
    });

    it('excludes saved purges', function () {
        Purge::factory()->create([
            'posted_at' => now()->subDays(10),
            'save' => true,
            'requested_at' => null,
        ]);

        $expected = Purge::factory()->create([
            'posted_at' => now()->subDays(5),
            'save' => false,
            'requested_at' => null,
        ]);

        $service = app(PurgeService::class);
        $next = $service->getNextPendingPurge();

        expect($next->id)->toBe($expected->id);
    });

    it('excludes already requested purges', function () {
        Purge::factory()->create([
            'posted_at' => now()->subDays(10),
            'save' => false,
            'requested_at' => now()->subHour(),
        ]);

        $expected = Purge::factory()->create([
            'posted_at' => now()->subDays(5),
            'save' => false,
            'requested_at' => null,
        ]);

        $service = app(PurgeService::class);
        $next = $service->getNextPendingPurge();

        expect($next->id)->toBe($expected->id);
    });

    it('returns null when no pending purges exist', function () {
        Purge::factory()->create([
            'save' => true,
            'requested_at' => null,
        ]);

        Purge::factory()->create([
            'save' => false,
            'requested_at' => now(),
        ]);

        $service = app(PurgeService::class);
        $next = $service->getNextPendingPurge();

        expect($next)->toBeNull();
    });
});

describe('PurgeService - Statistics', function () {
    beforeEach(function () {
        Purge::factory()->count(3)->create([
            'save' => false,
            'requested_at' => null,
            'purged_at' => null,
        ]);

        Purge::factory()->count(2)->create([
            'save' => true,
            'requested_at' => null,
            'purged_at' => null,
        ]);

        Purge::factory()->count(4)->create([
            'save' => false,
            'requested_at' => now()->subHour(),
            'purged_at' => null,
        ]);

        Purge::factory()->count(5)->create([
            'save' => false,
            'requested_at' => now()->subDay(),
            'purged_at' => now()->subHours(12),
        ]);
    });

    it('returns correct total count', function () {
        $service = app(PurgeService::class);
        $stats = $service->getStats();

        expect($stats['total'])->toBe(14);
    });

    it('returns correct pending count', function () {
        $service = app(PurgeService::class);
        $stats = $service->getStats();

        expect($stats['pending'])->toBe(3);
    });

    it('returns correct saved count', function () {
        $service = app(PurgeService::class);
        $stats = $service->getStats();

        expect($stats['saved'])->toBe(2);
    });

    it('returns correct requested count', function () {
        $service = app(PurgeService::class);
        $stats = $service->getStats();

        expect($stats['requested'])->toBe(4);
    });

    it('returns correct purged count', function () {
        $service = app(PurgeService::class);
        $stats = $service->getStats();

        expect($stats['purged'])->toBe(5);
    });
});

describe('PurgeService - Process Purge', function () {
    beforeEach(function () {
        $this->account = Account::factory()->create([
            'service' => SocialService::TWITTER,
            'username' => 'drewroberts',
            'is_active' => true,
        ]);
    });

    it('marks purge as requested before attempting deletion', function () {
        $purge = Purge::factory()->create([
            'save' => false,
            'requested_at' => null,
            'purged_at' => null,
        ]);

        $twitterService = Mockery::mock(TwitterAccountService::class);
        $twitterService->shouldReceive('deleteTweet')
            ->once()
            ->andReturn(true);

        $service = new PurgeService($twitterService);
        $service->processPurge($purge);

        expect($purge->fresh()->requested_at)->not->toBeNull();
    });

    it('marks purge as purged on successful deletion', function () {
        $purge = Purge::factory()->create([
            'save' => false,
            'requested_at' => null,
            'purged_at' => null,
        ]);

        $twitterService = Mockery::mock(TwitterAccountService::class);
        $twitterService->shouldReceive('deleteTweet')
            ->once()
            ->andReturn(true);

        $service = new PurgeService($twitterService);
        $result = $service->processPurge($purge);

        expect($result)->toBeTrue()
            ->and($purge->fresh()->purged_at)->not->toBeNull();
    });

    it('returns false for already requested purge', function () {
        $purge = Purge::factory()->create([
            'save' => false,
            'requested_at' => now()->subHour(),
            'purged_at' => null,
        ]);

        $twitterService = Mockery::mock(TwitterAccountService::class);
        $twitterService->shouldNotReceive('deleteTweet');

        Log::shouldReceive('warning')->once();

        $service = new PurgeService($twitterService);
        $result = $service->processPurge($purge);

        expect($result)->toBeFalse();
    });

    it('returns false for already purged record', function () {
        $purge = Purge::factory()->create([
            'save' => false,
            'requested_at' => now()->subHour(),
            'purged_at' => now()->subMinutes(30),
        ]);

        $twitterService = Mockery::mock(TwitterAccountService::class);
        $twitterService->shouldNotReceive('deleteTweet');

        Log::shouldReceive('warning')->once();

        $service = new PurgeService($twitterService);
        $result = $service->processPurge($purge);

        expect($result)->toBeFalse();
    });

    it('skips saved purges', function () {
        $purge = Purge::factory()->create([
            'save' => true,
            'requested_at' => null,
            'purged_at' => null,
        ]);

        $twitterService = Mockery::mock(TwitterAccountService::class);
        $twitterService->shouldNotReceive('deleteTweet');

        Log::shouldReceive('info')->once();

        $service = new PurgeService($twitterService);
        $result = $service->processPurge($purge);

        expect($result)->toBeFalse()
            ->and($purge->fresh()->requested_at)->toBeNull();
    });

    it('uses purge account when available', function () {
        $specificAccount = Account::factory()->create([
            'service' => SocialService::TWITTER,
            'is_active' => true,
        ]);

        $purge = Purge::factory()->create([
            'account_id' => $specificAccount->id,
            'save' => false,
            'requested_at' => null,
        ]);

        $twitterService = Mockery::mock(TwitterAccountService::class);
        $twitterService->shouldReceive('deleteTweet')
            ->once()
            ->andReturn(true);

        $service = new PurgeService($twitterService);
        $result = $service->processPurge($purge);

        expect($result)->toBeTrue();
    });

    it('uses default account when purge has no account', function () {
        $purge = Purge::factory()->create([
            'account_id' => null,
            'save' => false,
            'requested_at' => null,
        ]);

        $twitterService = Mockery::mock(TwitterAccountService::class);
        $twitterService->shouldReceive('deleteTweet')
            ->once()
            ->andReturn(true);

        $service = new PurgeService($twitterService);
        $result = $service->processPurge($purge);

        expect($result)->toBeTrue();
    });

    it('returns false when no account available', function () {
        $purge = Purge::factory()->create([
            'account_id' => null,
            'save' => false,
            'requested_at' => null,
        ]);

        // Delete the default account
        $this->account->delete();

        $twitterService = Mockery::mock(TwitterAccountService::class);
        $twitterService->shouldNotReceive('deleteTweet');

        Log::shouldReceive('error')->once();

        $service = new PurgeService($twitterService);
        $result = $service->processPurge($purge);

        expect($result)->toBeFalse()
            ->and($purge->fresh()->requested_at)->toBeNull(); // Does not mark as requested when no account
    });

    it('handles deletion failure gracefully', function () {
        $purge = Purge::factory()->create([
            'save' => false,
            'requested_at' => null,
            'purged_at' => null,
        ]);

        $twitterService = Mockery::mock(TwitterAccountService::class);
        $twitterService->shouldReceive('deleteTweet')
            ->once()
            ->andReturn(false);

        Log::shouldReceive('warning')->once();

        $service = new PurgeService($twitterService);
        $result = $service->processPurge($purge);

        expect($result)->toBeFalse()
            ->and($purge->fresh()->purged_at)->toBeNull();
    });

    it('handles exceptions during deletion', function () {
        $purge = Purge::factory()->create([
            'save' => false,
            'requested_at' => null,
            'purged_at' => null,
        ]);

        $twitterService = Mockery::mock(TwitterAccountService::class);
        $twitterService->shouldReceive('deleteTweet')
            ->once()
            ->andThrow(new Exception('API Error'));

        Log::shouldReceive('error')->once();

        $service = new PurgeService($twitterService);
        $result = $service->processPurge($purge);

        expect($result)->toBeFalse();
    });
});
