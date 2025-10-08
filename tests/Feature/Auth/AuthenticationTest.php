<?php

use App\Livewire\Auth\Login;
use App\Models\User;
use Laravel\Fortify\Features;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Login Screen', function () {
    it('renders successfully', function () {
        $this->get('/login')->assertOk();
    });
});

describe('Authentication', function () {
    it('authenticates users with valid credentials', function () {
        $user = User::factory()->withoutTwoFactor()->create();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    });

    it('rejects invalid passwords', function () {
        $user = User::factory()->create();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    });

    it('redirects to two-factor challenge when enabled', function () {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = User::factory()->create();

        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        Livewire::test('auth.login')
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('two-factor.login'))
            ->assertSessionHas('login.id', $user->id);

        $this->assertGuest();
    });
});

describe('Logout', function () {
    it('logs out authenticated users', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    });
});