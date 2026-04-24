<x-layout>
    <div class="py-8 max-w-4xl mx-auto">
        <div class="flex items-center justify-between">
            <a href="{{ route('idea.index') }}" class="text-sm gap-x-2 flex items-center font-medium">
                <x-icons.arrow-back />
                Back to Ideas
            </a>

            <div class="flex items-center gap-x-3">
                <button type="button" class="btn btn-outlined text-sky-500">
                    <x-icons.external />
                    Edit
                </button>

                <form action="{{ route('idea.destroy', $idea) }}" method="POST">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-outlined text-red-500">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-8 space-y-6">
            <h1 class="text-4xl font-bold">{{ $idea->title }}</h1>

            <div class="mt-2 flex items-center gap-3">
                <x-idea.status-label :status="$idea->status->value">
                    {{ $idea->status->label() }}
                </x-idea.status-label>

                <div class="text-muted-foreground text-sm">
                    {{ $idea->created_at->diffForHumans() }}
                </div>
            </div>

            <x-card class="mt-6">
                <div class="text-foreground max-w-none">{{ $idea->description }}</div>
            </x-card>

            @if ($idea->steps->count())
                <div>
                    <h3 class="font-bold text-xl mt-6">Actionable Steps</h3>

                    <div class="mt-3 space-y-2">
                        @foreach ($idea->steps as $step)
                            <x-card>
                                <form action="{{ route('step.update', $step) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <div class="flex items-center gap-x-3">
                                        <button type="submit" role="checkbox" aria-checked="{{ $step->completed }}"
                                            class="size-5 flex items-center justify-center rounded-lg text-primary-foreground {{ $step->completed ? 'bg-primary' : 'border border-primary' }}">
                                            &check;
                                        </button>
                                        <span
                                            class="{{ $step->completed ? 'line-through text-muted-foreground' : '' }}">{{ $step->description }}</span>
                                    </div>
                                </form>
                            </x-card>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($idea->links->count())
                <div>
                    <h3 class="font-bold text-xl mt-6">Links</h3>

                    <div class="mt-3 space-y-2">
                        @foreach ($idea->links as $link)
                            <x-card :href="$link" target="_blank"
                                class="text-primary font-medium flex items-center gap-x-2 hover:underline">
                                <x-icons.external />
                                {{ $link }}
                            </x-card>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layout>
