@extends('layouts.app')

@section('title', 'Blade Templates Pattern Demo')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="fas fa-code me-2"></i>
                    Blade Templates Pattern Demo
                    <span class="badge blade-pattern-badge text-white ms-2">Laravel</span>
                </h1>
                <a href="{{ route('blade.test') }}" class="btn btn-info">
                    <i class="fas fa-flask me-1"></i> Test Components
                </a>
            </div>

            <!-- Introduzione -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Cos'è il Blade Templates Pattern?
                    </h5>
                </div>
                <div class="card-body">
                    <p class="lead">
                        Blade è il motore di templating di Laravel che fornisce una sintassi elegante e potente 
                        per creare viste dinamiche e riutilizzabili. Permette la separazione della logica di 
                        presentazione dal codice PHP.
                    </p>
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle text-success me-2"></i>Vantaggi:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Separazione delle responsabilità</li>
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Template riutilizzabili</li>
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Sintassi elegante</li>
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Sicurezza integrata</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-cogs text-info me-2"></i>Caratteristiche:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Template inheritance</li>
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Components riutilizzabili</li>
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Directives personalizzate</li>
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Escape automatico</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Componenti Implementati -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card component-card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-window-maximize me-2"></i>
                                Layout Master
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>Struttura:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>HTML base</li>
                                <li><i class="fas fa-check text-success me-2"></i>Meta tag</li>
                                <li><i class="fas fa-check text-success me-2"></i>Stack CSS/JS</li>
                                <li><i class="fas fa-check text-success me-2"></i>Sezioni dinamiche</li>
                            </ul>
                            <h6>Utilizzo:</h6>
                            <div class="code-example p-2 rounded">
                                <code>@extends('layouts.app')</code>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card component-card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-puzzle-piece me-2"></i>
                                Components
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>Tipi:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Form Components</li>
                                <li><i class="fas fa-check text-success me-2"></i>UI Components</li>
                                <li><i class="fas fa-check text-success me-2"></i>Content Components</li>
                                <li><i class="fas fa-check text-success me-2"></i>Layout Components</li>
                            </ul>
                            <h6>Utilizzo:</h6>
                            <div class="code-example p-2 rounded">
                                <code>&lt;x-post-card :post="$post" /&gt;</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Esempi di Codice -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card component-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-code me-2"></i>
                                Template Inheritance
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>Layout Master:</h6>
                            <div class="code-example p-3 rounded mb-3">
                                <pre><code>&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;@yield('title')&lt;/title&gt;
    @stack('styles')
&lt;/head&gt;
&lt;body&gt;
    @yield('content')
    @stack('scripts')
&lt;/body&gt;
&lt;/html&gt;</code></pre>
                            </div>
                            <h6>Template Specifico:</h6>
                            <div class="code-example p-3 rounded">
                                <pre><code>@extends('layouts.app')

@section('title', 'My Page')

@section('content')
    &lt;h1&gt;Hello World&lt;/h1&gt;
@endsection</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card component-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-magic me-2"></i>
                                Components e Slots
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>Component Definition:</h6>
                            <div class="code-example p-3 rounded mb-3">
                                <pre><code>&lt;div class="card"&gt;
    @if(isset($header))
        &lt;div class="card-header"&gt;
            {{ $header }}
        &lt;/div&gt;
    @endif
    
    &lt;div class="card-body"&gt;
        {{ $slot }}
    &lt;/div&gt;
&lt;/div&gt;</code></pre>
                            </div>
                            <h6>Component Usage:</h6>
                            <div class="code-example p-3 rounded">
                                <pre><code>&lt;x-card&gt;
    &lt;x-slot name="header"&gt;
        &lt;h5&gt;Title&lt;/h5&gt;
    &lt;/x-slot&gt;
    
    Content here...
&lt;/x-card&gt;</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Demo Components -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-play me-2"></i>
                        Demo Components
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Form Components:</h6>
                            
                            <x-form-input name="demo_name" label="Nome" placeholder="Inserisci il nome" />
                            <x-form-input name="demo_email" label="Email" type="email" placeholder="Inserisci l'email" />
                            <x-form-input name="demo_password" label="Password" type="password" :required="true" />
                            
                            <h6 class="mt-4">Card Component:</h6>
                            <x-card>
                                <x-slot name="header">
                                    <h5 class="mb-0">Demo Card</h5>
                                </x-slot>
                                
                                <p>Questo è un esempio di utilizzo del componente Card con header e footer personalizzati.</p>
                                
                                <x-slot name="footer">
                                    <button class="btn btn-primary">Action</button>
                                </x-slot>
                            </x-card>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>Modal Component:</h6>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#demoModal">
                                Apri Modal
                            </button>
                            
                            <x-modal id="demoModal" title="Demo Modal">
                                <p>Questo è un esempio di utilizzo del componente Modal.</p>
                                <p>Puoi inserire qualsiasi contenuto qui dentro.</p>
                                
                                <x-slot name="footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                                    <button type="button" class="btn btn-primary">Salva</button>
                                </x-slot>
                            </x-modal>
                            
                            <h6 class="mt-4">User Avatar Component:</h6>
                            <div class="d-flex align-items-center">
                                <x-user-avatar :user="(object)['name' => 'John Doe', 'initials' => 'JD']" size="lg" class="me-3" />
                                <div>
                                    <h6 class="mb-0">John Doe</h6>
                                    <small class="text-muted">Developer</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Directives Personalizzate -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Directives Personalizzate
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Role Directives:</h6>
                            <div class="code-example p-3 rounded mb-3">
                                <pre><code>@role('admin')
    &lt;p&gt;Admin content&lt;/p&gt;
@endrole

@admin
    &lt;p&gt;Admin panel&lt;/p&gt;
@endadmin</code></pre>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Custom Directives:</h6>
                            <div class="code-example p-3 rounded mb-3">
                                <pre><code>@datetime($post->created_at)
@currency($product->price)
@includeWhen($user->isAdmin(), 'admin.panel')</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
