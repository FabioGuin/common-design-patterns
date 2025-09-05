<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decorator Pattern - Sistema Notifiche</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            flex: 1;
        }
        .form-group.full-width {
            flex: 100%;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        textarea {
            height: 80px;
            resize: vertical;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .checkbox-item input[type="checkbox"] {
            width: auto;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        button:hover {
            background: #0056b3;
        }
        button.danger {
            background: #dc3545;
        }
        button.danger:hover {
            background: #c82333;
        }
        button.success {
            background: #28a745;
        }
        button.success:hover {
            background: #218838;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 14px;
            max-height: 400px;
            overflow-y: auto;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .decorator-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .decorator-info h4 {
            margin-top: 0;
            color: #495057;
        }
        .decorator-info .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .decorator-info .info-label {
            font-weight: bold;
            color: #6c757d;
        }
        .decorator-info .info-value {
            color: #495057;
        }
        .examples {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .example-button {
            background: #28a745;
            margin: 5px;
            padding: 8px 16px;
            font-size: 14px;
        }
        .example-button:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Decorator Pattern - Sistema Notifiche</h1>
        
        <div class="examples">
            <h3>Esempi Rapidi:</h3>
            <button class="example-button" onclick="loadExample('basic')">Notifica Base</button>
            <button class="example-button" onclick="loadExample('with-logging')">Con Logging</button>
            <button class="example-button" onclick="loadExample('with-caching')">Con Caching</button>
            <button class="example-button" onclick="loadExample('with-validation')">Con Validazione</button>
            <button class="example-button" onclick="loadExample('with-throttling')">Con Throttling</button>
            <button class="example-button" onclick="loadExample('all-decorators')">Tutti i Decoratori</button>
        </div>

        <div class="form-section">
            <h3>Configurazione Notifica</h3>
            
            <div class="form-group full-width">
                <label for="message">Messaggio:</label>
                <textarea id="message" placeholder="Inserisci il messaggio da inviare..." required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email (opzionale):</label>
                    <input type="email" id="email" placeholder="user@example.com">
                </div>
                <div class="form-group">
                    <label for="phone">Telefono (opzionale):</label>
                    <input type="text" id="phone" placeholder="+1234567890">
                </div>
                <div class="form-group">
                    <label for="priority">Priorità:</label>
                    <select id="priority">
                        <option value="normal">Normale</option>
                        <option value="low">Bassa</option>
                        <option value="high">Alta</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Decoratori da Applicare:</label>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="decorator-logging" value="logging">
                        <label for="decorator-logging">Logging</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="decorator-caching" value="caching">
                        <label for="decorator-caching">Caching</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="decorator-validation" value="validation">
                        <label for="decorator-validation">Validazione</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="decorator-throttling" value="throttling">
                        <label for="decorator-throttling">Throttling</label>
                    </div>
                </div>
            </div>

            <button onclick="sendNotification()">Invia Notifica</button>
            <button onclick="getDecoratorInfo()" class="success">Info Decoratori</button>
            <button onclick="resetThrottling()" class="danger">Reset Throttling</button>
        </div>

        <div id="decoratorInfo" class="decorator-info" style="display: none;">
            <h4>Informazioni Decoratori</h4>
            <div id="decoratorInfoContent"></div>
        </div>

        <div id="result"></div>
    </div>

    <script>
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const examples = {
            'basic': {
                message: 'Notifica base senza decoratori',
                decorators: []
            },
            'with-logging': {
                message: 'Notifica con logging abilitato',
                decorators: ['logging']
            },
            'with-caching': {
                message: 'Notifica con caching abilitato',
                decorators: ['caching']
            },
            'with-validation': {
                message: 'Notifica con validazione abilitata',
                decorators: ['validation'],
                email: 'user@example.com',
                priority: 'high'
            },
            'with-throttling': {
                message: 'Notifica con throttling abilitato',
                decorators: ['throttling']
            },
            'all-decorators': {
                message: 'Notifica con tutti i decoratori',
                decorators: ['logging', 'caching', 'validation', 'throttling'],
                email: 'user@example.com',
                priority: 'high'
            }
        };

        function loadExample(exampleKey) {
            const example = examples[exampleKey];
            if (example) {
                document.getElementById('message').value = example.message;
                document.getElementById('email').value = example.email || '';
                document.getElementById('phone').value = example.phone || '';
                document.getElementById('priority').value = example.priority || 'normal';
                
                // Reset checkboxes
                document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                // Set selected decorators
                example.decorators.forEach(decorator => {
                    const checkbox = document.getElementById(`decorator-${decorator}`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
        }

        function showResult(data, type = 'info') {
            const resultDiv = document.getElementById('result');
            resultDiv.className = `result ${type}`;
            resultDiv.textContent = JSON.stringify(data, null, 2);
        }

        function showDecoratorInfo(data) {
            const infoDiv = document.getElementById('decoratorInfo');
            const contentDiv = document.getElementById('decoratorInfoContent');
            
            if (data.success && data.info) {
                const info = data.info;
                let html = `
                    <div class="info-item">
                        <span class="info-label">Tipo:</span>
                        <span class="info-value">${info.type}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Descrizione:</span>
                        <span class="info-value">${info.description}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Costo:</span>
                        <span class="info-value">€${info.cost.toFixed(2)}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Disponibile:</span>
                        <span class="info-value">${info.available ? 'Sì' : 'No'}</span>
                    </div>
                `;
                
                if (info.throttle_info) {
                    html += `
                        <hr style="margin: 15px 0;">
                        <h5>Informazioni Throttling:</h5>
                        <div class="info-item">
                            <span class="info-label">Limite:</span>
                            <span class="info-value">${info.throttle_info.limit}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Finestra:</span>
                            <span class="info-value">${info.throttle_info.window}s</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Corrente:</span>
                            <span class="info-value">${info.throttle_info.current}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Rimanenti:</span>
                            <span class="info-value">${info.throttle_info.remaining}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Reset:</span>
                            <span class="info-value">${new Date(info.throttle_info.reset_at).toLocaleString()}</span>
                        </div>
                    `;
                }
                
                contentDiv.innerHTML = html;
                infoDiv.style.display = 'block';
            } else {
                infoDiv.style.display = 'none';
            }
        }

        function getSelectedDecorators() {
            const decorators = [];
            document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
                decorators.push(checkbox.value);
            });
            return decorators;
        }

        function sendNotification() {
            const data = {
                message: document.getElementById('message').value,
                decorators: getSelectedDecorators(),
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                priority: document.getElementById('priority').value
            };

            if (!data.message) {
                showResult({ error: 'Messaggio richiesto' }, 'error');
                return;
            }

            fetch('/notifications/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                showResult(data, data.success ? 'success' : 'error');
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }

        function getDecoratorInfo() {
            const data = {
                decorators: getSelectedDecorators()
            };

            fetch('/notifications/info', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                showDecoratorInfo(data);
                showResult(data, data.success ? 'success' : 'error');
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }

        function resetThrottling() {
            const data = {
                decorators: getSelectedDecorators()
            };

            fetch('/notifications/reset-throttling', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                showResult(data, data.success ? 'success' : 'error');
                if (data.success) {
                    getDecoratorInfo(); // Refresh info
                }
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }
    </script>
</body>
</html>
