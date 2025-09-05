@extends('layouts.app')

@section('title', 'Service Provider Demo')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="fas fa-cogs me-2"></i>
                Service Provider Pattern Demo
                <span class="badge pattern-badge text-white ms-2">Laravel</span>
            </h1>
            <div>
                <a href="{{ route('test-services') }}" class="btn btn-info" target="_blank">
                    <i class="fas fa-flask me-1"></i> Test Services
                </a>
                <a href="{{ route('test-notifications') }}" class="btn btn-warning" target="_blank">
                    <i class="fas fa-bell me-1"></i> Test Notifications
                </a>
                <a href="{{ route('test-api-client') }}" class="btn btn-success" target="_blank">
                    <i class="fas fa-plug me-1"></i> Test API
                </a>
            </div>
        </div>

        <!-- Introduzione -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Cos'è il Service Provider Pattern?
                </h5>
            </div>
            <div class="card-body">
                <p class="lead">
                    I Service Provider in Laravel sono la chiave per organizzare e gestire i servizi dell'applicazione. 
                    Permettono di registrare servizi, configurazioni, middleware e molto altro in modo modulare.
                </p>
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-check-circle text-success me-2"></i>Vantaggi:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Organizzazione modulare</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Configurazione centralizzata</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Lazy loading</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Facile testing</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-cogs text-info me-2"></i>Caratteristiche:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Binding di servizi</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Pubblicazione assets</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Registrazione middleware</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Gestione eventi</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Provider Implementati -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card provider-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-blog me-2"></i>
                            BlogServiceProvider
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Servizi Registrati:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>PostService</li>
                            <li><i class="fas fa-check text-success me-2"></i>CommentService</li>
                            <li><i class="fas fa-check text-success me-2"></i>CategoryService</li>
                        </ul>
                        <h6>Funzionalità:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Binding singleton</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Pubblicazione config</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Middleware blog.auth</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Eventi post</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card api-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-code me-2"></i>
                            ApiServiceProvider
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Servizi Registrati:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>ApiClient</li>
                            <li><i class="fas fa-check text-success me-2"></i>ApiResponse</li>
                        </ul>
                        <h6>Funzionalità:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Rate limiting</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Middleware api.auth</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Route groups API</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Response macros</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card notification-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bell me-2"></i>
                            NotificationServiceProvider
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Servizi Registrati:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>NotificationService</li>
                            <li><i class="fas fa-check text-success me-2"></i>EmailChannel</li>
                            <li><i class="fas fa-check text-success me-2"></i>SmsChannel</li>
                            <li><i class="fas fa-check text-success me-2"></i>PushChannel</li>
                        </ul>
                        <h6>Funzionalità:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Multi-canale</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Configurazione dinamica</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Eventi notifiche</li>
                            <li><i class="fas fa-arrow-right text-primary me-2"></i>Test canali</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Esempi di Codice -->
        <div class="row">
            <div class="col-md-6">
                <div class="card service-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-code me-2"></i>
                            Registrazione Servizi
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>BlogServiceProvider:</h6>
                        <div class="code-example p-3 rounded mb-3">
                            <pre><code>public function register()
{
    // Singleton
    $this->app->singleton(PostService::class);
    
    // Binding interfaccia
    $this->app->bind(
        PostRepositoryInterface::class,
        EloquentPostRepository::class
    );
    
    // Alias
    $this->app->alias(PostService::class, 'blog.posts');
}</code></pre>
                        </div>
                        <h6>Utilizzo nel Controller:</h6>
                        <div class="code-example p-3 rounded">
                            <pre><code>public function __construct(
    private PostService $postService,
    private CommentService $commentService
) {
    // Dependency injection automatica
}</code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card service-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-magic me-2"></i>
                            Bootstrap e Configurazione
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>Metodo boot():</h6>
                        <div class="code-example p-3 rounded mb-3">
                            <pre><code>public function boot()
{
    // Pubblica configurazioni
    $this->publishes([
        __DIR__.'/config/blog.php' => config_path('blog.php'),
    ]);
    
    // Registra middleware
    $this->app['router']->aliasMiddleware('blog.auth', BlogAuthMiddleware::class);
    
    // Registra eventi
    Event::listen(PostCreated::class, SendNotification::class);
}</code></pre>
                        </div>
                        <h6>Configurazione dinamica:</h6>
                        <div class="code-example p-3 rounded">
                            <pre><code>if ($this->app->environment('production')) {
    $this->app['config']->set('blog.cache_ttl', 7200);
}</code></pre>
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
                    <div class="col-md-4">
                        <h6>Testa i Servizi:</h6>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="testServices()">
                                <i class="fas fa-flask me-1"></i> Test All Services
                            </button>
                            <button class="btn btn-success" onclick="testApiClient()">
                                <i class="fas fa-plug me-1"></i> Test API Client
                            </button>
                            <button class="btn btn-warning" onclick="testNotifications()">
                                <i class="fas fa-bell me-1"></i> Test Notifications
                            </button>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h6>Risultato:</h6>
                        <div id="test-result" class="alert alert-light" style="min-height: 200px;">
                            <i class="fas fa-info-circle me-2"></i>
                            Clicca su un pulsante per testare i servizi
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiche Provider -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Statistiche Provider
                </h5>
            </div>
            <div class="card-body">
                <div class="row" id="provider-stats">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h6>Provider Registrati</h6>
                                <h3 id="total-providers">-</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h6>Servizi Attivi</h6>
                                <h3 id="active-services">-</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h6>Middleware</h6>
                                <h3 id="middleware-count">-</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h6>Eventi</h6>
                                <h3 id="events-count">-</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function testServices() {
    showLoading();
    fetch('{{ route("test-services") }}')
        .then(response => response.json())
        .then(data => {
            showResult('Services Test', data);
        })
        .catch(error => {
            showError('Errore nel test servizi: ' + error.message);
        });
}

