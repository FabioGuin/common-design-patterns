<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adapter Pattern - Sistema Pagamenti</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
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
        .provider-selector {
            margin-bottom: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input, select {
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
        .current-provider {
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Adapter Pattern - Sistema Pagamenti</h1>
        
        <div class="provider-selector">
            <h3>Provider Corrente: <span class="current-provider" id="currentProvider">{{ ucfirst($currentProvider) }}</span></h3>
            <div class="form-group">
                <label for="providerSelect">Cambia Provider:</label>
                <select id="providerSelect">
                    @foreach($availableProviders as $key => $name)
                        <option value="{{ $key }}" {{ $key === $currentProvider ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <button onclick="switchProvider()">Cambia Provider</button>
            </div>
        </div>

        <div class="form-group">
            <label for="amount">Importo:</label>
            <input type="number" id="amount" step="0.01" min="0.01" value="10.00" placeholder="0.00">
        </div>

        <div class="form-group">
            <label for="currency">Valuta:</label>
            <select id="currency">
                <option value="USD">USD - Dollaro Americano</option>
                <option value="EUR">EUR - Euro</option>
                <option value="GBP">GBP - Sterlina Britannica</option>
            </select>
        </div>

        <div>
            <button onclick="processPayment()">Processa Pagamento</button>
            <button onclick="getPaymentStatus()">Verifica Stato</button>
            <button onclick="refundPayment()">Rimborsa</button>
        </div>

        <div class="form-group">
            <label for="paymentId">Payment ID (per verifiche e rimborsi):</label>
            <input type="text" id="paymentId" placeholder="Inserisci l'ID del pagamento">
        </div>

        <div id="result"></div>
    </div>

    <script>
        // Configura CSRF token per tutte le richieste AJAX
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        function showResult(data, type = 'info') {
            const resultDiv = document.getElementById('result');
            resultDiv.className = `result ${type}`;
            resultDiv.textContent = JSON.stringify(data, null, 2);
        }

        function switchProvider() {
            const provider = document.getElementById('providerSelect').value;
            
            fetch('/payments/switch-provider', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ provider: provider })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('currentProvider').textContent = data.provider.charAt(0).toUpperCase() + data.provider.slice(1);
                    showResult(data, 'success');
                } else {
                    showResult(data, 'error');
                }
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }

        function processPayment() {
            const amount = document.getElementById('amount').value;
            const currency = document.getElementById('currency').value;
            const provider = document.getElementById('providerSelect').value;

            if (!amount || amount <= 0) {
                showResult({ error: 'Inserisci un importo valido' }, 'error');
                return;
            }

            fetch('/payments/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    amount: parseFloat(amount),
                    currency: currency,
                    provider: provider
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('paymentId').value = data.payment_id;
                    showResult(data, 'success');
                } else {
                    showResult(data, 'error');
                }
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }

        function getPaymentStatus() {
            const paymentId = document.getElementById('paymentId').value;
            const provider = document.getElementById('providerSelect').value;

            if (!paymentId) {
                showResult({ error: 'Inserisci un Payment ID' }, 'error');
                return;
            }

            fetch('/payments/status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    payment_id: paymentId,
                    provider: provider
                })
            })
            .then(response => response.json())
            .then(data => {
                showResult(data, data.success ? 'success' : 'error');
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }

        function refundPayment() {
            const paymentId = document.getElementById('paymentId').value;
            const amount = document.getElementById('amount').value;
            const provider = document.getElementById('providerSelect').value;

            if (!paymentId) {
                showResult({ error: 'Inserisci un Payment ID' }, 'error');
                return;
            }

            fetch('/payments/refund', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    payment_id: paymentId,
                    amount: amount ? parseFloat(amount) : null,
                    provider: provider
                })
            })
            .then(response => response.json())
            .then(data => {
                showResult(data, data.success ? 'success' : 'error');
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }
    </script>
</body>
</html>
