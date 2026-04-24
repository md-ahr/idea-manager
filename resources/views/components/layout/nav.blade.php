<nav class="border-b border-border px-6">
    <div class="max-w-7xl mx-auto h-16 flex items-center justify-between">
        <div>
            <a href="/" class="">
                <img src="{{ asset('images/logo.svg') }}" width="100" height="auto" alt="Idea Logo" />
            </a>
        </div>

        <div class="flex items-center gap-x-5">
            @auth
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="btn bg-red-500 text-white hover:bg-red-600 transition-colors">Log
                        Out</button>
                </form>
            @endauth

            @guest
                <a href="/login">Sign in</a>
                <a href="/register" class="btn">Register</a>
            @endguest
        </div>
    </div>
</nav>
