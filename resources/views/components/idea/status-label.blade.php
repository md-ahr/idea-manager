@use('App\IdeaStatus')

@props(['status' => IdeaStatus::PENDING->value])
@php
    $classes = 'inline-block rounded-full border px-2 py-1 text-xs font-medium';

    if ($status === IdeaStatus::PENDING->value) {
        $classes .= 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20';
    }

    if ($status === IdeaStatus::IN_PROGRESS->value) {
        $classes .= 'bg-blue-500/10 text-blue-500 border-blue-500/20';
    }

    if ($status === IdeaStatus::COMPLETED->value) {
        $classes .= 'bg-primary/10 text-primary border-primary/20';
    }
@endphp

<span {{ $attributes(['class' => $classes]) }}>
    {{ $slot }}
</span>
