<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outbox Pattern - Esempio</title>
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
        .stat-card.pending { border-left-color: #ffc107; }
        .stat-card.processing { border-left-color: #17a2b8; }
        .stat-card.published { border-left-color: #28a745; }
        .stat-card.failed { border-left-color: #dc3545; }
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
        .event-pending {
            border-left: 4px solid #ffc107;
        }
        .event-processing {
            border-left: 4px solid #17a2b8;
        }
        .event-published {
            border-left: 4px solid #28a745;
        }
        .event-failed {
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
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .orders-table th,
        .orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
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
        <h1>Outbox Pattern - Esempio</h1>
        
        <div class="pattern-info">
            <h3>Come funziona l'Outbox Pattern</h3>
            <p>L'Outbox Pattern garantisce la pubblicazione affidabile di eventi in sistemi distribuiti. Quando aggiorni il database, l'evento viene inserito in una tabella "outbox" nella stessa transazione. Un processo separato legge dall'outbox e pubblica gli eventi, gestendo automaticamente i retry in caso di fallimento.</p>
        </div>

        <!-- Statistiche -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card pending">
                <div class="stat-number" id="pendingCount">{{ $stats['pending'] ?? 0 }}</div>
                <div class="stat-label">Eventi Pendenti</div>
            </div>
            <div class="stat-card processing">
                <div class="stat-number" id="processingCount">{{ $stats['processing'] ?? 0 }}</div>
                <div class="stat-label">In Processing</div>
            </div>
            <div class="stat-card published">
                <div class="stat-number" id="publishedCount">{{ $stats['published'] ?? 0 }}</div>
                <div class="stat-label">Pubblicati</div>
            </div>
            <div class="stat-card failed">
                <div class="stat-number" id="failedCount">{{ $stats['failed'] ?? 0 }}</div>
                <div class="stat-label">Falliti</div>
            </div>
        </div>

        <!-- Form per creare ordini -->
        <div class="container">
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
                    <textarea id="notes" name="notes" rows="3">Ordine di test per Outbox Pattern</textarea>
                </div>
                
                <button type="submit">Crea Ordine</button>
                <button type="button" onclick="refreshStats()">Aggiorna Statistiche</button>
                <button type="button" onclick="processEvents()" class="success">Processa Eventi</button>
                <button type="button" onclick="testConnection()" class="secondary">Test Connessione</button>
            </form>
        </div>

        <!-- Lista ordini -->
        <div class="container">
            <h3>Ordini Recenti</h3>
            <table class="orders-table" id="ordersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Importo</th>
                        <th>Status</th>
                        <th>Eventi</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    <!-- Popolato via JavaScript -->
                </tbody>
            </table>
        </div>

        <div id="status"></div>
        
        <div class="events-log">
            <h3>Log Eventi Outbox</h3>
            <div id="eventsList">
                @foreach($recentEvents as $event)
                <div class="event-item event-{{ $event->status }}">
                    <strong>{{ $event->created_at->format('H:i:s') }}</strong> - {{ $event->event_type }} ({{ $event->status }})
                    <br><small>{{ $event->description ?? 'Nessuna descrizione' }}</small>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        // Carica ordini all'avvio
        document.addEventListener('DOMContentLoaded', function() {
            loadOrders();
            refreshStats();
        });

        // Form per creare ordini
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            createOrder(data);
        });

        function createOrder(data) {
            const statusDiv = document.getElementById('status');
            statusDiv.innerHTML = '<div class="status status-info">Creazione ordine in corso...</div>';
            
            fetch('/outbox/orders', {
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
                    statusDiv.innerHTML = '<div class="status status-success">Ordine creato con successo! ID: ' + data.order.id + '</div>';
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
            fetch('/outbox/orders')
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
                        <td>${order.events_count || 0}</td>
                        <td>
                            <button onclick="viewOrderEvents(${order.id})" class="secondary">Eventi</button>
                            <button onclick="updateOrder(${order.id})" class="success">Aggiorna</button>
                            <button onclick="deleteOrder(${order.id})" class="danger">Elimina</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Errore nel caricamento ordini:', error);
            });
        }

        function refreshStats() {
            fetch('/outbox/status')
            .then(response => response.json())
            .then(data => {
                document.getElementById('pendingCount').textContent = data.stats.pending;
                document.getElementById('processingCount').textContent = data.stats.processing;
                document.getElementById('publishedCount').textContent = data.stats.published;
                document.getElementById('failedCount').textContent = data.stats.failed;
            })
            .catch(error => {
                console.error('Errore nel refresh statistiche:', error);
            });
        }

        function processEvents() {
            const statusDiv = document.getElementById('status');
            statusDiv.innerHTML = '<div class="status status-info">Processing eventi in corso...</div>';
            
            fetch('/outbox/process', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.innerHTML = '<div class="status status-success">Processing avviato con successo!</div>';
                    setTimeout(() => {
                        refreshStats();
                        loadOrders();
                    }, 2000);
                } else {
                    statusDiv.innerHTML = '<div class="status status-error">Errore: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                statusDiv.innerHTML = '<div class="status status-error">Errore: ' + error.message + '</div>';
            });
        }

        function testConnection() {
            const statusDiv = document.getElementById('status');
            statusDiv.innerHTML = '<div class="status status-info">Test connessione in corso...</div>';
            
            fetch('/outbox/test-connection')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.innerHTML = '<div class="status status-success">Connessione OK!</div>';
                } else {
                    statusDiv.innerHTML = '<div class="status status-error">Connessione fallita: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                statusDiv.innerHTML = '<div class="status status-error">Errore: ' + error.message + '</div>';
            });
        }

        function viewOrderEvents(orderId) {
            fetch(`/outbox/orders/${orderId}/events`)
            .then(response => response.json())
            .then(data => {
                const eventsList = document.getElementById('eventsList');
                eventsList.innerHTML = '<h4>Eventi per Ordine ' + orderId + '</h4>';
                
                data.events.forEach(event => {
                    const eventDiv = document.createElement('div');
                    eventDiv.className = 'event-item event-' + event.status;
                    eventDiv.innerHTML = `
                        <strong>${new Date(event.created_at).toLocaleTimeString()}</strong> - ${event.event_type} (${event.status})
                        <br><small>Retry: ${event.retry_count}</small>
                    `;
                    eventsList.appendChild(eventDiv);
                });
            })
            .catch(error => {
                console.error('Errore nel caricamento eventi:', error);
            });
        }

        function updateOrder(orderId) {
            const newStatus = prompt('Nuovo status (pending, processing, shipped, completed, cancelled):');
            if (newStatus) {
                fetch(`/outbox/orders/${orderId}`, {
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

        function deleteOrder(orderId) {
            if (confirm('Sei sicuro di voler eliminare questo ordine?')) {
                fetch(`/outbox/orders/${orderId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
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

        // Auto-refresh ogni 30 secondi
        setInterval(() => {
            refreshStats();
        }, 30000);
    </script>
</body>
</html>
