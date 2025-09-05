<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event-Driven Architecture - Esempio</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .pattern-info {
            background: #e8f4f8;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        .stat-card.events { border-left-color: #28a745; }
        .stat-card.orders { border-left-color: #ffc107; }
        .stat-card.listeners { border-left-color: #17a2b8; }
        .stat-card.types { border-left-color: #dc3545; }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            color: #6c757d;
            margin-top: 5px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #34495e;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        button:hover {
            background: #0056b3;
        }
        button.secondary {
            background: #6c757d;
        }
        button.secondary:hover {
            background: #545b62;
        }
        button.success {
            background: #28a745;
        }
        button.success:hover {
            background: #1e7e34;
        }
        button.danger {
            background: #dc3545;
        }
        button.danger:hover {
            background: #c82333;
        }
        .events-log {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            max-height: 400px;
            overflow-y: auto;
        }
        .event-item {
            margin-bottom: 10px;
            padding: 8px;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
        }
        .event-OrderCreated {
            border-left: 4px solid #28a745;
        }
        .event-OrderUpdated {
            border-left: 4px solid #ffc107;
        }
        .event-PaymentProcessed {
            border-left: 4px solid #17a2b8;
        }
        .event-InventoryUpdated {
            border-left: 4px solid #dc3545;
        }
        .status {
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-weight: bold;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }
        .tab.active {
            border-bottom-color: #007bff;
            color: #007bff;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .events-table, .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .events-table th,
        .events-table td,
        .orders-table th,
        .orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .events-table th,
        .orders-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-shipped { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Event-Driven Architecture - Esempio</h1>
        
        <div class="pattern-info">
            <h3>Come funziona l'Event-Driven Architecture</h3>
            <p>L'Event-Driven Architecture promuove la comunicazione asincrona tra componenti attraverso eventi. Quando accade qualcosa di importante, viene pubblicato un evento che altri componenti possono ascoltare e reagire di conseguenza, creando sistemi disaccoppiati e scalabili.</p>
        </div>

        <!-- Statistiche -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card events">
                <div class="stat-number" id="totalEvents">{{ $stats['total_events'] ?? 0 }}</div>
                <div class="stat-label">Eventi Totali</div>
            </div>
            <div class="stat-card orders">
                <div class="stat-number" id="totalOrders">{{ $orders->count() ?? 0 }}</div>
                <div class="stat-label">Ordini</div>
            </div>
            <div class="stat-card listeners">
                <div class="stat-number" id="totalListeners">4</div>
                <div class="stat-label">Listener Attivi</div>
            </div>
            <div class="stat-card types">
                <div class="stat-number" id="eventTypes">{{ count($stats['events_by_type'] ?? []) }}</div>
                <div class="stat-label">Tipi Eventi</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <div class="tab active" onclick="showTab('orders')">Gestione Ordini</div>
            <div class="tab" onclick="showTab('events')">Eventi</div>
            <div class="tab" onclick="showTab('replay')">Replay</div>
            <div class="tab" onclick="showTab('subscriptions')">Sottoscrizioni</div>
        </div>

        <!-- Tab Gestione Ordini -->
        <div id="orders" class="tab-content active">
            <h3>Gestione Ordini</h3>
            <form id="orderForm">
                <div class="form-group">
                    <label for="customer_name">Nome Cliente:</label>
                    <input type="text" id="customer_name" name="customer_name" value="Mario Rossi" required>
                </div>
                
                <div class="form-group">
                    <label for="customer_email">Email Cliente:</label>
                    <input type="email" id="customer_email" name="customer_email" value="mario.rossi@example.com" required>
                </div>
                
                <div class="form-group">
                    <label for="amount">Importo:</label>
                    <input type="number" id="amount" name="amount" value="100.50" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="notes">Note:</label>
                    <textarea id="notes" name="notes" rows="3">Ordine di test per Event-Driven Architecture</textarea>
                </div>
                
                <button type="submit">Crea Ordine</button>
                <button type="button" onclick="refreshStats()">Aggiorna Statistiche</button>
                <button type="button" onclick="testEventBus()" class="secondary">Test Event Bus</button>
            </form>

            <h4>Ordini Recenti</h4>
            <table class="orders-table" id="ordersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Importo</th>
                        <th>Status</th>
                        <th>Creato</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>€ {{ number_format($order->amount, 2) }}</td>
                        <td><span class="status-badge status-{{ $order->status }}">{{ $order->status }}</span></td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <button onclick="updateOrder({{ $order->id }})" class="success">Aggiorna</button>
                            <button onclick="processPayment({{ $order->id }})" class="secondary">Pagamento</button>
                            <button onclick="viewOrderEvents({{ $order->id }})" class="secondary">Eventi</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Tab Eventi -->
        <div id="events" class="tab-content">
            <h3>Eventi Recenti</h3>
            <button onclick="loadEvents()" class="success">Carica Eventi</button>
            <button onclick="refreshStats()" class="secondary">Aggiorna</button>
            
            <table class="events-table" id="eventsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Aggregato</th>
                        <th>Versione</th>
                        <th>Occorso</th>
                    </tr>
                </thead>
                <tbody id="eventsTableBody">
                    <!-- Popolato via JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Tab Replay -->
        <div id="replay" class="tab-content">
            <h3>Replay Eventi</h3>
            <form id="replayForm">
                <div class="form-group">
                    <label for="from_date">Data Inizio:</label>
                    <input type="date" id="from_date" name="from_date" value="{{ now()->subDays(7)->format('Y-m-d') }}" required>
                </div>
                
                <div class="form-group">
                    <label for="to_date">Data Fine (opzionale):</label>
                    <input type="date" id="to_date" name="to_date">
                </div>
                
                <div class="form-group">
                    <label for="event_type">Tipo Evento (opzionale):</label>
                    <select id="event_type" name="event_type">
                        <option value="">Tutti</option>
                        <option value="OrderCreated">OrderCreated</option>
                        <option value="OrderUpdated">OrderUpdated</option>
                        <option value="PaymentProcessed">PaymentProcessed</option>
                    </select>
                </div>
                
                <button type="submit">Avvia Replay</button>
                <button type="button" onclick="cleanupEvents()" class="danger">Pulisci Eventi Vecchi</button>
            </form>
        </div>

        <!-- Tab Sottoscrizioni -->
        <div id="subscriptions" class="tab-content">
            <h3>Sottoscrizioni Event Bus</h3>
            <button onclick="loadSubscriptions()" class="success">Carica Sottoscrizioni</button>
            
            <div id="subscriptionsList">
                <!-- Popolato via JavaScript -->
            </div>
        </div>

        <div id="status"></div>
        
        <div class="events-log">
            <h3>Log Eventi in Tempo Reale</h3>
            <div id="eventsList">
                @foreach($recentEvents as $event)
                <div class="event-item event-{{ $event->event_type }}">
                    <strong>{{ $event->occurred_at->format('H:i:s') }}</strong> - {{ $event->event_type }}
                    <br><small>Aggregato: {{ $event->aggregate_id }} | Versione: {{ $event->version }}</small>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        // Carica dati all'avvio
        document.addEventListener('DOMContentLoaded', function() {
            refreshStats();
        });

        // Form per creare ordini
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            createOrder(data);
        });

        // Form per replay
        document.getElementById('replayForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            replayEvents(data);
        });

        function createOrder(data) {
            const statusDiv = document.getElementById('status');
            statusDiv.innerHTML = '<div class="status status-info">Creazione ordine in corso...</div>';
            
            fetch('/event-driven/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.innerHTML = '<div class="status status-success">Ordine creato e evento pubblicato! ID: ' + data.order.id + '</div>';
                    loadOrders();
                    refreshStats();
                } else {
                    statusDiv.innerHTML = '<div class="status status-error">Errore: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                statusDiv.innerHTML = '<div class="status status-error">Errore: ' + error.message + '</div>';
            });
        }

        function loadOrders() {
            fetch('/event-driven/orders')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('ordersTableBody');
                tbody.innerHTML = '';
                
                data.orders.forEach(order => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${order.id}</td>
                        <td>${order.customer_name}</td>
                        <td>€ ${parseFloat(order.amount).toFixed(2)}</td>
                        <td><span class="status-badge status-${order.status}">${order.status}</span></td>
                        <td>${new Date(order.created_at).toLocaleString()}</td>
                        <td>
                            <button onclick="updateOrder(${order.id})" class="success">Aggiorna</button>
                            <button onclick="processPayment(${order.id})" class="secondary">Pagamento</button>
                            <button onclick="viewOrderEvents(${order.id})" class="secondary">Eventi</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Errore nel caricamento ordini:', error);
            });
        }

        function loadEvents() {
            fetch('/event-driven/events')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('eventsTableBody');
                tbody.innerHTML = '';
                
                data.events.forEach(event => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${event.id}</td>
                        <td>${event.event_type}</td>
                        <td>${event.aggregate_id || 'N/A'}</td>
                        <td>${event.version}</td>
                        <td>${new Date(event.occurred_at).toLocaleString()}</td>
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Errore nel caricamento eventi:', error);
            });
        }

        function loadSubscriptions() {
            fetch('/event-driven/subscriptions')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('subscriptionsList');
                container.innerHTML = '<h4>Sottoscrizioni Event Bus</h4>';
                
                Object.entries(data.subscriptions).forEach(([eventType, listeners]) => {
                    const div = document.createElement('div');
                    div.innerHTML = `
                        <h5>${eventType}</h5>
                        <ul>
                            ${listeners.map(listener => `<li>${listener}</li>`).join('')}
                        </ul>
                    `;
                    container.appendChild(div);
                });
            })
            .catch(error => {
                console.error('Errore nel caricamento sottoscrizioni:', error);
            });
        }

        function refreshStats() {
            fetch('/event-driven/stats')
            .then(response => response.json())
            .then(data => {
                document.getElementById('totalEvents').textContent = data.event_store_stats.total_events;
                document.getElementById('eventTypes').textContent = Object.keys(data.event_store_stats.events_by_type).length;
            })
            .catch(error => {
                console.error('Errore nel refresh statistiche:', error);
            });
        }

        function updateOrder(orderId) {
            const newStatus = prompt('Nuovo status (pending, processing, shipped, completed, cancelled):');
            if (newStatus) {
                fetch(`/event-driven/orders/${orderId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadOrders();
                        refreshStats();
                    } else {
                        alert('Errore: ' + data.message);
                    }
                });
            }
        }

        function processPayment(orderId) {
            const amount = prompt('Importo pagamento:');
            const method = prompt('Metodo pagamento (credit_card, paypal, bank_transfer):');
            
            if (amount && method) {
                fetch(`/event-driven/orders/${orderId}/payment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ amount: parseFloat(amount), payment_method: method })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pagamento processato e evento pubblicato!');
                        refreshStats();
                    } else {
                        alert('Errore: ' + data.message);
                    }
                });
            }
        }

        function viewOrderEvents(orderId) {
            fetch(`/event-driven/orders/${orderId}/events`)
            .then(response => response.json())
            .then(data => {
                const eventsList = document.getElementById('eventsList');
                eventsList.innerHTML = '<h4>Eventi per Ordine ' + orderId + '</h4>';
                
                data.events.forEach(event => {
                    const eventDiv = document.createElement('div');
                    eventDiv.className = 'event-item event-' + event.event_type;
                    eventDiv.innerHTML = `
                        <strong>${new Date(event.occurred_at).toLocaleTimeString()}</strong> - ${event.event_type}
                        <br><small>Versione: ${event.version}</small>
                    `;
                    eventsList.appendChild(eventDiv);
                });
            })
            .catch(error => {
                console.error('Errore nel caricamento eventi ordine:', error);
            });
        }

        function replayEvents(data) {
            const statusDiv = document.getElementById('status');
            statusDiv.innerHTML = '<div class="status status-info">Replay eventi in corso...</div>';
            
            fetch('/event-driven/replay', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.innerHTML = '<div class="status status-success">' + data.message + '</div>';
                } else {
                    statusDiv.innerHTML = '<div class="status status-error">Errore: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                statusDiv.innerHTML = '<div class="status status-error">Errore: ' + error.message + '</div>';
            });
        }

        function testEventBus() {
            const statusDiv = document.getElementById('status');
            statusDiv.innerHTML = '<div class="status status-info">Test event bus in corso...</div>';
            
            fetch('/event-driven/test-event-bus')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.innerHTML = '<div class="status status-success">Event bus OK!</div>';
                } else {
                    statusDiv.innerHTML = '<div class="status status-error">Event bus fallito: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                statusDiv.innerHTML = '<div class="status status-error">Errore: ' + error.message + '</div>';
            });
        }

        function cleanupEvents() {
            if (confirm('Sei sicuro di voler pulire gli eventi vecchi?')) {
                const statusDiv = document.getElementById('status');
                statusDiv.innerHTML = '<div class="status status-info">Pulizia eventi in corso...</div>';
                
                fetch('/event-driven/cleanup', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        statusDiv.innerHTML = '<div class="status status-success">' + data.message + '</div>';
                        refreshStats();
                    } else {
                        statusDiv.innerHTML = '<div class="status status-error">Errore: ' + data.message + '</div>';
                    }
                });
            }
        }

        function showTab(tabName) {
            // Nascondi tutti i tab
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Mostra il tab selezionato
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');

            // Carica dati specifici per il tab
            if (tabName === 'events') {
                loadEvents();
            } else if (tabName === 'subscriptions') {
                loadSubscriptions();
            }
        }

        // Auto-refresh ogni 30 secondi
        setInterval(() => {
            refreshStats();
        }, 30000);
    </script>
</body>
</html>
