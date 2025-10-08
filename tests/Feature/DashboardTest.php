<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Dashboard Access', function () {
    it('redirects guests to login', function () {
        $this->get('/dashboard')
            ->assertRedirect('/login');
    });

    it('allows authenticated users', function () {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk();
    });
});