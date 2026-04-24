<x-layout>
    <div>
        <header class="py-8 md:py-12">
            <h1 class="text-3xl font-bold">Ideas</h1>
            <p class="text-muted-foreground text-sm mt-2">Capture your thoughts. Make a plan.</p>

            <x-card x-data @click="$dispatch('open-modal', 'create-idea')" is="button" type="button"
                data-test="create-idea-button" class="mt-10 space-y-3 cursor-pointer h-32 w-full text-left">
                What's the idea?
            </x-card>
        </header>

        <div class="flex items-center gap-x-2">
            <a href="/ideas" class="btn {{ request()->has('status') ? 'btn-outlined' : '' }}">All
                <span
                    class="text-xs pl-1 {{ request()->has('status') ? 'text-muted-foreground' : 'text-black' }}">({{ $statusCounts->get('all') }})</span></a>

            @foreach (App\IdeaStatus::cases() as $status)
                <a href="/ideas?status={{ $status->value }}"
                    class="btn {{ request()->status === $status->value ? '' : 'btn-outlined' }}">
                    {{ $status->label() }} <span
                        class="text-xs pl-1 {{ request()->status === $status->value ? 'text-black' : 'text-muted-foreground' }}">({{ $statusCounts->get($status->value) }})</span>
                </a>
            @endforeach
        </div>

        <div class="mt-10 text-muted-foreground">
            <div class="grid md:grid-cols-2 gap-6">
                @forelse ($ideas as $idea)
                    <x-card href="{{ route('idea.show', $idea) }}">
                        @if ($idea->image_path)
                            <div class="mb-4 -mx-4 -mt-4 rounded-t-lg overflow-hidden">
                                <img src="{{ asset('storage/' . $idea->image_path) }}" alt="{{ $idea->title }}"
                                    class="w-full h-48 object-cover">
                            </div>
                        @endif

                        <h3 class="text-lg text-foreground">{{ $idea->title }}</h3>

                        <div class="mt-2">
                            <x-idea.status-label status="{{ $idea->status }}">
                                {{ $idea->status->label() }}
                            </x-idea.status-label>
                        </div>

                        <div class="mt-5 line-clamp-3">{{ $idea->description }}</div>
                        <div class="mt-4">{{ $idea->created_at->diffForHumans() }}</div>
                    </x-card>
                @empty
                    <x-card>
                        <p>No ideas at this time.</p>
                    </x-card>
                @endforelse
            </div>
        </div>

        <x-modal name="create-idea" title="New idea">
            <form x-data="{
                status: @js(old('status', 'pending')),
                newLink: '',
                links: @js(array_values(array_filter(old('links', [])))),
                newStep: '',
                steps: @js(array_values(array_filter(old('steps', [])))),
                wantsImage: @json($errors->has('image')),
            }" action="{{ route('idea.store') }}" method="POST"
                x-bind:enctype="wantsImage ? 'multipart/form-data' : 'application/x-www-form-urlencoded'">
                @csrf

                <div class="space-y-4">
                    @if ($errors->any())
                        <div class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-destructive"
                            role="alert">
                            <p class="font-medium">Could not save this idea.</p>
                            <ul class="mt-2 list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <x-form.field type="text" name="title" label="Title"
                        placeholder="Enter an idea for your title" autofocus required />

                    <div class="space-y-2">
                        <label for="status" class="label">Status</label>

                        <div class="flex gap-x-3">
                            @foreach (App\IdeaStatus::cases() as $status)
                                <button type="button" @click="status = @js($status->value)"
                                    data-test="button-status-{{ $status->value }}" class="btn flex-1 h-10"
                                    :class="{ 'btn-outlined': status !== @js($status->value) }">
                                    {{ $status->label() }}
                                </button>
                            @endforeach

                            <input type="hidden" name="status" x-model="status" />
                        </div>

                        <x-form.error name="status" />
                    </div>

                    <x-form.field type="textarea" name="description" label="Description"
                        placeholder="Describe your idea..." />

                    <div class="space-y-2">
                        <template x-if="! wantsImage">
                            <button type="button" @click="wantsImage = true"
                                class="btn btn-outlined w-full sm:w-auto text-sm">
                                Add featured image (optional)
                            </button>
                        </template>
                        <template x-if="wantsImage">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <label for="image" class="label mb-0">Featured Image</label>
                                    <button type="button" @click="wantsImage = false"
                                        class="text-sm text-muted-foreground hover:text-foreground">
                                        Remove
                                    </button>
                                </div>
                                <input type="file" name="image" id="image" accept="image/*" />
                                <x-form.error name="image" />
                            </div>
                        </template>
                    </div>

                    <div>
                        <fieldset class="space-y-3">
                            <legend class="label">Actionable Steps</legend>

                            <template x-for="(step, index) in steps" :key="step">
                                <div class="flex gap-x-2 items-center">
                                    <input type="text" name="steps[]" x-model="step"
                                        class="input pointer-events-none" readonly />
                                    <button type="button" @click="steps.splice(index, 1)" aria-label="Remove step"
                                        class="form-muted-icon">
                                        <x-icons.close />
                                    </button>
                                </div>
                            </template>

                            <div class="flex gap-x-2 items-center">
                                <input x-model="newStep" type="text" id="new-step"
                                    placeholder="What needs to be done?" class="input flex-1" spellcheck="false"
                                    data-test="new-step" />

                                <button type="button" @click="steps.push(newStep.trim()); newStep = ''"
                                    :disabled="newStep.trim().length === 0" aria-label="Add a new step"
                                    class="form-muted-icon" data-test="submit-new-step-button">
                                    <x-icons.close class="rotate-45" />
                                </button>
                            </div>
                        </fieldset>
                    </div>

                    <div>
                        <fieldset class="space-y-3">
                            <legend class="label">Links</legend>

                            <template x-for="(link, index) in links" :key="link">
                                <div class="flex gap-x-2 items-center">
                                    <input type="text" name="links[]" x-model="link"
                                        class="input pointer-events-none" readonly />
                                    <button type="button" @click="links.splice(index, 1)" aria-label="Remove link"
                                        class="form-muted-icon">
                                        <x-icons.close />
                                    </button>
                                </div>
                            </template>

                            <div class="flex gap-x-2 items-center">
                                <input x-model="newLink" type="url" id="new-link"
                                    placeholder="https://example.com" autocomplete="url" class="input flex-1"
                                    spellcheck="false" data-test="new-link" />

                                <button type="button" @click="links.push(newLink.trim()); newLink = ''"
                                    :disabled="newLink.trim().length === 0" aria-label="Add a new link"
                                    class="form-muted-icon" data-test="submit-new-link-button">
                                    <x-icons.close class="rotate-45" />
                                </button>
                            </div>
                        </fieldset>
                    </div>

                    <div class="flex justify-end gap-x-5">
                        <button type="button" @click="$dispatch('close-modal')"
                            class="btn btn-outlined">Cancel</button>
                        <button type="submit" class="btn">Create</button>
                    </div>
                </div>
            </form>
        </x-modal>

        @if ($errors->any())
            <div x-data x-init="$dispatch('open-modal', 'create-idea')"></div>
        @endif
    </div>
</x-layout>
