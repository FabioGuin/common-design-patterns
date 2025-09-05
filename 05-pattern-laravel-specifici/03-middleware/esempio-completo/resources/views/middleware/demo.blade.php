@extends('layouts.app')

@section('title', 'Middleware Pattern Demo')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="fas fa-shield-alt me-2"></i>
                Middleware Pattern Demo
                <span class="badge pattern-badge text-white ms-2">Laravel</span>
            </h1>
            <div>
                <a href="{{ route('middleware.demo.test') }}" class="btn btn-info">
                    <i class="fas fa-flask me-1"></i> Test Middleware
                </a>
                <a href="{{ route('test-middleware') }}" class="btn btn-success" target="_blank">
                    <i class="fas fa-check me-1"></i> Verifica Registrazione
                </a>
            </div>
        </div>

        <!-- Introduzione -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Cos'è il Middleware Pattern?
                </h5>
            </div>
            <div class="card-body">
                <p class="lead">
                    Il Middleware Pattern permette di eseguire codice prima e dopo l'elaborazione di una richiesta HTTP. 
                    In Laravel, i middleware forniscono un meccanismo potente per gestire autenticazione, autorizzazione, 
                    logging, caching e altre funzionalità trasversali.
                </p>
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-check-circle text-success me-2"></i>Vantaggi:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Separazione delle responsabilità</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Codice riutilizzabile</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Pipeline di elaborazione</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Gestione centralizzata</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-cogs text-info me-2"></i>Caratteristiche:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Pre e post processing</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Terminazione anticipata</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Parametri configurabili</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Gruppi e alias</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Middleware Implementati -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card auth-card middleware-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-shield me-2"></i>
                            AuthMiddleware
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Funzionalità:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Controllo autenticazione</li>
                            <li><i class="fas fa-check text-success me-2"></i>Redirect per non autenticati</li>
                            <li><i class="fas fa-check text-success me-2"></i>Supporto API e Web</li>
                            <li><i class="fas fa-check text-success me-2"></i>Logging accessi</li>
                        </ul>
                        <h6>Utilizzo:</h6>
                        <div class="code-example p-2 rounded">
                            <code>Route::middleware('auth')->group(...)</code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card role-card middleware-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-tag me-2"></i>
                            RoleMiddleware
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Funzionalità:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Controllo autorizzazione</li>
                            <li><i class="fas fa-check text-success me-2"></i>Supporto ruoli multipli</li>
                            <li><i class="fas fa-check text-success me-2"></i>Gestione errori</li>
                            <li><i class="fas fa-check text-success me-2"></i>Logging tentativi</li>
                        </ul>
                        <h6>Utilizzo:</h6>
                        <div class="code-example p-2 rounded">
                            <code>Route::middleware('role:admin,editor')->group(...)</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card cache-card middleware-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-database me-2"></i>
                            CacheMiddleware
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Funzionalità:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Caching risposte HTTP</li>
                            <li><i class="fas fa-check text-success me-2"></i>TTL configurabile</li>
                            <li><i class="fas fa-check text-success me-2"></i>Invalidazione automatica</li>
                            <li><i class="fas fa-check text-success me-2"></i>Header informativi</li>
                        </ul>
                        <h6>Utilizzo:</h6>
                        <div class="code-example p-2 rounded">
                            <code>Route::middleware('cache:300')->group(...)</code>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card log-card middleware-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            LogMiddleware
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Funzionalità:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>Logging richieste HTTP</li>
                            <li><i class="fas fa-check text-success me-2"></i>Metriche performance</li>
                            <li><i class="fas fa-check text-success me-2"></i>Filtri sensibili</li>
                            <li><i class="fas fa-check text-success me-2"></i>Alert per richieste lente</li>
                        </ul>
                        <h6>Utilizzo:</h6>
                        <div class="code-example p-2 rounded">
                            <code>Route::middleware('log')->group(...)</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Esempi di Codice -->
        <div class="row">
            <div class="col-md-6">
                <div class="card middleware-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-code me-2"></i>
                            Creazione Middleware
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Struttura base:</h6>
                        <div class="code-example p-3 rounded mb-3">
                            <pre><code>class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }
        
        return $next($request);
    }
}</code></pre>
                        </div>
                        <h6>Registrazione:</h6>
                        <div class="code-example p-3 rounded">
                            <pre><code>// Kernel.php
protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\AuthMiddleware::class,
];</code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card middleware-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-magic me-2"></i>
                            Utilizzo nei Controller
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Nel Controller:</h6>
                        <div class="code-example p-3 rounded mb-3">
                            <pre><code>class BlogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:editor')->only(['create', 'store']);
        $this->middleware('cache:300')->only(['index', 'show']);
    }
}</code></pre>
                        </div>
                        <h6>Nelle Route:</h6>
                        <div class="code-example p-3 rounded">
                            <pre><code>Route::middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/admin', [AdminController::class, 'index']);
    });</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Interattivi -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-play me-2"></i>
                    Test Interattivi
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Testa i Middleware:</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('middleware.demo.auth-test') }}" class="btn btn-danger">
                                <i class="fas fa-user-shield me-1"></i> Test Auth
                            </a>
                            <a href="{{ route('middleware.demo.role-test') }}" class="btn btn-warning">
                                <i class="fas fa-user-tag me-1"></i> Test Role
                            </a>
                            <a href="{{ route('middleware.demo.cache-test') }}" class="btn btn-success">
                                <i class="fas fa-database me-1"></i> Test Cache
                            </a>
                            <a href="{{ route('middleware.demo.rate-limit-test') }}" class="btn btn-info">
                                <i class="fas fa-tachometer-alt me-1"></i> Test Rate Limit
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Test di Performance:</h6>
                        <div class="d-grid gap-2">
                            <a href="{{ route('performance-test') }}" class="btn btn-primary" target="_blank">
                                <i class="fas fa-tachometer-alt me-1"></i> Performance Test
                            </a>
                            <a href="{{ route('cache-test') }}" class="btn btn-success" target="_blank">
                                <i class="fas fa-database me-1"></i> Cache Test
                            </a>
                            <a href="{{ route('test-middleware') }}" class="btn btn-info" target="_blank">
                                <i class="fas fa-check me-1"></i> Verifica Registrazione
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pipeline di Middleware -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-project-diagram me-2"></i>
                    Pipeline di Middleware
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="pipeline-flow">
                                <div class="pipeline-step">
                                    <i class="fas fa-arrow-right"></i>
                                    <span>Request</span>
                                </div>
                                <div class="pipeline-arrow">→</div>
                                <div class="pipeline-step">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Auth</span>
                                </div>
                                <div class="pipeline-arrow">→</div>
                                <div class="pipeline-step">
                                    <i class="fas fa-user-tag"></i>
                                    <span>Role</span>
                                </div>
                                <div class="pipeline-arrow">→</div>
                                <div class="pipeline-step">
                                    <i class="fas fa-database"></i>
                                    <span>Cache</span>
                                </div>
                                <div class="pipeline-arrow">→</div>
                                <div class="pipeline-step">
                                    <i class="fas fa-cogs"></i>
                                    <span>Controller</span>
                                </div>
                                <div class="pipeline-arrow">→</div>
                                <div class="pipeline-step">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>Response</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pipeline-flow {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.pipeline-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px;
    background: #f8f9fa;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    min-width: 80px;
}

.pipeline-step i {
    font-size: 1.5rem;
    margin-bottom: 5px;
}

.pipeline-arrow {
    font-size: 1.5rem;
    color: #6c757d;
}

@media (max-width: 768px) {
    .pipeline-flow {
        flex-direction: column;
    }
    
    .pipeline-arrow {
        transform: rotate(90deg);
    }
}
</style>
@endsection
