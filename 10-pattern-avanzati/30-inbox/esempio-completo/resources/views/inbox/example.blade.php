<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox Pattern - Esempio</title>
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
        .stat-card.processed { border-left-color: #28a745; }
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
        .event-processed {
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
        .events-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .events-table th,
        .events-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .events-table th {
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
        .status-processed { background: #d4edda; color: #155724; }
        .status-failed { background: #f8d7da; color: #721c24; }
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Inbox Pattern - Esempio</h1>
        
        <div class="pattern-info">
            <h3>Come funziona l'Inbox Pattern</h3>
            <p>L'Inbox Pattern garantisce la ricezione affidabile di eventi in sistemi distribuiti. Ogni evento ha un ID univoco e viene processato solo una volta, garantendo idempotenza. Se un evento fallisce, viene ritentato automaticamente con backoff esponenziale.</p>
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
            <div class="stat-card processed">
                <div class="stat-number" id="processedCount">{{ $stats['processed'] ?? 0 }}</div>
                <div class="stat-label">Processati</div>
            </div>
            <div class="stat-card failed">
                <div class="stat-number" id="failedCount">{{ $stats['failed'] ?? 0 }}</div>
                <div class="stat-label">Falliti</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <div class="tab active" onclick="showTab('simulate')">Simula Eventi</div>
            <div class="tab" onclick="showTab('events')">Eventi</div>
            <div class="tab" onclick="showTab('orders')">Ordini</div>
        </div>

        <!-- Tab Simula Eventi -->
        <div id="simulate" class="tab-content active">
            <h3>Simula Arrivo Eventi</h3>
            <form id="eventForm">
                <div class="form-group">
                    <label for="event_type">Tipo Evento:</label>
                    <select id="event_type" name="event_type" required>
                        <option value="OrderCreated">OrderCreated</option>
                        <option value="OrderUpdated">OrderUpdated</option>
                        <option value="OrderDeleted">OrderDeleted</option>
                        <option value="PaymentProcessed">PaymentProcessed</option>
                        <option value="InventoryUpdated">InventoryUpdated</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="order_id">ID Ordine (opzionale):</label>
                    <input type="number" id="order_id" name="order_id" value="1">
                </div>
                
                <div class="form-group">
                    <label for="customer_name">Nome Cliente (opzionale):</label>
                    <input type="text" id="customer_name" name="customer_name" value="Mario Rossi">
                </div>
                
                <div class="form-group">
                    <label for="amount">Importo (opzionale):</label>
                    <input type="number" id="amount" name="amount" value="100.50" step="0.01">
                </div>
                
                <button type="submit">Simula Evento</button>
                <button type="button" onclick="refreshStats()">Aggiorna Statistiche</button>
                <button type="button" onclick="processEvents()" class="success">Processa Eventi</button>
                <button type="button" onclick="testConnection()" class="secondary">Test Connessione</button>
                <button type="button" onclick="restoreStuckEvents()" class="danger">Ripristina Stuck</button>
            </form>
        </div>

        <!-- Tab Eventi -->
        <div id="events" class="tab-content">
            <h3>Eventi Recenti</h3>
            <table class="events-table" id="eventsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event ID</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Retry</th>
                        <th>Creato</th>
                    </tr>
                </thead>
                <tbody id="eventsTableBody">
                    <!-- Popolato via JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Tab Ordini -->
        <div id="orders" class="tab-content">
            <h3>Ordini</h3>
            <table class="events-table" id="ordersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Importo</th>
                        <th>Status</th>
                        <th>Creato</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    <!-- Popolato via JavaScript -->
                </tbody>
            </table>
        </div>

        <div id="status"></div>
        
        <div class="events-log">
            <h3>Log Eventi Inbox</h3>
            <div id="eventsList">
                @foreach($recentEvents as $event)
                <div class="event-item event-{{ $event->status }}">
                    <strong>{{ $event->created_at->format('H:i:s') }}</strong> - {{ $event->event_type }} ({{ $event->status }})
                    <br><small>ID: {{ $event->event_id }} | Retry: {{ $event->retry_count }}</small>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        // Carica dati all'avvio
        document.addEventListener('DOMContentLoaded', function() {
            loadEvents();
            loadOrders();
            refreshStats();
        });

        // Form per simulare eventi
        document.getElementById('eventForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            simulateEvent(data);
        });

        function simulateEvent(data) {
            const statusDiv = document.getElementById('status');
            statusDiv.innerHTML = '<div class="status status-info">Simulazione evento in corso...</div>';
            
            fetch('/inbox/simulate', {
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
                    statusDiv.innerHTML = '<div class="status status-success">Evento simulato con successo! ID: ' + data.event_id + '</div>';
                    loadEvents();
                    refreshStats();
                } else {
                    statusDiv.innerHTML = '<div class="status status-error">Errore: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                statusDiv.innerHTML = '<div class="status status-error">Errore: ' + error.message + '</div>';
            });
        }

        function loadEvents() {
            fetch('/inbox/status')
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('eventsTableBody');
                tbody.innerHTML = '';
                
                data.recent_events.forEach(event => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${event.id}</td>
                        <td>${event.event_id}</td>
                        <td>${event.event_type}</td>
                        <td><span class="status-badge status-${event.status}">${event.status}</span></td>
                        <td>${event.retry_count}</td>
                        <td>${new Date(event.created_at).toLocaleString()}</td>
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Errore nel caricamento eventi:', error);
            });
        }

        function loadOrders() {
            fetch('/inbox/orders')
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
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Errore nel caricamento ordini:', error);
            });
        }

        function refreshStats() {
            fetch('/inbox/status')
            .then(response => response.json())
            .then(data => {
                document.getElementById('pendingCount').textContent = data.stats.pending;
                document.getElementById('processingCount').textContent = data.stats.processing;
                document.getElementById('processedCount').textContent = data.stats.processed;
                document.getElementById('failedCount').textContent = data.stats.failed;
            })
            .catch(error => {
                console.error('Errore nel refresh statistiche:', error);
            });
        }

        function processEvents() {
            const statusDiv = document.getElementById('status');
            statusDiv.innerHTML = '<div class="status status-info">Processing eventi in corso...</div>';
            
            fetch('/inbox/process', {
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
                        loadEvents();
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
            
            fetch('/inbox/test-connection')
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

        function restoreStuckEvents() {
            const statusDiv = document.getElementById('status');
            statusDiv.innerHTML = '<div class="status status-info">Ripristino eventi stuck in corso...</div>';
            
            fetch('/inbox/restore-stuck', {
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
                    loadEvents();
                } else {
                    statusDiv.innerHTML = '<div class="status status-error">Errore: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                statusDiv.innerHTML = '<div class="status status-error">Errore: ' + error.message + '</div>';
            });
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
            } else if (tabName === 'orders') {
                loadOrders();
            }
        }

        // Auto-refresh ogni 30 secondi
        setInterval(() => {
            refreshStats();
        }, 30000);
    </script>
</body>
</html>
