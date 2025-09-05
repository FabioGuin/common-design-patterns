<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bridge Pattern - Sistema Notifiche</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
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
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            flex: 1;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
            resize: vertical;
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
        button:disabled {
            background: #6c757d;
            cursor: not-allowed;
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
        <h1>Bridge Pattern - Sistema Notifiche</h1>
        
        <div class="examples">
            <h3>Esempi Rapidi:</h3>
            <button class="example-button" onclick="loadExample('email-html')">Email + HTML</button>
            <button class="example-button" onclick="loadExample('sms-text')">SMS + Text</button>
            <button class="example-button" onclick="loadExample('push-json')">Push + JSON</button>
            <button class="example-button" onclick="loadExample('email-text')">Email + Text</button>
            <button class="example-button" onclick="loadExample('sms-json')">SMS + JSON</button>
        </div>

        <form id="notificationForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="channel">Canale:</label>
                    <select id="channel" required>
                        @foreach($availableChannels as $key => $name)
                            <option value="{{ $key }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="formatter">Formattatore:</label>
                    <select id="formatter" required>
                        @foreach($availableFormatters as $key => $name)
                            <option value="{{ $key }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="message">Messaggio:</label>
                <textarea id="message" placeholder="Inserisci il messaggio da inviare..." required></textarea>
            </div>

            <div class="form-group">
                <label for="title">Titolo (opzionale):</label>
                <input type="text" id="title" placeholder="Titolo della notifica">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email (per notifiche email):</label>
                    <input type="email" id="email" placeholder="user@example.com">
                </div>
                <div class="form-group">
                    <label for="phone">Telefono (per SMS):</label>
                    <input type="text" id="phone" placeholder="+1234567890">
                </div>
            </div>

            <div class="form-group">
                <label for="device_token">Device Token (per push):</label>
                <input type="text" id="device_token" placeholder="device_token_123">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="action_url">URL Azione (opzionale):</label>
                    <input type="url" id="action_url" placeholder="https://example.com/action">
                </div>
                <div class="form-group">
                    <label for="action_text">Testo Azione (opzionale):</label>
                    <input type="text" id="action_text" placeholder="Visualizza">
                </div>
            </div>

            <button type="submit">Invia Notifica</button>
        </form>

        <div id="result"></div>
    </div>

    <script>
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const examples = {
            'email-html': {
                channel: 'email',
                formatter: 'html',
                message: 'Benvenuto nella nostra applicazione! Il tuo account è stato creato con successo.',
                title: 'Account Creato',
                email: 'user@example.com',
                action_url: 'https://example.com/dashboard',
                action_text: 'Vai alla Dashboard'
            },
            'sms-text': {
                channel: 'sms',
                formatter: 'text',
                message: 'Il tuo ordine #12345 è stato spedito. Codice tracking: ABC123',
                title: 'Ordine Spedito',
                phone: '+1234567890'
            },
            'push-json': {
                channel: 'push',
                formatter: 'json',
                message: 'Hai ricevuto un nuovo messaggio da Mario Rossi',
                title: 'Nuovo Messaggio',
                device_token: 'device_token_123',
                action_url: 'https://example.com/messages',
                action_text: 'Apri Chat'
            },
            'email-text': {
                channel: 'email',
                formatter: 'text',
                message: 'Il tuo pagamento di €50.00 è stato elaborato con successo.',
                title: 'Pagamento Confermato',
                email: 'user@example.com'
            },
            'sms-json': {
                channel: 'sms',
                formatter: 'json',
                message: 'Codice di verifica: 123456. Valido per 5 minuti.',
                title: 'Codice di Verifica',
                phone: '+1234567890'
            }
        };

        function loadExample(exampleKey) {
            const example = examples[exampleKey];
            if (example) {
                Object.keys(example).forEach(key => {
                    const element = document.getElementById(key);
                    if (element) {
                        element.value = example[key];
                    }
                });
            }
        }

        function showResult(data, type = 'info') {
            const resultDiv = document.getElementById('result');
            resultDiv.className = `result ${type}`;
            resultDiv.textContent = JSON.stringify(data, null, 2);
        }

        document.getElementById('notificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
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
        });
    </script>
</body>
</html>
