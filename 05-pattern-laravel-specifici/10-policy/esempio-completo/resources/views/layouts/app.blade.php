<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Policy Demo')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Policy Pattern Demo</h1>
            <p class="text-gray-600">Sistema di autorizzazione con Laravel Policy</p>
        </header>

        <nav class="mb-8">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('policy.demo') }}" 
                       class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Dashboard
                    </a>
                    <a href="{{ route('posts.index') }}" 
                       class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                        Post
                    </a>
                    <a href="{{ route('comments.index') }}" 
                       class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
                        Commenti
                    </a>
                    <a href="{{ route('users.index') }}" 
                       class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                        Utenti
                    </a>
                    <a href="{{ route('api.posts.index') }}" 
                       class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                        API Posts
                    </a>
                </div>
            </div>
        </nav>

        <main>
            @yield('content')
        </main>

        <footer class="mt-12 text-center text-gray-500">
            <p>Esempio completo del Policy Pattern in Laravel</p>
        </footer>
    </div>

    @yield('scripts')
</body>
</html>
