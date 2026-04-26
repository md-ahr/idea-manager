<?php

use App\Models\Idea;
use App\Models\User;

test('it belongs to a user', function () {
    $idea = Idea::factory()->create();

    expect($idea->user)->toBeInstanceOf(User::class);
});

test('it can have steps', function () {
    $idea = Idea::factory()->create();

    expect($idea->steps)->toBeEmpty();

    $idea->steps()->create([
        'description' => 'Step 1',
    ]);

    expect($idea->fresh()->steps)->toHaveCount(1);
});

test('it can format a description using markdown', function () {
    $idea = Idea::factory()->create(['description' => 'Hello *world*']);

    expect($idea->formattedDescription)->toEqual('<p>Hello <em>world</em></p>');
});
