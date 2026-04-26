<?php

use Illuminate\Support\Facades\Auth;

it('registers a user', function () {
    visit('/register')
        ->fill('name', 'John Doe')
        ->fill('email', 'john@example.com')
        ->fill('password', 'password')
        ->click('Create Account')
        ->assertRoute('idea.index');

    $this->assertAuthenticated();

    expect(Auth::user())->toMatchArray([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

it('requires a valid email address', function () {
    visit('/register')
        ->fill('name', 'John Doe')
        ->fill('email', 'john.com')
        ->fill('password', 'password')
        ->click('Create Account')
        ->assertPathIs('/register');

    $this->assertGuest();
});