function testApiClient() {
    showLoading();
    fetch('{{ route("test-api-client") }}')
        .then(response => response.json())
        .then(data => {
            showResult('API Client Test', data);
        })
        .catch(error => {
            showError('Errore nel test API Client: ' + error.message);
        });
}

function testNotifications() {
    showLoading();
    fetch('{{ route("test-notifications") }}')
        .then(response => response.json())
        .then(data => {
            showResult('Notifications Test', data);
        })
        .catch(error => {
            showError('Errore nel test notifiche: ' + error.message);
        });
}

function showLoading() {
    document.getElementById('test-result').innerHTML = 
        '<i class="fas fa-spinner fa-spin me-2"></i>Testando i servizi...';
}

function showResult(testName, data) {
    const resultDiv = document.getElementById('test-result');
    const success = data.success;
    const icon = success ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-danger';
    
    let html = `
        <div class="d-flex align-items-start">
            <i class="fas ${icon} me-2 mt-1"></i>
            <div>
                <strong>${testName}:</strong> ${data.message}
                <br><small class="text-muted">Dati: ${JSON.stringify(data.data, null, 2)}</small>
            </div>
        </div>
    `;
    
    resultDiv.innerHTML = html;
    resultDiv.className = `alert ${success ? 'alert-success' : 'alert-danger'}`;
}

function showError(message) {
    document.getElementById('test-result').innerHTML = 
        `<i class="fas fa-exclamation-circle text-danger me-2"></i>${message}`;
    document.getElementById('test-result').className = 'alert alert-danger';
}

// Carica statistiche al caricamento della pagina
document.addEventListener('DOMContentLoaded', function() {
    loadProviderStats();
});

function loadProviderStats() {
    // Simula caricamento statistiche
    document.getElementById('total-providers').textContent = '3';
    document.getElementById('active-services').textContent = '8';
    document.getElementById('middleware-count').textContent = '4';
    document.getElementById('events-count').textContent = '6';
}
</script>
@endpush
@endsection
