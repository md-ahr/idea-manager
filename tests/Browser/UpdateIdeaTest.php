<?php

use App\Models\Idea;
use App\Models\User;

it('shows the initial input state', function () {
    $this->actingAs($user = User::factory()->create());

    $idea = Idea::factory()->for($user)->create();

    visit(route('idea.show', $idea))
        ->click('@edit-idea-button')
        ->assertValue('title', $idea->title)
        ->assertValue('description', $idea->description)
        ->assertValue('status', $idea->status->value);
});

it('edits an existing idea', function () {
    $this->actingAs($user = User::factory()->create());

    $idea = Idea::factory()->for($user)->create();

    visit(route('idea.show', $idea))
        ->click('@edit-idea-button')
        ->fill('title', 'Updated idea')
        ->click('@button-status-completed')
        ->fill('description', 'Updated description')
        ->fill('new-link', 'https://example.com')
        ->click('@submit-new-link-button')
        ->fill('@new-step', 'Updated step 1')
        ->click('@submit-new-step-button')
        ->click('Update')
        ->assertRoute('idea.show', [$idea]);

    expect($idea = $user->ideas()->first())->toMatchArray([
        'title' => 'Updated idea',
        'status' => 'completed',
        'description' => 'Updated description',
        'links' => [$idea->links[0], 'https://example.com'],
    ]);

    expect($idea->steps)->toHaveCount(1);
});
