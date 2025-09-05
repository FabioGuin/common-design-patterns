<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Form Request Demo')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Form Request Pattern Demo</h1>
            <p class="text-gray-600">Sistema di validazione e autorizzazione con Laravel Form Request</p>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="mt-12 text-center text-gray-500">
            <p>Esempio completo del Form Request Pattern in Laravel</p>
        </footer>
    </div>

    @yield('scripts')
</body>
</html>
