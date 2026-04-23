<nav class="border-b border-border px-6">
    <div class="max-w-7xl mx-auto h-16 flex items-center justify-between">
        <div>
            <a href="/" class="">
                <img src="{{ asset('images/logo.svg') }}" alt="Idea Logo" width="100" />
            </a>
        </div>

        <div class="flex items-center gap-x-5">
            @auth
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="btn">Logout</button>
                </form>
            @endauth

            @guest
                <a href="/login">Sign in</a>
                <a href="/register" class="btn">Register</a>
            @endguest
        </div>
    </div>
</nav>
