@extends('layouts.app')

@section('title', $article->title)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Article Header -->
        <div class="card mb-4">
            <div class="card-body">
                <!-- Status Badge -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge bg-{{ $article->isPublished() ? 'success' : 'warning' }} fs-6">
                        {{ $article->isPublished() ? 'Pubblicato' : 'Bozza' }}
                    </span>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>
                            Azioni
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('articles.edit', $article) }}">
                                    <i class="fas fa-edit me-2"></i>
                                    Modifica
                                </a>
                            </li>
                            @if($article->isPublished())
                                <li>
                                    <form method="POST" action="{{ route('articles.draft', $article) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-eye-slash me-2"></i>
                                            Metti in Bozza
                                        </button>
                                    </form>
                                </li>
                            @else
                                <li>
                                    <form method="POST" action="{{ route('articles.publish', $article) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-eye me-2"></i>
                                            Pubblica
                                        </button>
                                    </form>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('articles.destroy', $article) }}" 
                                      class="d-inline" onsubmit="return confirm('Sei sicuro di voler eliminare questo articolo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-trash me-2"></i>
                                        Elimina
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Article Title -->
                <h1 class="display-6 fw-bold mb-3">{{ $article->formatted_title }}</h1>

                <!-- Article Meta -->
                <div class="d-flex flex-wrap align-items-center text-muted mb-4">
                    <div class="d-flex align-items-center me-4">
                        <img src="{{ $article->user->avatar_url }}" 
                             alt="{{ $article->user->name }}" 
                             class="author-avatar me-2">
                        <div>
                            <div class="fw-semibold">{{ $article->user->formatted_name }}</div>
                            <small>{{ $article->user->formatted_role }}</small>
                        </div>
                    </div>
                    <div class="vr me-4"></div>
                    <div class="me-4">
                        <i class="fas fa-calendar me-1"></i>
                        {{ $article->created_at->format('d/m/Y') }}
                    </div>
                    @if($article->isPublished())
                        <div class="me-4">
                            <i class="fas fa-clock me-1"></i>
                            {{ $article->reading_time }} min di lettura
                        </div>
                    @endif
                    <div>
                        <i class="fas fa-tag me-1"></i>
                        {{ $article->status }}
                    </div>
                </div>

                <!-- Article Excerpt -->
                @if($article->excerpt)
                    <div class="alert alert-light border-start border-primary border-4">
                        <p class="mb-0 fst-italic">{{ $article->excerpt }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Article Content -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="article-content">
                    {!! nl2br(e($article->content)) !!}
                </div>
            </div>
        </div>

        <!-- Article Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-3">Azioni Articolo</h6>
                        <div class="d-grid gap-2 d-md-block">
                            <a href="{{ route('articles.edit', $article) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>
                                Modifica
                            </a>
                            @if($article->isPublished())
                                <form method="POST" action="{{ route('articles.draft', $article) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-eye-slash me-1"></i>
                                        Metti in Bozza
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('articles.publish', $article) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-eye me-1"></i>
                                        Pubblica
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-3">Condivisione</h6>
                        <div class="d-grid gap-2 d-md-block">
                            <button class="btn btn-outline-primary" onclick="shareArticle()">
                                <i class="fas fa-share me-1"></i>
                                Condividi
                            </button>
                            <button class="btn btn-outline-secondary" onclick="printArticle()">
                                <i class="fas fa-print me-1"></i>
                                Stampa
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Author Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>
                    Informazioni Autore
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="{{ $article->user->avatar_url }}" 
                         alt="{{ $article->user->name }}" 
                         class="rounded-circle" 
                         style="width: 80px; height: 80px;">
                    <h6 class="mt-2 mb-1">{{ $article->user->formatted_name }}</h6>
                    <small class="text-muted">{{ $article->user->formatted_role }}</small>
                </div>
                
                @if($article->user->bio)
                    <p class="text-muted">{{ $article->user->bio }}</p>
                @endif

                <div class="row text-center">
                    <div class="col-6">
                        <div class="fw-bold">{{ $article->user->published_articles_count }}</div>
                        <small class="text-muted">Articoli</small>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold">{{ $article->user->created_at->format('Y') }}</div>
                        <small class="text-muted">Membro dal</small>
                    </div>
                </div>

                <div class="d-grid mt-3">
                    <a href="{{ route('users.show', $article->user) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-user me-1"></i>
                        Vedi Profilo
                    </a>
                </div>
            </div>
        </div>

        <!-- Article Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Statistiche Articolo
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="fw-bold">{{ $article->reading_time }}</div>
                        <small class="text-muted">Min di lettura</small>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold">{{ strlen($article->content) }}</div>
                        <small class="text-muted">Caratteri</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="fw-bold">{{ $article->created_at->format('d/m/Y') }}</div>
                        <small class="text-muted">Creato</small>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold">{{ $article->updated_at->format('d/m/Y') }}</div>
                        <small class="text-muted">Modificato</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Articles -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-newspaper me-2"></i>
                    Altri Articoli
                </h5>
            </div>
            <div class="card-body">
                @php
                    $relatedArticles = \App\Models\Article::where('user_id', $article->user_id)
                                                        ->where('id', '!=', $article->id)
                                                        ->published()
                                                        ->recent()
                                                        ->limit(3)
                                                        ->get();
                @endphp

                @if($relatedArticles->count() > 0)
                    @foreach($relatedArticles as $relatedArticle)
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-light rounded" style="width: 60px; height: 60px;"></div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">
                                    <a href="{{ route('articles.show', $relatedArticle) }}" class="text-decoration-none">
                                        {{ Str::limit($relatedArticle->title, 50) }}
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    {{ $relatedArticle->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">Nessun altro articolo disponibile.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Navigation -->
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-between">
            <a href="{{ route('articles.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Torna agli Articoli
            </a>
            <div>
                @if($article->id > 1)
                    <a href="{{ route('articles.show', $article->id - 1) }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-chevron-left me-1"></i>
                        Precedente
                    </a>
                @endif
                <a href="{{ route('articles.show', $article->id + 1) }}" class="btn btn-outline-primary">
                    Successivo
                    <i class="fas fa-chevron-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function shareArticle() {
        if (navigator.share) {
            navigator.share({
                title: '{{ $article->title }}',
                text: '{{ $article->excerpt }}',
                url: window.location.href
            });
        } else {
            // Fallback: copia URL negli appunti
            navigator.clipboard.writeText(window.location.href).then(function() {
                alert('URL copiato negli appunti!');
            });
        }
    }

    function printArticle() {
        window.print();
    }

    // Print styles
    const printStyles = `
        @media print {
            .navbar, .footer, .card-header, .btn, .dropdown { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
            .article-content { font-size: 14px !important; }
        }
    `;
    
    const styleSheet = document.createElement("style");
    styleSheet.textContent = printStyles;
    document.head.appendChild(styleSheet);
</script>
@endpush
