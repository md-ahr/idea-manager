@props(['idea' => new App\Models\Idea()])

<x-modal name="{{ $idea->exists ? 'edit-idea' : 'create-idea' }}"
    title="{{ $idea->exists ? 'Edit idea' : 'Create idea' }}">
    <form x-data="{
        status: @js(old('status', $idea->status->value)),
        newLink: '',
        links: @js(old('links', $idea->links ?? [])),
        newStep: '',
        steps: @js(old('steps', $idea->steps->map->only(['id', 'description', 'completed']))),
        formEnctype: 'application/x-www-form-urlencoded',
        onImageInput() {
            this.formEnctype = this.$refs.imageField?.files?.length
                ? 'multipart/form-data'
                : 'application/x-www-form-urlencoded'
        },
    }" x-bind:enctype="formEnctype" action="{{ $idea->exists ? route('idea.update', $idea) : route('idea.store') }}"
        method="POST">
        @csrf

        @if ($idea->exists)
            @method('PATCH')
        @endif

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

            <x-form.field type="text" name="title" label="Title" placeholder="Enter an idea for your title"
                :value="$idea->title" autofocus required />

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

            <x-form.field type="textarea" name="description" label="Description" placeholder="Describe your idea..."
                :value="$idea->description" />

            <div class="space-y-2">
                @if ($idea->image_path)
                    <div class="space-y-2">
                        <img src="{{ asset('storage/' . $idea->image_path) }}" alt="{{ $idea->title }}"
                            class="w-full h-48 object-cover rounded-lg" />
                        <button class="btn btn-outlined h-10 w-full" form="delete-image-form">
                            Remove Image
                        </button>
                    </div>
                @endif


                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <label for="image" class="label mb-0">Featured Image</label>
                    </div>
                    <input type="file" name="image" id="image" x-ref="imageField" accept="image/*" @change="onImageInput()" />
                    <x-form.error name="image" />
                </div>

            </div>

            <div>
                <fieldset class="space-y-3">
                    <legend class="label">Actionable Steps</legend>

                    <template x-for="(step, index) in steps" :key="step.id || index">
                        <div class="flex gap-x-2 items-center">
                            <input type="text" :name="`steps[${index}][description]`" x-model="step.description"
                                class="input pointer-events-none" readonly />
                            <input type="hidden" :name="`steps[${index}][completed]`"
                                :value="step.completed ? '1' : '0'" />

                            <button type="button" @click="steps.splice(index, 1)" aria-label="Remove step"
                                class="form-muted-icon">
                                <x-icons.close />
                            </button>
                        </div>
                    </template>

                    <div class="flex gap-x-2 items-center">
                        <input x-model="newStep" type="text" id="new-step" placeholder="What needs to be done?"
                            class="input flex-1" spellcheck="false" data-test="new-step" />

                        <button type="button"
                            @click="steps.push({ description: newStep.trim(), completed: false }); newStep = ''"
                            :disabled="newStep.trim().length === 0" aria-label="Add a new step" class="form-muted-icon"
                            data-test="submit-new-step-button">
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
                            <input type="text" name="links[]" x-model="link" class="input pointer-events-none"
                                readonly />
                            <button type="button" @click="links.splice(index, 1)" aria-label="Remove link"
                                class="form-muted-icon">
                                <x-icons.close />
                            </button>
                        </div>
                    </template>

                    <div class="flex gap-x-2 items-center">
                        <input x-model="newLink" type="url" id="new-link" placeholder="https://example.com"
                            autocomplete="url" class="input flex-1" spellcheck="false" data-test="new-link" />

                        <button type="button" @click="links.push(newLink.trim()); newLink = ''"
                            :disabled="newLink.trim().length === 0" aria-label="Add a new link" class="form-muted-icon"
                            data-test="submit-new-link-button">
                            <x-icons.close class="rotate-45" />
                        </button>
                    </div>
                </fieldset>
            </div>

            <div class="flex justify-end gap-x-5">
                <button type="button" @click="$dispatch('close-modal')" class="btn btn-outlined">Cancel</button>
                <button type="submit" class="btn">{{ $idea->exists ? 'Update' : 'Create' }}</button>
            </div>
        </div>
    </form>

    @if ($idea->image_path)
        <form id="delete-image-form" method="POST" action="{{ route('idea.image.destroy', $idea) }}">
            @csrf
            @method('DELETE')
        </form>
    @endif
</x-modal>
