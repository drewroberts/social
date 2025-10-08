<?php

use App\Livewire\Settings\Profile;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Profile Settings Page', function () {
    it('renders successfully', function () {
        $this->actingAs(User::factory()->create())
            ->get('/settings/profile')
            ->assertOk();
    });
});

describe('Profile Update', function () {
    it('updates profile information', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();

        expect($user->name)->toBe('Test User');
        expect($user->email)->toBe('test@example.com');
        expect($user->email_verified_at)->toBeNull();
    });

    it('preserves email verification when email unchanged', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        expect($user->refresh()->email_verified_at)->not->toBeNull();
    });
});

describe('Account Deletion', function () {
    it('deletes account with valid password', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('settings.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser')
            ->assertHasNoErrors()
            ->assertRedirect('/');

        expect($user->fresh())->toBeNull();
        $this->assertGuest();
    });

    it('requires correct password to delete', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('settings.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser')
            ->assertHasErrors(['password']);

        expect($user->fresh())->not->toBeNull();
    });
});
