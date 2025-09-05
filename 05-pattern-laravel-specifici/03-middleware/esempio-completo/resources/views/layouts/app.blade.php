<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Middleware Pattern') - Laravel Patterns</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .middleware-card {
            transition: transform 0.2s;
        }
        .middleware-card:hover {
            transform: translateY(-2px);
        }
        .pattern-badge {
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
        }
        .code-example {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .auth-card {
            border-left: 4px solid #dc3545;
        }
        .role-card {
            border-left: 4px solid #ffc107;
        }
        .cache-card {
            border-left: 4px solid #28a745;
        }
        .log-card {
            border-left: 4px solid #17a2b8;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('blog.index') }}">
                <i class="fas fa-shield-alt me-2"></i>
                Middleware Pattern
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('blog.index') }}">
                            <i class="fas fa-blog me-1"></i> Blog
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('middleware.demo.index') }}">
                            <i class="fas fa-cogs me-1"></i> Demo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('middleware.demo.test') }}">
                            <i class="fas fa-flask me-1"></i> Test
                        </a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.index') }}">
                                <i class="fas fa-crown me-1"></i> Admin
                            </a>
                        </li>
                    @endauth
                </ul>
                <div class="d-flex">
                    <a href="{{ route('test-middleware') }}" class="btn btn-outline-light me-2" target="_blank">
                        <i class="fas fa-flask me-1"></i> Test Middleware
                    </a>
                    <a href="{{ route('performance-test') }}" class="btn btn-outline-light me-2" target="_blank">
                        <i class="fas fa-tachometer-alt me-1"></i> Performance
                    </a>
                    <a href="{{ route('cache-test') }}" class="btn btn-outline-light" target="_blank">
                        <i class="fas fa-database me-1"></i> Cache Test
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container my-4">
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

        @yield('content')
    </main>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-shield-alt me-2"></i>Middleware Pattern</h5>
                    <p class="mb-0">Esempio completo di utilizzo dei middleware in Laravel per gestire autenticazione, autorizzazione, logging e caching.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <small>
                            <i class="fas fa-code me-1"></i>
                            Laravel {{ app()->version() }} | 
                            <i class="fas fa-calendar me-1"></i>
                            {{ date('Y') }}
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
