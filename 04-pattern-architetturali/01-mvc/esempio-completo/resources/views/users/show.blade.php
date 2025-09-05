@extends('layouts.app')

@section('title', $user->name)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- User Profile Card -->
        <div class="card mb-4">
            <div class="card-body">
                <!-- User Header -->
                <div class="d-flex align-items-start mb-4">
                    <img src="{{ $user->avatar_url }}" 
                         alt="{{ $user->name }}" 
                         class="rounded-circle me-4" 
                         style="width: 120px; height: 120px;">
                    <div class="flex-grow-1">
                        <h1 class="h3 mb-2">{{ $user->formatted_name }}</h1>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }} me-2">
                                {{ $user->is_active ? 'Attivo' : 'Inattivo' }}
                            </span>
                            <span class="badge bg-info">{{ $user->formatted_role }}</span>
                        </div>
                        <p class="text-muted mb-0">
                            <i class="fas fa-envelope me-1"></i>
                            {{ $user->email }}
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-calendar me-1"></i>
                            Membro dal {{ $user->created_at->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>
                            Azioni
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('users.articles', $user) }}">
                                    <i class="fas fa-newspaper me-2"></i>
                                    Vedi Articoli
                                </a>
                            </li>
                            @if($user->is_active)
                                <li>
                                    <form method="POST" action="{{ route('users.deactivate', $user) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-user-slash me-2"></i>
                                            Disattiva
                                        </button>
                                    </form>
                                </li>
                            @else
                                <li>
                                    <form method="POST" action="{{ route('users.activate', $user) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-user-check me-2"></i>
                                            Attiva
                                        </button>
                                    </form>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button class="dropdown-item" onclick="changeRole()">
                                    <i class="fas fa-user-tag me-2"></i>
                                    Cambia Ruolo
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- User Bio -->
                @if($user->bio)
                    <div class="alert alert-light border-start border-primary border-4">
                        <h6 class="fw-semibold mb-2">Biografia</h6>
                        <p class="mb-0">{{ $user->bio }}</p>
                    </div>
                @endif

                <!-- User Stats -->
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body py-3">
                                <h4 class="text-primary mb-1">{{ $user->published_articles_count }}</h4>
                                <small class="text-muted">Articoli Pubblicati</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body py-3">
                                <h4 class="text-info mb-1">{{ $user->total_articles_count }}</h4>
                                <small class="text-muted">Articoli Totali</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body py-3">
                                <h4 class="text-warning mb-1">{{ $user->total_articles_count - $user->published_articles_count }}</h4>
                                <small class="text-muted">Bozze</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body py-3">
                                <h4 class="text-success mb-1">{{ $user->created_at->diffInDays(now()) }}</h4>
                                <small class="text-muted">Giorni Membro</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Articles -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-newspaper me-2"></i>
                    Articoli Recenti
                </h5>
                <a href="{{ route('users.articles', $user) }}" class="btn btn-outline-primary btn-sm">
                    Vedi Tutti
                </a>
            </div>
            <div class="card-body">
                @if($recentArticles->count() > 0)
                    @foreach($recentArticles as $article)
                        <div class="d-flex mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0">
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                     style="width: 60px; height: 60px;">
                                    <i class="fas fa-newspaper text-muted"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">
                                    <a href="{{ route('articles.show', $article) }}" class="text-decoration-none">
                                        {{ $article->title }}
                                    </a>
                                </h6>
                                <p class="text-muted small mb-1">{{ Str::limit($article->excerpt, 100) }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        {{ $article->created_at->diffForHumans() }}
                                    </small>
                                    <span class="badge bg-{{ $article->isPublished() ? 'success' : 'warning' }} badge-sm">
                                        {{ $article->isPublished() ? 'Pubblicato' : 'Bozza' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-newspaper fa-2x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Nessun articolo pubblicato ancora.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- User Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Informazioni Dettagliate
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Email:</strong><br>
                    <a href="mailto:{{ $user->email }}" class="text-decoration-none">{{ $user->email }}</a>
                </div>
                <div class="mb-3">
                    <strong>Ruolo:</strong><br>
                    <span class="badge bg-info">{{ $user->formatted_role }}</span>
                </div>
                <div class="mb-3">
                    <strong>Stato:</strong><br>
                    <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                        {{ $user->is_active ? 'Attivo' : 'Inattivo' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Registrato:</strong><br>
                    {{ $user->created_at->format('d/m/Y H:i') }}
                </div>
                <div class="mb-3">
                    <strong>Ultimo aggiornamento:</strong><br>
                    {{ $user->updated_at->format('d/m/Y H:i') }}
                </div>
                <div class="mb-3">
                    <strong>Membro da:</strong><br>
                    {{ $user->created_at->diffForHumans() }}
                </div>
            </div>
        </div>

        <!-- User Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Statistiche
                </h5>
            </div>
            <div class="card-body">
                @php
                    $stats = $user->getStats();
                @endphp
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Articoli Totali:</span>
                        <strong>{{ $stats['total_articles'] }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Articoli Pubblicati:</span>
                        <strong class="text-success">{{ $stats['published_articles'] }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Bozze:</span>
                        <strong class="text-warning">{{ $stats['draft_articles'] }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Membro da:</span>
                        <strong>{{ $stats['member_since'] }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Azioni Rapide
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('users.articles', $user) }}" class="btn btn-outline-primary">
                        <i class="fas fa-newspaper me-1"></i>
                        Vedi Tutti gli Articoli
                    </a>
                    <a href="{{ route('articles.create') }}" class="btn btn-outline-success">
                        <i class="fas fa-plus me-1"></i>
                        Crea Nuovo Articolo
                    </a>
                    @if($user->is_active)
                        <form method="POST" action="{{ route('users.deactivate', $user) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning w-100">
                                <i class="fas fa-user-slash me-1"></i>
                                Disattiva Utente
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('users.activate', $user) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="fas fa-user-check me-1"></i>
                                Attiva Utente
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Role Modal -->
<div class="modal fade" id="changeRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambia Ruolo Utente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('users.change-role', $user) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="role" class="form-label">Nuovo Ruolo</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                            <option value="editor" {{ $user->role === 'editor' ? 'selected' : '' }}>Editor</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Cambia Ruolo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Navigation -->
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-between">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Torna agli Autori
            </a>
            <div>
                <a href="{{ route('users.articles', $user) }}" class="btn btn-primary">
                    <i class="fas fa-newspaper me-1"></i>
                    Vedi Articoli
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function changeRole() {
        const modal = new bootstrap.Modal(document.getElementById('changeRoleModal'));
        modal.show();
    }
</script>
@endpush
