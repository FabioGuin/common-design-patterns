<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        .blade-pattern-badge {
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
        }
        .component-card {
            transition: transform 0.2s;
        }
        .component-card:hover {
            transform: translateY(-2px);
        }
        .code-example {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div id="app">
        @include('partials.header')
        
        <main class="py-4">
            @yield('content')
        </main>
        
        @include('partials.footer')
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
