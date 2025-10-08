<?php

use App\Exceptions\UnauthorizedEmailDomainException;
use App\Livewire\Auth\Register;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register with allowed email domain', function () {
    $response = Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'test@drewroberts.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('registration is denied for emails outside organization', function () {
    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');
})->throws(UnauthorizedEmailDomainException::class, 'You are outside the organization');

test('registration is denied for gmail addresses', function () {
    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'user@gmail.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');
})->throws(UnauthorizedEmailDomainException::class);

test('registration is denied for yahoo addresses', function () {
    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'user@yahoo.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');
})->throws(UnauthorizedEmailDomainException::class);

test('registration is denied for emails with similar but different domain', function () {
    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'test@drewroberts.org')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');
})->throws(UnauthorizedEmailDomainException::class);

test('registration works with capitalized email and stores as lowercase', function () {
    $response = Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'Test.User@DrewRoberts.COM')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    
    // Verify email is stored in lowercase
    $this->assertDatabaseHas('users', [
        'email' => 'test.user@drewroberts.com',
        'name' => 'Test User',
    ]);
});