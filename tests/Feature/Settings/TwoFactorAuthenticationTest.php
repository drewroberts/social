<?php

use App\Models\User;
use Laravel\Fortify\Features;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);
});

describe('Two-Factor Settings Page', function () {
    it('renders when password confirmed', function () {
        $user = User::factory()->withoutTwoFactor()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('two-factor.show'))
            ->assertOk()
            ->assertSee('Two Factor Authentication')
            ->assertSee('Disabled');
    });

    it('requires password confirmation', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('two-factor.show'))
            ->assertRedirect(route('password.confirm'));
    });

    it('returns forbidden when feature disabled', function () {
        config(['fortify.features' => []]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('two-factor.show'))
            ->assertForbidden();
    });
});

describe('Two-Factor Configuration', function () {
    it('clears unconfirmed setup on page load', function () {
        $user = User::factory()->create();

        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->actingAs($user);

        Livewire::test('settings.two-factor')
            ->assertSet('twoFactorEnabled', false);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);
    });
});
