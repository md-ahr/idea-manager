<x-layout>
    <x-form title="Edit your account" description="Need to make a tweak?">
        <form action="/profile" method="POST" class="mt-10 space-y-4">
            @csrf
            @method('PATCH')

            <x-form.field type="text" name="name" label="Name" :value="$user->name" />

            <x-form.field type="email" name="email" label="Email" :value="$user->email" />

            <x-form.field type="password" name="password" label="New Password" />

            <button type="submit" class="btn mt-2 h-10 w-full">Update Account</button>
        </form>
    </x-form>
</x-layout>
