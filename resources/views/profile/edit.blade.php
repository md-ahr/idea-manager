<X-layout>
    <x-form title="Edit Profile" description="Update your profile information.">
        <form action="/profile/edit" method="POST" class="mt-10 space-y-4">
            @csrf
            @method('PATCH')

            <x-form.field type="text" name="name" label="Name" />
            <x-form.field type="email" name="email" label="Email" />
            <x-form.field type="password" name="password" label="Password" />
            <x-form.field type="password" name="password_confirmation" label="Confirm Password" />
            <button type="submit" class="btn mt-2 h-10 w-full">Update Profile</button>
        </form>
    </x-form>
</X-layout>
