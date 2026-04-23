<x-layout>
    <x-form title="Register an account" description="Start tracking your ideas today.">
        <form action="/register" method="POST" class="mt-10 space-y-4">
            @csrf

            <x-form.field type="text" name="name" label="Name" />

            <x-form.field type="email" name="email" label="Email" />

            <x-form.field type="password" name="password" label="Password" />

            <button type="submit" class="btn mt-2 h-10 w-full">Create Account</button>
        </form>
    </x-form>
</x-layout>
