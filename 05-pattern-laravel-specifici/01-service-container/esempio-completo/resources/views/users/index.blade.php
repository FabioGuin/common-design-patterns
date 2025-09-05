@extends('layouts.app')

@section('title', 'Gestione Utenti')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">
                <i class="fas fa-users me-2"></i>
                Gestione Utenti
                <span class="badge pattern-badge text-white ms-2">Service Container</span>
            </h1>
            <div>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Nuovo Utente
                </a>
                <a href="{{ route('users.test-services') }}" class="btn btn-info">
                    <i class="fas fa-flask me-1"></i> Test Services
                </a>
            </div>
        </div>

        <!-- Statistiche rapide -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Totale Utenti</h6>
                                <h3 class="mb-0">{{ $userStats['total'] ?? 0 }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Attivi</h6>
                                <h3 class="mb-0">{{ $userStats['active'] ?? 0 }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Inattivi</h6>
                                <h3 class="mb-0">{{ $userStats['inactive'] ?? 0 }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-times fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Nuovi Oggi</h6>
                                <h3 class="mb-0">{{ $userStats['today'] ?? 0 }}</h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-user-plus fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtri e ricerca -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Cerca</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Nome, email...">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Stato</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tutti</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Attivi</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inattivi</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sort" class="form-label">Ordina per</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nome</option>
                            <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>Email</option>
                            <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Data Creazione</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Filtra
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabella utenti -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    Lista Utenti
                    @if(request()->hasAny(['search', 'status', 'sort']))
                        <small class="text-muted">(filtrati)</small>
                    @endif
                </h5>
            </div>
            <div class="card-body p-0">
                @if($users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Stato</th>
                                    <th>Creato</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                {{ $user->name }}
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->is_active)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Attivo
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-times me-1"></i>Inattivo
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $user->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('users.show', $user) }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($user->is_active)
                                                    <form action="{{ route('users.deactivate', $user) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-warning" 
                                                                onclick="return confirm('Disattivare questo utente?')">
                                                            <i class="fas fa-user-times"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('users.activate', $user) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success">
                                                            <i class="fas fa-user-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Eliminare definitivamente questo utente?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nessun utente trovato</h5>
                        <p class="text-muted">
                            @if(request()->hasAny(['search', 'status', 'sort']))
                                Prova a modificare i filtri di ricerca.
                            @else
                                Inizia creando il primo utente.
                            @endif
                        </p>
                        @if(!request()->hasAny(['search', 'status', 'sort']))
                            <a href="{{ route('users.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Crea Primo Utente
                            </a>
                        @endif
                    </div>
                @endif
            </div>
            @if($users->hasPages())
                <div class="card-footer">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
}
</style>
@endsection
