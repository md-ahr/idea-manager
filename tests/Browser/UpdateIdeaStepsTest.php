<?php

declare(strict_types=1);

use App\Models\Idea;
use App\Models\User;

it('persists actionable steps on update', function () {
    $user = User::factory()->create();
    $idea = Idea::factory()->for($user)->create();

    $this->actingAs($user)
        ->patch(route('idea.update', $idea), [
            'title' => $idea->title,
            'description' => (string) $idea->description,
            'status' => $idea->status->value,
            'links' => $idea->links ? iterator_to_array($idea->links) : [],
            'steps' => [
                ['description' => 'First step', 'completed' => false],
                ['description' => 'Second step', 'completed' => '1'],
            ],
        ])
        ->assertRedirect();

    $idea->refresh();

    expect($idea->steps)->toHaveCount(2);
    expect($idea->steps->pluck('description')->all())->toBe(['First step', 'Second step']);
    expect($idea->steps->pluck('completed')->map(fn (bool|int $c): bool => (bool) $c)->all())->toBe([false, true]);
});
