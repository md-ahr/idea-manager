<?php

use App\Models\Idea;
use App\Models\User;

it('creates a new idea', function () {
    $this->actingAs($user = User::factory()->create());

    visit('/ideas')
        ->click('@create-idea-button')
        ->fill('title', 'Test idea')
        ->click('@button-status-completed')
        ->fill('description', 'Test description')
        ->click('Create')
        ->assertPathIs('/ideas');

    expect(Idea::count())->toBe(1);

    expect($user->ideas()->first())->toMatchArray([
        'title' => 'Test idea',
        'status' => 'completed',
        'description' => 'Test description',
    ]);
});
