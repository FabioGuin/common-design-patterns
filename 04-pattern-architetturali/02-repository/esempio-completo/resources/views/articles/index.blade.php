@extends('layouts.app')

@section('title', 'Articoli - Repository Pattern')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-newspaper text-primary me-2"></i>
                    Articoli - Repository Pattern
                </h1>
                <a href="{{ route('articles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Nuovo Articolo
                </a>
            </div>

            <!-- Pattern Info -->
            <div class="alert alert-info mb-4">
                <h6 class="fw-semibold mb-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Pattern Repository in Azione
                </h6>
                <p class="mb-0">
                    Questo esempio dimostra il pattern Repository che separa l'accesso ai dati dalla logica di business.
                    I controller usano i Service che a loro volta usano i Repository per accedere ai dati.
                </p>
            </div>

            <!-- Statistics -->
            @if(isset($stats))
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">{{ $stats['total'] }}</h4>
                                <small>Articoli Totali</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">{{ $stats['published'] }}</h4>
                                <small>Pubblicati</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">{{ $stats['drafts'] }}</h4>
                                <small>Bozze</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">{{ $articles->count() }}</h4>
                                <small>Visualizzati</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Articles List -->
            @if($articles->count() > 0)
                <div class="row">
                    @foreach($articles as $article)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column">
                                    <!-- Article Header -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <span class="badge bg-{{ $article->status === 'published' ? 'success' : 'warning' }}">
                                            {{ $article->status === 'published' ? 'Pubblicato' : 'Bozza' }}
                                        </span>
                                        <small class="text-muted">
                                            {{ $article->created_at->diffForHumans() }}
                                        </small>
                                    </div>

                                    <!-- Article Title -->
                                    <h5 class="card-title">
                                        <a href="{{ route('articles.show', $article) }}" class="text-decoration-none">
                                            {{ $article->title }}
                                        </a>
                                    </h5>

                                    <!-- Article Excerpt -->
                                    <p class="card-text text-muted flex-grow-1">
                                        {{ Str::limit($article->excerpt ?? $article->content, 100) }}
                                    </p>

                                    <!-- Article Meta -->
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $article->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($article->user->name) }}" 
                                                     alt="{{ $article->user->name }}" 
                                                     class="rounded-circle me-2" 
                                                     style="width: 30px; height: 30px;">
                                                <div>
                                                    <small class="text-muted d-block">{{ $article->user->name }}</small>
                                                    <small class="text-muted">{{ ucfirst($article->user->role) }}</small>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $article->created_at->format('d/m/Y') }}
                                                </small>
                                            </div>
                                        </div>

                                        <!-- Article Actions -->
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="btn-group w-100" role="group">
                                                <a href="{{ route('articles.show', $article) }}" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>
                                                    Leggi
                                                </a>
                                                <a href="{{ route('articles.edit', $article) }}" 
                                                   class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-edit me-1"></i>
                                                    Modifica
                                                </a>
                                                <form method="POST" action="{{ route('articles.destroy', $article) }}" 
                                                      class="d-inline" onsubmit="return confirm('Sei sicuro di voler eliminare questo articolo?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-trash me-1"></i>
                                                        Elimina
                                                    </button>
                                                </form>
                                            </div>
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
                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Nessun articolo trovato</h4>
                    <p class="text-muted mb-4">
                        Inizia a creare il tuo primo articolo utilizzando il pattern Repository!
                    </p>
                    <a href="{{ route('articles.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Crea Primo Articolo
                    </a>
                </div>
            @endif

            <!-- Pattern Architecture Info -->
            <div class="card mt-5">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-sitemap me-2"></i>
                        Architettura del Pattern Repository
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="fw-semibold">Controller Layer</h6>
                            <p class="text-muted small">
                                I controller ricevono le richieste HTTP e delegano la logica ai Service.
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="fw-semibold">Service Layer</h6>
                            <p class="text-muted small">
                                I Service contengono la logica di business e usano i Repository per accedere ai dati.
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="fw-semibold">Repository Layer</h6>
                            <p class="text-muted small">
                                I Repository astraggono l'accesso ai dati e possono essere facilmente sostituiti.
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <strong>Flusso:</strong> 
                        <span class="text-muted">Controller → Service → Repository → Database</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
