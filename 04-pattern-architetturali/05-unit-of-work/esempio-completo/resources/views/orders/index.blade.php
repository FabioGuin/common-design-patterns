@extends('layouts.app')

@section('title', 'Ordini - Unit of Work Pattern')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-shopping-cart text-primary me-2"></i>
                    Ordini - Unit of Work Pattern
                </h1>
                <a href="{{ route('orders.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Nuovo Ordine
                </a>
            </div>

            <!-- Pattern Info -->
            <div class="alert alert-info mb-4">
                <h6 class="fw-semibold mb-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Pattern Unit of Work in Azione
                </h6>
                <p class="mb-0">
                    Questo esempio dimostra il pattern Unit of Work che gestisce transazioni atomiche
                    per ordini, prodotti e inventario. Tutte le operazioni vengono eseguite insieme o annullate.
                </p>
            </div>

            <!-- Statistics -->
            @if(isset($stats))
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">{{ $stats['total_orders'] }}</h4>
                                <small>Ordini Totali</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">{{ $stats['pending_orders'] }}</h4>
                                <small>In Attesa</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">{{ $stats['processing_orders'] }}</h4>
                                <small>In Elaborazione</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">{{ $stats['completed_orders'] }}</h4>
                                <small>Completati</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">{{ $stats['cancelled_orders'] }}</h4>
                                <small>Cancellati</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-dark text-white">
                            <div class="card-body text-center">
                                <h4 class="mb-0">€{{ number_format($stats['total_revenue'], 2) }}</h4>
                                <small>Fatturato</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Orders List -->
            @if($orders->count() > 0)
                <div class="row">
                    @foreach($orders as $order)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column">
                                    <!-- Order Header -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <span class="badge bg-{{ $this->getStatusColor($order->status) }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                        <small class="text-muted">
                                            {{ $order->created_at->diffForHumans() }}
                                        </small>
                                    </div>

                                    <!-- Order Info -->
                                    <h5 class="card-title">
                                        <a href="{{ route('orders.show', $order) }}" class="text-decoration-none">
                                            Ordine #{{ $order->id }}
                                        </a>
                                    </h5>

                                    <p class="card-text text-muted">
                                        <strong>Cliente:</strong> {{ $order->customer_name }}<br>
                                        <strong>Email:</strong> {{ $order->customer_email }}<br>
                                        <strong>Totale:</strong> €{{ number_format($order->total_amount, 2) }}
                                    </p>

                                    <!-- Order Products -->
                                    <div class="mb-3">
                                        <h6 class="fw-semibold">Prodotti:</h6>
                                        <ul class="list-unstyled mb-0">
                                            @foreach($order->products as $product)
                                                <li class="small text-muted">
                                                    {{ $product->name }} x{{ $product->pivot->quantity }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <!-- Order Actions -->
                                    <div class="mt-auto">
                                        <div class="btn-group w-100" role="group">
                                            <a href="{{ route('orders.show', $order) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye me-1"></i>
                                                Dettagli
                                            </a>
                                            <a href="{{ route('orders.edit', $order) }}" 
                                               class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-edit me-1"></i>
                                                Modifica
                                            </a>
                                            @if($order->status === 'processing')
                                                <button type="button" class="btn btn-outline-success btn-sm" 
                                                        onclick="completeOrder({{ $order->id }})">
                                                    <i class="fas fa-check me-1"></i>
                                                    Completa
                                                </button>
                                            @endif
                                            @if(in_array($order->status, ['pending', 'processing']))
                                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                                        onclick="cancelOrder({{ $order->id }})">
                                                    <i class="fas fa-times me-1"></i>
                                                    Cancella
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
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Nessun ordine trovato</h4>
                    <p class="text-muted mb-4">
                        Inizia a creare il tuo primo ordine utilizzando il pattern Unit of Work!
                    </p>
                    <a href="{{ route('orders.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Crea Primo Ordine
                    </a>
                </div>
            @endif

            <!-- Pattern Architecture Info -->
            <div class="card mt-5">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-sitemap me-2"></i>
                        Architettura del Pattern Unit of Work
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6 class="fw-semibold">Begin Transaction</h6>
                            <p class="text-muted small">
                                Inizia una nuova transazione e registra le operazioni.
                            </p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="fw-semibold">Register Operations</h6>
                            <p class="text-muted small">
                                Registra inserimenti, aggiornamenti ed eliminazioni.
                            </p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="fw-semibold">Execute Operations</h6>
                            <p class="text-muted small">
                                Esegue tutte le operazioni in batch quando necessario.
                            </p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="fw-semibold">Commit/Rollback</h6>
                            <p class="text-muted small">
                                Conferma o annulla tutte le operazioni insieme.
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <strong>Flusso:</strong> 
                        <span class="text-muted">Begin → Register → Execute → Commit/Rollback</span>
                    </div>
                </div>
            </div>

            <!-- Unit of Work Benefits -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Vantaggi del Pattern Unit of Work
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i> Atomicità delle operazioni</li>
                                <li><i class="fas fa-check text-success me-2"></i> Consistenza dei dati</li>
                                <li><i class="fas fa-check text-success me-2"></i> Isolamento delle transazioni</li>
                                <li><i class="fas fa-check text-success me-2"></i> Rollback automatico</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i> Performance ottimizzata</li>
                                <li><i class="fas fa-check text-success me-2"></i> Gestione concorrenza</li>
                                <li><i class="fas fa-check text-success me-2"></i> Operazioni batch</li>
                                <li><i class="fas fa-check text-success me-2"></i> Durabilità dei dati</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript per le azioni -->
<script>
function completeOrder(id) {
    if (confirm('Sei sicuro di voler completare questo ordine?')) {
        fetch(`/orders/${id}/complete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Errore: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Errore durante il completamento dell\'ordine');
        });
    }
}

function cancelOrder(id) {
    if (confirm('Sei sicuro di voler cancellare questo ordine?')) {
        fetch(`/orders/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Errore: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Errore durante la cancellazione dell\'ordine');
        });
    }
}

// Helper function per i colori degli stati
function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'processing': 'info',
        'completed': 'success',
        'cancelled': 'danger'
    };
    return colors[status] || 'secondary';
}
</script>
@endsection
