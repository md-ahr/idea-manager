<?php

use App\Models\User;
use App\Notifications\EmailChanged;
use Illuminate\Support\Facades\Notification;

it('requires authentication', function () {
    $this->get(route('profile.edit'))->assertRedirect('/login');
});

it('edit a profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    visit(route('profile.edit'))
        ->assertValue('name', $user->name)
        ->fill('name', 'John Doe')
        ->assertValue('email', $user->email)
        ->fill('email', 'john@example.com')
        ->click('Update Account')
        ->assertSee('Your profile has been updated.');

    expect($user->fresh())->toMatchArray([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

it('notifies the original email if update', function () {
    $user = User::factory()->create();
    $originalEmail = $user->email;

    $this->actingAs($user);

    Notification::fake();

    visit(route('profile.edit'))
        ->assertValue('email', $user->email)
        ->fill('email', 'john@example.com')
        ->click('Update Account')
        ->assertSee('Your profile has been updated.');

    Notification::assertSentOnDemand(EmailChanged::class, fn (EmailChanged $notification, $channels, $notifiable) => $notifiable->routeNotificationFor('mail') === $originalEmail);
});
