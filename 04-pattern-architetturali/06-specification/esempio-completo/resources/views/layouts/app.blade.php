<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Specification Pattern Example') - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .navbar-brand {
            font-weight: 600;
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .pattern-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        .specification-demo {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div id="app">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ route('products.index') }}">
                    <i class="fas fa-filter text-primary me-2"></i>
                    Specification Pattern
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                                <i class="fas fa-box me-1"></i>
                                Prodotti
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('stats') }}">
                                <i class="fas fa-chart-bar me-1"></i>
                                Statistiche
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('specification-demo') }}">
                                <i class="fas fa-code me-1"></i>
                                Specification Demo
                            </a>
                        </li>
                    </ul>

                    <!-- Search Form -->
                    <form class="d-flex" action="{{ route('search') }}" method="GET">
                        <div class="input-group">
                            <input class="form-control" type="search" name="q" placeholder="Cerca prodotti..." value="{{ request('q') }}">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="py-4">
            <div class="container">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Page Content -->
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="footer mt-5 py-4">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Specification Pattern Example</h5>
                        <p class="text-muted">
                            Esempio completo del pattern Specification implementato con Laravel.
                            Dimostra l'incapsulamento della logica di business in oggetti componibili.
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6>Pattern Implementati</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-1"></i> Specification Pattern</li>
                            <li><i class="fas fa-check text-success me-1"></i> Business Rules</li>
                            <li><i class="fas fa-check text-success me-1"></i> Query Optimization</li>
                            <li><i class="fas fa-check text-success me-1"></i> Composition Logic</li>
                        </ul>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12 text-center">
                        <p class="text-muted mb-0">
                            &copy; {{ date('Y') }} Specification Pattern Example. 
                            <a href="https://laravel.com" target="_blank" class="text-decoration-none">Laravel</a> 
                            powered with ❤️
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Confirm delete actions
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
                if (!confirm('Sei sicuro di voler eliminare questo elemento?')) {
                    e.preventDefault();
                }
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
