<?php

use App\Livewire\Auth\Register;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Registration Screen', function () {
    it('renders successfully', function () {
        $this->get('/register')->assertOk();
    });
});

describe('Email Domain Validation', function () {
    it('allows registration with authorized domain', function () {
        Livewire::test(Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@drewroberts.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    });

    it('normalizes email to lowercase', function () {
        Livewire::test(Register::class)
            ->set('name', 'Test User')
            ->set('email', 'Test.User@DrewRoberts.COM')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'test.user@drewroberts.com',
            'name' => 'Test User',
        ]);
    });

    it('denies unauthorized domains', function ($email) {
        Livewire::test(Register::class)
            ->set('name', 'Test User')
            ->set('email', $email)
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertRedirect(route('register.denied'));

        $this->assertGuest();
    })->with([
        'generic domain' => 'test@example.com',
        'gmail' => 'user@gmail.com',
        'yahoo' => 'user@yahoo.com',
        'similar domain' => 'test@drewroberts.org',
    ]);
});

describe('Registration Denied Page', function () {
    it('renders with proper messaging', function () {
        $this->get('/register/denied')
            ->assertOk()
            ->assertSee('Registration Unavailable')
            ->assertSee('Access Restricted')
            ->assertSee('W3RD SOCIAL');
    });
});