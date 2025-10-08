<?php

use App\Enums\SocialService;
use App\Models\Account;
use App\Models\User;

describe('Social OAuth Routes', function () {
    it('requires authentication to connect', function () {
        $this->get(route('social.connect', ['service' => 'twitter']))
            ->assertRedirect();
    });

    it('rejects invalid service types', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('social.connect', ['service' => 'invalid']))
            ->assertRedirect(route('filament.admin.pages.dashboard'))
            ->assertSessionHas('error', 'Invalid social service');
    });
});

describe('Account Relationships', function () {
    it('loads user accounts', function () {
        $user = User::factory()->create();

        Account::factory()->create([
            'user_id' => $user->id,
            'service' => SocialService::TWITTER,
            'username' => 'testuser',
            'is_active' => true,
        ]);

        expect($user->accounts)->toHaveCount(1);
        expect($user->accounts->first()->username)->toBe('testuser');
    });

    it('filters active accounts', function () {
        $user = User::factory()->create();

        Account::factory()->create([
            'user_id' => $user->id,
            'service' => SocialService::TWITTER,
            'username' => 'active',
            'is_active' => true,
        ]);

        Account::factory()->create([
            'user_id' => $user->id,
            'service' => SocialService::FACEBOOK,
            'username' => 'inactive',
            'is_active' => false,
        ]);

        expect($user->accounts)->toHaveCount(2);
        expect($user->activeAccounts)->toHaveCount(1);
        expect($user->activeAccounts->first()->username)->toBe('active');
    });
});

describe('Token Expiration', function () {
    it('detects expired tokens', function () {
        $account = Account::factory()->create([
            'token_expires_at' => now()->subDay(),
        ]);

        expect($account->isTokenExpired())->toBeTrue();

        $account->token_expires_at = now()->addDay();
        $account->save();

        expect($account->isTokenExpired())->toBeFalse();
    });

    it('identifies tokens needing refresh', function () {
        $account = Account::factory()->create([
            'token_expires_at' => now()->addHours(12),
        ]);

        expect($account->needsTokenRefresh())->toBeTrue();

        $account->token_expires_at = now()->addDays(2);
        $account->save();
        $account->refresh();

        expect($account->needsTokenRefresh())->toBeFalse();
    });
});

describe('Account Display Name', function () {
    it('returns username when available', function () {
        $account = Account::factory()->create([
            'username' => 'testuser',
        ]);

        expect($account->displayName)->toBe('testuser');
    });

    it('falls back to service user id', function () {
        $account = Account::factory()->create([
            'username' => null,
            'service_user_id' => '12345',
        ]);

        expect($account->displayName)->toBe('12345');
    });
});
