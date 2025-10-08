<?php

use App\Livewire\Settings\Password;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Password Update', function () {
    it('updates password with valid credentials', function () {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        Livewire::test(Password::class)
            ->set('current_password', 'password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasNoErrors();

        expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
    });

    it('requires correct current password', function () {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        Livewire::test(Password::class)
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);
    });
});