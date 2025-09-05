@extends('layouts.app')

@section('title', 'Prodotti - Specification Pattern')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-filter text-primary me-2"></i>
                    Prodotti - Specification Pattern
                </h1>
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Nuovo Prodotto
                </a>
            </div>

            <!-- Pattern Info -->
            <div class="alert alert-info mb-4">
                <h6 class="fw-semibold mb-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Pattern Specification in Azione
                </h6>
                <p class="mb-0">
                    Questo esempio dimostra il pattern Specification che incapsula la logica di business
                    in oggetti componibili e riutilizzabili. Le specifiche possono essere combinate per creare filtri complessi.
                </p>
            </div>

            <!-- Statistics -->
            @if(isset($stats))
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">{{ $stats['total_products'] }}</h4>
                                <small>Prodotti Totali</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">{{ $stats['available_products'] }}</h4>
                                <small>Disponibili</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">{{ $stats['products_on_sale'] }}</h4>
                                <small>In Offerta</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">€{{ number_format($stats['average_price'], 2) }}</h4>
                                <small>Prezzo Medio</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-dark text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">€{{ number_format($stats['total_value'], 2) }}</h4>
                                <small>Valore Totale</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">{{ $products->count() }}</h4>
                                <small>Visualizzati</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filtri (Specification Pattern)
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('products.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="name" class="form-label">Nome Prodotto</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="{{ $criteria['name'] ?? '' }}" placeholder="Cerca per nome...">
                            </div>
                            <div class="col-md-2">
                                <label for="category_id" class="form-label">Categoria</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">Tutte le categorie</option>
                                    <option value="1" {{ ($criteria['category_id'] ?? '') == '1' ? 'selected' : '' }}>Elettronica</option>
                                    <option value="2" {{ ($criteria['category_id'] ?? '') == '2' ? 'selected' : '' }}>Abbigliamento</option>
                                    <option value="3" {{ ($criteria['category_id'] ?? '') == '3' ? 'selected' : '' }}>Casa</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="min_price" class="form-label">Prezzo Min</label>
                                <input type="number" class="form-control" id="min_price" name="min_price" 
                                       value="{{ $criteria['min_price'] ?? '' }}" placeholder="0.00" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <label for="max_price" class="form-label">Prezzo Max</label>
                                <input type="number" class="form-control" id="max_price" name="max_price" 
                                       value="{{ $criteria['max_price'] ?? '' }}" placeholder="1000.00" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <label for="in_stock" class="form-label">Disponibilità</label>
                                <select class="form-select" id="in_stock" name="in_stock">
                                    <option value="">Tutti</option>
                                    <option value="1" {{ ($criteria['in_stock'] ?? '') == '1' ? 'selected' : '' }}>Solo disponibili</option>
                                </select>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products List -->
            @if($products->count() > 0)
                <div class="row">
                    @foreach($products as $product)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column">
                                    <!-- Product Header -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <span class="badge bg-{{ $product->stock > 0 ? 'success' : 'danger' }}">
                                            {{ $product->stock > 0 ? 'Disponibile' : 'Esaurito' }}
                                        </span>
                                        <small class="text-muted">
                                            {{ $product->created_at->diffForHumans() }}
                                        </small>
                                    </div>

                                    <!-- Product Info -->
                                    <h5 class="card-title">
                                        <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
                                            {{ $product->name }}
                                        </a>
                                    </h5>

                                    <p class="card-text text-muted">
                                        <strong>Prezzo:</strong> €{{ number_format($product->price, 2) }}<br>
                                        <strong>Categoria:</strong> {{ $product->category->name ?? 'N/A' }}<br>
                                        <strong>Stock:</strong> {{ $product->stock }} unità
                                    </p>

                                    <!-- Product Description -->
                                    <p class="card-text text-muted flex-grow-1">
                                        {{ Str::limit($product->description, 100) }}
                                    </p>

                                    <!-- Product Actions -->
                                    <div class="mt-auto">
                                        <div class="btn-group w-100" role="group">
                                            <a href="{{ route('products.show', $product) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>
                                                Dettagli
                                            </a>
                                            <a href="{{ route('products.edit', $product) }}" 
                                               class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-edit me-1"></i>
                                                Modifica
                                            </a>
                                            @if($product->stock > 0)
                                                <button type="button" class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-shopping-cart me-1"></i>
                                                    Aggiungi
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-box fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Nessun prodotto trovato</h4>
                    <p class="text-muted mb-4">
                        Inizia a creare il tuo primo prodotto utilizzando il pattern Specification!
                    </p>
                    <a href="{{ route('products.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Crea Primo Prodotto
                    </a>
                </div>
            @endif

            <!-- Pattern Architecture Info -->
            <div class="card mt-5">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-sitemap me-2"></i>
                        Architettura del Pattern Specification
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6 class="fw-semibold">Business Rules</h6>
                            <p class="text-muted small">
                                Regole di business incapsulate in specifiche.
                            </p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="fw-semibold">Specification Objects</h6>
                            <p class="text-muted small">
                                Oggetti che implementano la logica di business.
                            </p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="fw-semibold">Combination Logic</h6>
                            <p class="text-muted small">
                                Logica per combinare specifiche (AND, OR, NOT).
                            </p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="fw-semibold">Query Generation</h6>
                            <p class="text-muted small">
                                Generazione ottimizzata delle query database.
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <strong>Flusso:</strong> 
                        <span class="text-muted">Business Rule → Specification → Combination → Query → Results</span>
                    </div>
                </div>
            </div>

            <!-- Specification Benefits -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Vantaggi del Pattern Specification
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i> Modularità della logica</li>
                                <li><i class="fas fa-check text-success me-2"></i> Riusabilità delle specifiche</li>
                                <li><i class="fas fa-check text-success me-2"></i> Testabilità isolata</li>
                                <li><i class="fas fa-check text-success me-2"></i> Leggibilità del codice</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i> Flessibilità di combinazione</li>
                                <li><i class="fas fa-check text-success me-2"></i> Manutenibilità migliorata</li>
                                <li><i class="fas fa-check text-success me-2"></i> Performance ottimizzata</li>
                                <li><i class="fas fa-check text-success me-2"></i> Documentazione automatica</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
