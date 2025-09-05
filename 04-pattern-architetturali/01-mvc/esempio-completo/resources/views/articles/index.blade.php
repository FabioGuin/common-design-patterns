@extends('layouts.app')

@section('title', 'Articoli')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-newspaper text-primary me-2"></i>
                Articoli
            </h1>
            <a href="{{ route('articles.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Nuovo Articolo
            </a>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('articles.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Stato</label>
                        <select name="status" id="status" class="form-select">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Tutti</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Pubblicati</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Bozze</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="author" class="form-label">Autore</label>
                        <input type="text" name="author" id="author" class="form-control" 
                               placeholder="Nome autore" value="{{ request('author') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="search" class="form-label">Ricerca</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Cerca nel titolo o contenuto" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="sort" class="form-label">Ordina per</label>
                        <select name="sort" id="sort" class="form-select">
                            <option value="published_at" {{ request('sort') === 'published_at' ? 'selected' : '' }}>Data pubblicazione</option>
                            <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Data creazione</option>
                            <option value="title" {{ request('sort') === 'title' ? 'selected' : '' }}>Titolo</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fas fa-filter me-1"></i>
                            Filtra
                        </button>
                        <a href="{{ route('articles.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Articles List -->
        @if($articles->count() > 0)
            <div class="row">
                @foreach($articles as $article)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card article-card h-100">
                            <div class="card-body d-flex flex-column">
                                <!-- Article Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-{{ $article->isPublished() ? 'success' : 'warning' }} status-badge">
                                        {{ $article->isPublished() ? 'Pubblicato' : 'Bozza' }}
                                    </span>
                                    <small class="text-muted">
                                        {{ $article->created_at->diffForHumans() }}
                                    </small>
                                </div>

                                <!-- Article Title -->
                                <h5 class="card-title">
                                    <a href="{{ route('articles.show', $article) }}" class="text-decoration-none">
                                        {{ $article->formatted_title }}
                                    </a>
                                </h5>

                                <!-- Article Excerpt -->
                                <p class="card-text text-muted flex-grow-1">
                                    {{ $article->excerpt }}
                                </p>

                                <!-- Article Meta -->
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $article->user->avatar_url }}" 
                                                 alt="{{ $article->user->name }}" 
                                                 class="author-avatar me-2">
                                            <div>
                                                <small class="text-muted d-block">{{ $article->user->formatted_name }}</small>
                                                <small class="text-muted">{{ $article->user->formatted_role }}</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <small class="reading-time">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $article->reading_time }} min
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

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $articles->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Nessun articolo trovato</h4>
                <p class="text-muted mb-4">
                    @if(request()->hasAny(['status', 'author', 'search']))
                        Prova a modificare i filtri di ricerca o 
                        <a href="{{ route('articles.index') }}">visualizza tutti gli articoli</a>.
                    @else
                        Inizia a creare il tuo primo articolo!
                    @endif
                </p>
                <a href="{{ route('articles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Crea Primo Articolo
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Statistics Card -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card stats-card">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h3 class="mb-0">{{ $articles->total() }}</h3>
                        <small>Articoli Totali</small>
                    </div>
                    <div class="col-md-3">
                        <h3 class="mb-0">{{ $articles->where('status', 'published')->count() }}</h3>
                        <small>Pubblicati</small>
                    </div>
                    <div class="col-md-3">
                        <h3 class="mb-0">{{ $articles->where('status', 'draft')->count() }}</h3>
                        <small>Bozze</small>
                    </div>
                    <div class="col-md-3">
                        <h3 class="mb-0">{{ $articles->unique('user_id')->count() }}</h3>
                        <small>Autori</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit form on filter change
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.querySelector('form[method="GET"]');
        const selects = filterForm.querySelectorAll('select');
        
        selects.forEach(select => {
            select.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    });
</script>
@endpush
