<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saga Choreography Pattern - Esempio</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #34495e;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        button:hover {
            background: #2980b9;
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
        .event-success {
            border-left: 4px solid #27ae60;
        }
        .event-error {
            border-left: 4px solid #e74c3c;
        }
        .event-info {
            border-left: 4px solid #3498db;
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
        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Saga Choreography Pattern - Esempio</h1>
        
        <div class="pattern-info">
            <h3>Come funziona il Saga Choreography Pattern</h3>
            <p>Questo pattern gestisce transazioni distribuite attraverso eventi. Ogni servizio reagisce agli eventi degli altri servizi senza un coordinatore centrale. Se un passaggio fallisce, gli eventi di compensazione vengono inviati per annullare le operazioni precedenti.</p>
        </div>

        <form id="sagaForm">
            <div class="form-group">
                <label for="user_id">ID Utente:</label>
                <input type="number" id="user_id" name="user_id" value="1" required>
            </div>
            
            <div class="form-group">
                <label for="product_id">ID Prodotto:</label>
                <input type="number" id="product_id" name="product_id" value="1" required>
            </div>
            
            <div class="form-group">
                <label for="quantity">Quantità:</label>
                <input type="number" id="quantity" name="quantity" value="2" required>
            </div>
            
            <div class="form-group">
                <label for="amount">Importo:</label>
                <input type="number" id="amount" name="amount" value="100.00" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="scenario">Scenario di Test:</label>
                <select id="scenario" name="scenario">
                    <option value="success">Successo Completo</option>
                    <option value="user_validation_fail">Fallimento Validazione Utente</option>
                    <option value="inventory_fail">Fallimento Inventario</option>
                    <option value="payment_fail">Fallimento Pagamento</option>
                </select>
            </div>
            
            <button type="submit">Avvia Saga Choreography</button>
            <button type="button" onclick="clearLog()">Pulisci Log</button>
        </form>

        <div id="status"></div>
        
        <div class="events-log">
            <h3>Log Eventi Saga</h3>
            <div id="eventsList"></div>
        </div>
    </div>

    <script>
        document.getElementById('sagaForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            startSaga(data);
        });

        function startSaga(data) {
            const statusDiv = document.getElementById('status');
            const eventsList = document.getElementById('eventsList');
            
            statusDiv.innerHTML = '<div class="status status-processing">Avvio Saga Choreography...</div>';
            eventsList.innerHTML = '';
            
            fetch('/saga-choreography/start', {
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
                    statusDiv.innerHTML = '<div class="status status-success">Saga avviata con successo! ID: ' + data.saga_id + '</div>';
                    pollEvents(data.saga_id);
                } else {
                    statusDiv.innerHTML = '<div class="status status-error">Errore: ' + data.message + '</div>';
                }
            })
            .catch(error => {
                statusDiv.innerHTML = '<div class="status status-error">Errore: ' + error.message + '</div>';
            });
        }

        function pollEvents(sagaId) {
            const eventsList = document.getElementById('eventsList');
            
            const interval = setInterval(() => {
                fetch(`/saga-choreography/events/${sagaId}`)
                .then(response => response.json())
                .then(data => {
                    eventsList.innerHTML = '';
                    data.events.forEach(event => {
                        const eventDiv = document.createElement('div');
                        eventDiv.className = 'event-item event-' + (event.status === 'success' ? 'success' : event.status === 'error' ? 'error' : 'info');
                        eventDiv.innerHTML = `
                            <strong>${event.timestamp}</strong> - ${event.event_type}<br>
                            <small>${event.description}</small>
                        `;
                        eventsList.appendChild(eventDiv);
                    });
                    
                    if (data.completed) {
                        clearInterval(interval);
                        const statusDiv = document.getElementById('status');
                        if (data.success) {
                            statusDiv.innerHTML = '<div class="status status-success">Saga completata con successo!</div>';
                        } else {
                            statusDiv.innerHTML = '<div class="status status-error">Saga fallita - Compensazione in corso...</div>';
                        }
                    }
                })
                .catch(error => {
                    console.error('Errore nel polling:', error);
                    clearInterval(interval);
                });
            }, 1000);
        }

        function clearLog() {
            document.getElementById('eventsList').innerHTML = '';
            document.getElementById('status').innerHTML = '';
        }
    </script>
</body>
</html>
