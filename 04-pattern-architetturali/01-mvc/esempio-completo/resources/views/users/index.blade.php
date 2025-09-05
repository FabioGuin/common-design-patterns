@extends('layouts.app')

@section('title', 'Autori')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-users text-primary me-2"></i>
                Autori
            </h1>
            <a href="{{ route('users.stats') }}" class="btn btn-outline-primary">
                <i class="fas fa-chart-bar me-1"></i>
                Statistiche
            </a>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="role" class="form-label">Ruolo</label>
                        <select name="role" id="role" class="form-select">
                            <option value="all" {{ request('role') === 'all' ? 'selected' : '' }}>Tutti</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="editor" {{ request('role') === 'editor' ? 'selected' : '' }}>Editor</option>
                            <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Stato</label>
                        <select name="status" id="status" class="form-select">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Tutti</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Attivi</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inattivi</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Ricerca</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Cerca per nome o email" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="sort" class="form-label">Ordina per</label>
                        <select name="sort" id="sort" class="form-select">
                            <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Data registrazione</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Nome</option>
                            <option value="published_articles_count" {{ request('sort') === 'published_articles_count' ? 'selected' : '' }}>Articoli</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fas fa-filter me-1"></i>
                            Filtra
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users List -->
        @if($users->count() > 0)
            <div class="row">
                @foreach($users as $user)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column">
                                <!-- User Header -->
                                <div class="text-center mb-3">
                                    <img src="{{ $user->avatar_url }}" 
                                         alt="{{ $user->name }}" 
                                         class="rounded-circle mb-2" 
                                         style="width: 80px; height: 80px;">
                                    <h5 class="mb-1">{{ $user->formatted_name }}</h5>
                                    <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }} mb-2">
                                        {{ $user->is_active ? 'Attivo' : 'Inattivo' }}
                                    </span>
                                    <div class="text-muted small">{{ $user->formatted_role }}</div>
                                </div>

                                <!-- User Info -->
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-envelope text-muted me-2"></i>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                    @if($user->bio)
                                        <p class="text-muted small mb-0">{{ Str::limit($user->bio, 100) }}</p>
                                    @endif
                                </div>

                                <!-- User Stats -->
                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <div class="fw-bold text-primary">{{ $user->published_articles_count }}</div>
                                        <small class="text-muted">Articoli</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="fw-bold text-info">{{ $user->total_articles_count }}</div>
                                        <small class="text-muted">Totali</small>
                                    </div>
                                </div>

                                <!-- User Actions -->
                                <div class="mt-auto">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('users.show', $user) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-user me-1"></i>
                                            Vedi Profilo
                                        </a>
                                        <a href="{{ route('users.articles', $user) }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-newspaper me-1"></i>
                                            Articoli
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $users->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Nessun autore trovato</h4>
                <p class="text-muted mb-4">
                    @if(request()->hasAny(['role', 'status', 'search']))
                        Prova a modificare i filtri di ricerca o 
                        <a href="{{ route('users.index') }}">visualizza tutti gli autori</a>.
                    @else
                        Non ci sono ancora autori registrati.
                    @endif
                </p>
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
                        <h3 class="mb-0">{{ $users->total() }}</h3>
                        <small>Autori Totali</small>
                    </div>
                    <div class="col-md-3">
                        <h3 class="mb-0">{{ $users->where('is_active', true)->count() }}</h3>
                        <small>Attivi</small>
                    </div>
                    <div class="col-md-3">
                        <h3 class="mb-0">{{ $users->where('published_articles_count', '>', 0)->count() }}</h3>
                        <small>Con Articoli</small>
                    </div>
                    <div class="col-md-3">
                        <h3 class="mb-0">{{ $users->sum('published_articles_count') }}</h3>
                        <small>Articoli Pubblicati</small>
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
