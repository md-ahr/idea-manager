<?php

use App\Models\Idea;
use App\Models\User;

it('require authentication', function () {
    $idea = Idea::factory()->create();

    $this->get(route('idea.show', $idea))->assertRedirect(route('login'));
});

it('disallows accessing an idea you did not create', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $idea = Idea::factory()->create();

    $this->get(route('idea.show', $idea))->assertForbidden();
});

it('shows an idea', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $idea = Idea::factory()->create(['user_id' => $user->id]);

    $this->get(route('idea.show', $idea))->assertSee($idea->title);
});
