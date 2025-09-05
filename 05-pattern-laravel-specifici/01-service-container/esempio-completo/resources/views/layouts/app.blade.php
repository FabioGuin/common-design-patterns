<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Service Container Pattern') - Laravel Patterns</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .service-card {
            transition: transform 0.2s;
        }
        .service-card:hover {
            transform: translateY(-2px);
        }
        .pattern-badge {
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
        }
        .code-example {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('users.index') }}">
                <i class="fas fa-cube me-2"></i>
                Service Container Pattern
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.index') }}">
                            <i class="fas fa-users me-1"></i> Utenti
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('service-container-demo') }}">
                            <i class="fas fa-code me-1"></i> Demo Pattern
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('container-test') }}" target="_blank">
                            <i class="fas fa-flask me-1"></i> Test Container
                        </a>
                    </li>
                </ul>
                <form class="d-flex" action="{{ route('search') }}" method="GET">
                    <input class="form-control me-2" type="search" name="q" placeholder="Cerca..." value="{{ request('q') }}">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
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
                    <h5><i class="fas fa-cube me-2"></i>Service Container Pattern</h5>
                    <p class="mb-0">Esempio pratico di utilizzo del Service Container di Laravel per la gestione delle dipendenze.</p>
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
