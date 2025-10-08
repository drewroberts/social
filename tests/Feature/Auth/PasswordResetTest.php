<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Forgot Password Screen', function () {
    it('renders successfully', function () {
        $this->get('/forgot-password')->assertOk();
    });
});

describe('Password Reset Request', function () {
    it('sends reset link to valid email', function () {
        Notification::fake();

        $user = User::factory()->create();

        Livewire::test(ForgotPassword::class)
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    });
});

describe('Password Reset Screen', function () {
    it('renders with valid token', function () {
        Notification::fake();

        $user = User::factory()->create();

        Livewire::test(ForgotPassword::class)
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) {
            $this->get('/reset-password/'.$notification->token)->assertOk();

            return true;
        });
    });
});

describe('Password Reset', function () {
    it('resets password with valid token', function () {
        Notification::fake();

        $user = User::factory()->create();

        Livewire::test(ForgotPassword::class)
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            Livewire::test(ResetPassword::class, ['token' => $notification->token])
                ->set('email', $user->email)
                ->set('password', 'password')
                ->set('password_confirmation', 'password')
                ->call('resetPassword')
                ->assertHasNoErrors()
                ->assertRedirect(route('login', absolute: false));

            return true;
        });
    });
});