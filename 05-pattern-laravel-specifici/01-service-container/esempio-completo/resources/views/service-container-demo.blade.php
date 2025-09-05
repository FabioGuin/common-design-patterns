@extends('layouts.app')

@section('title', 'Service Container Demo')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="fas fa-cube me-2"></i>
                Service Container Pattern Demo
                <span class="badge pattern-badge text-white ms-2">Laravel</span>
            </h1>
            <a href="{{ route('container-test') }}" class="btn btn-info" target="_blank">
                <i class="fas fa-flask me-1"></i> Test API
            </a>
        </div>

        <!-- Introduzione -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Cos'è il Service Container?
                </h5>
            </div>
            <div class="card-body">
                <p class="lead">
                    Il Service Container di Laravel è un potente strumento per la gestione delle dipendenze 
                    e l'inversione del controllo (IoC). Permette di registrare servizi e risolverli automaticamente.
                </p>
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-check-circle text-success me-2"></i>Vantaggi:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Dependency Injection automatica</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Singleton e istanze condivise</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Binding di interfacce</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Risoluzione automatica</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-cogs text-info me-2"></i>Caratteristiche:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Auto-wiring</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Service Providers</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Contextual binding</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Tagging</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Esempi di utilizzo -->
        <div class="row">
            <div class="col-md-6">
                <div class="card service-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-code me-2"></i>
                            Registrazione Servizi
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Service Provider:</h6>
                        <div class="code-example p-3 rounded mb-3">
                            <pre><code>// BlogServiceProvider.php
public function register()
{
    // Singleton
    $this->app->singleton(UserService::class);
    
    // Binding interfaccia
    $this->app->bind(
        EmailServiceInterface::class,
        EmailService::class
    );
    
    // Closure binding
    $this->app->bind('cache', function ($app) {
        return new CacheService($app['config']);
    });
}</code></pre>
                        </div>
                        <h6>Utilizzo nel Controller:</h6>
                        <div class="code-example p-3 rounded">
                            <pre><code>// UserController.php
public function __construct(
    private UserService $userService,
    private EmailService $emailService,
    private CacheService $cacheService
) {
    // Dependency injection automatica
}</code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card service-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-magic me-2"></i>
                            Risoluzione Servizi
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Metodi di risoluzione:</h6>
                        <div class="code-example p-3 rounded mb-3">
                            <pre><code>// Risoluzione diretta
$userService = app(UserService::class);

// Helper function
$userService = resolve(UserService::class);

// Iniezione automatica
public function index(UserService $userService)
{
    return $userService->getAllUsers();
}</code></pre>
                        </div>
                        <h6>Verifica binding:</h6>
                        <div class="code-example p-3 rounded">
                            <pre><code>// Controllo esistenza
if (app()->bound(UserService::class)) {
    $service = app(UserService::class);
}

// Verifica singleton
$isSingleton = app()->isShared(UserService::class);</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Servizi registrati -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Servizi Registrati nell'Esempio
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-user fa-2x text-primary mb-3"></i>
                                <h6 class="card-title">UserService</h6>
                                <p class="card-text small">
                                    Gestione utenti con cache e notifiche
                                </p>
                                <span class="badge bg-primary">Singleton</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope fa-2x text-success mb-3"></i>
                                <h6 class="card-title">EmailService</h6>
                                <p class="card-text small">
                                    Invio email e notifiche
                                </p>
                                <span class="badge bg-success">Interface Binding</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-database fa-2x text-info mb-3"></i>
                                <h6 class="card-title">CacheService</h6>
                                <p class="card-text small">
                                    Gestione cache e performance
                                </p>
                                <span class="badge bg-info">Closure Binding</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test interattivo -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-play me-2"></i>
                    Test Interattivo
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Testa i servizi:</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="testUserService()">
                                <i class="fas fa-user me-1"></i> Test UserService
                            </button>
                            <button class="btn btn-success" onclick="testEmailService()">
                                <i class="fas fa-envelope me-1"></i> Test EmailService
                            </button>
                            <button class="btn btn-info" onclick="testCacheService()">
                                <i class="fas fa-database me-1"></i> Test CacheService
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Risultato:</h6>
                        <div id="test-result" class="alert alert-light" style="min-height: 100px;">
                            <i class="fas fa-info-circle me-2"></i>
                            Clicca su un pulsante per testare il servizio
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function testUserService() {
    showLoading();
    fetch('{{ route("users.test-services") }}')
        .then(response => response.json())
        .then(data => {
            showResult('UserService', data);
        })
        .catch(error => {
            showError('Errore nel test UserService: ' + error.message);
        });
}

function testEmailService() {
    showLoading();
    // Simula test EmailService
    setTimeout(() => {
        showResult('EmailService', {
            success: true,
            message: 'EmailService funziona correttamente',
            data: {
                method: 'sendWelcomeEmail',
                status: 'success'
            }
        });
    }, 1000);
}

function testCacheService() {
    showLoading();
    // Simula test CacheService
    setTimeout(() => {
        showResult('CacheService', {
            success: true,
            message: 'CacheService funziona correttamente',
            data: {
                method: 'get',
                status: 'success',
                cached: true
            }
        });
    }, 800);
}

function showLoading() {
    document.getElementById('test-result').innerHTML = 
        '<i class="fas fa-spinner fa-spin me-2"></i>Testando il servizio...';
}

function showResult(serviceName, data) {
    const resultDiv = document.getElementById('test-result');
    const success = data.success;
    const icon = success ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-danger';
    
    resultDiv.innerHTML = `
        <div class="d-flex align-items-start">
            <i class="fas ${icon} me-2 mt-1"></i>
            <div>
                <strong>${serviceName}:</strong> ${data.message}
                ${data.data ? `<br><small class="text-muted">Dati: ${JSON.stringify(data.data)}</small>` : ''}
            </div>
        </div>
    `;
    resultDiv.className = `alert ${success ? 'alert-success' : 'alert-danger'}`;
}

function showError(message) {
    document.getElementById('test-result').innerHTML = 
        `<i class="fas fa-exclamation-circle text-danger me-2"></i>${message}`;
    document.getElementById('test-result').className = 'alert alert-danger';
}
</script>
@endpush
@endsection
