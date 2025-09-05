<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chain of Responsibility Pattern - Esempio Completo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .example-card {
            transition: transform 0.2s;
        }
        .example-card:hover {
            transform: translateY(-5px);
        }
        .chain-step {
            position: relative;
            margin-bottom: 1rem;
        }
        .chain-step::after {
            content: '→';
            position: absolute;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5rem;
            color: #007bff;
        }
        .chain-step:last-child::after {
            display: none;
        }
        .result-area {
            min-height: 200px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
        }
        .handler-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-link text-primary"></i>
                    Chain of Responsibility Pattern - Esempio Completo
                </h1>
                <p class="text-center text-muted mb-5">
                    Sistema di approvazione ordini multi-livello con gestori a cascata
                </p>
            </div>
        </div>

        <!-- Catena di Responsabilità -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-sitemap text-success"></i>
                    Catena di Approvazione
                </h2>
                <div class="d-flex flex-wrap justify-content-center align-items-center">
                    <div class="chain-step">
                        <div class="card text-center" style="width: 150px;">
                            <div class="card-body">
                                <i class="fas fa-check-circle text-primary mb-2"></i>
                                <h6>Validation</h6>
                                <small class="text-muted">Dati validi</small>
                            </div>
                        </div>
                    </div>
                    <div class="chain-step">
                        <div class="card text-center" style="width: 150px;">
                            <div class="card-body">
                                <i class="fas fa-credit-card text-warning mb-2"></i>
                                <h6>Credit Check</h6>
                                <small class="text-muted">Credito sufficiente</small>
                            </div>
                        </div>
                    </div>
                    <div class="chain-step">
                        <div class="card text-center" style="width: 150px;">
                            <div class="card-body">
                                <i class="fas fa-boxes text-info mb-2"></i>
                                <h6>Inventory</h6>
                                <small class="text-muted">Prodotti disponibili</small>
                            </div>
                        </div>
                    </div>
                    <div class="chain-step">
                        <div class="card text-center" style="width: 150px;">
                            <div class="card-body">
                                <i class="fas fa-user-tie text-success mb-2"></i>
                                <h6>Manager</h6>
                                <small class="text-muted">Approvazione manager</small>
                            </div>
                        </div>
                    </div>
                    <div class="chain-step">
                        <div class="card text-center" style="width: 150px;">
                            <div class="card-body">
                                <i class="fas fa-crown text-danger mb-2"></i>
                                <h6>Director</h6>
                                <small class="text-muted">Approvazione direttore</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Esempi di Handler -->
        <div class="row mb-5">
            @foreach($examples as $key => $example)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card example-card h-100">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            <i class="fas fa-{{ $key === 'validation' ? 'check-circle' : ($key === 'credit_check' ? 'credit-card' : ($key === 'inventory_check' ? 'boxes' : ($key === 'manager_approval' ? 'user-tie' : ($key === 'director_approval' ? 'crown' : 'cogs')))) }}"></i>
                            {{ $example['title'] }}
                        </h5>
                        <p class="card-text">{{ $example['description'] }}</p>
                        <ul class="list-unstyled">
                            @foreach($example['features'] as $feature)
                            <li><i class="fas fa-check text-success me-2"></i>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Test Interattivi -->
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-play-circle text-success"></i>
                    Test Interattivi
                </h2>
            </div>
        </div>

        <div class="row">
            <!-- Test Ordine Completo -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs"></i>
                            Test Ordine Completo
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>Testa un ordine attraverso tutta la catena di approvazione.</p>
                        <form id="order-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="customer-name" class="form-label">Nome Cliente:</label>
                                        <input type="text" class="form-control" id="customer-name" value="John Doe" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="customer-email" class="form-label">Email Cliente:</label>
                                        <input type="email" class="form-control" id="customer-email" value="john@example.com" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="total-amount" class="form-label">Importo Totale:</label>
                                        <input type="number" class="form-control" id="total-amount" value="500" step="0.01" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="customer-credit" class="form-label">Credito Cliente:</label>
                                        <input type="number" class="form-control" id="customer-credit" value="1000" step="0.01" required>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-play"></i> Processa Ordine
                            </button>
                        </form>
                        <div class="result-area mt-3" id="order-result">
                            <em class="text-muted">Compila il form e clicca "Processa Ordine" per vedere i risultati...</em>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Handler Singolo -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-flask"></i>
                            Test Handler Singolo
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>Testa un singolo gestore della catena.</p>
                        <form id="handler-form">
                            <div class="mb-3">
                                <label for="handler-type" class="form-label">Tipo Handler:</label>
                                <select class="form-select" id="handler-type" required>
                                    <option value="validation">Validation Handler</option>
                                    <option value="credit_check">Credit Check Handler</option>
                                    <option value="inventory_check">Inventory Check Handler</option>
                                    <option value="manager_approval">Manager Approval Handler</option>
                                    <option value="director_approval">Director Approval Handler</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="test-amount" class="form-label">Importo Test:</label>
                                <input type="number" class="form-control" id="test-amount" value="500" step="0.01" required>
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-play"></i> Test Handler
                            </button>
                        </form>
                        <div class="result-area mt-3" id="handler-result">
                            <em class="text-muted">Seleziona un handler e clicca "Test Handler" per vedere i risultati...</em>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Esempi Predefiniti -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb"></i>
                            Esempi Predefiniti
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>Testa scenari predefiniti per vedere come funziona la catena.</p>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <button class="btn btn-outline-primary w-100" onclick="testScenario('small')">
                                    <i class="fas fa-shopping-cart"></i><br>
                                    <small>Ordine Piccolo<br>(€100)</small>
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button class="btn btn-outline-warning w-100" onclick="testScenario('medium')">
                                    <i class="fas fa-shopping-bag"></i><br>
                                    <small>Ordine Medio<br>(€2,500)</small>
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button class="btn btn-outline-danger w-100" onclick="testScenario('large')">
                                    <i class="fas fa-shopping-basket"></i><br>
                                    <small>Ordine Grande<br>(€7,500)</small>
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <button class="btn btn-outline-secondary w-100" onclick="testScenario('invalid')">
                                    <i class="fas fa-exclamation-triangle"></i><br>
                                    <small>Ordine Non Valido<br>(Dati mancanti)</small>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Test ordine completo
        document.getElementById('order-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const resultDiv = document.getElementById('order-result');
            resultDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Processing order...</div>';
            
            try {
                const formData = new FormData();
                formData.append('customer_name', document.getElementById('customer-name').value);
                formData.append('customer_email', document.getElementById('customer-email').value);
                formData.append('total_amount', document.getElementById('total-amount').value);
                formData.append('customer_credit', document.getElementById('customer-credit').value);
                
                const response = await fetch('/orders/process', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const result = data.result;
                    const statusClass = result.approved ? 'success' : 'danger';
                    const statusIcon = result.approved ? 'check-circle' : 'times-circle';
                    
                    resultDiv.innerHTML = `
                        <div class="alert alert-${statusClass}">
                            <h6><i class="fas fa-${statusIcon}"></i> ${result.approved ? 'Ordine Approvato!' : 'Ordine Rifiutato!'}</h6>
                            <p><strong>Handler:</strong> <span class="badge bg-${statusClass}">${result.handler_class}</span></p>
                            <p><strong>Messaggio:</strong> ${result.message}</p>
                            <p><strong>Ordine ID:</strong> ${data.order_id}</p>
                            <p><strong>Importo:</strong> €${data.order_details.total_amount}</p>
                            <pre class="mt-2"><code>${JSON.stringify(result, null, 2)}</code></pre>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${data.error}</div>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Errore: ${error.message}</div>`;
            }
        });

        // Test handler singolo
        document.getElementById('handler-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const resultDiv = document.getElementById('handler-result');
            resultDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Testing handler...</div>';
            
            try {
                const formData = new FormData();
                formData.append('handler_type', document.getElementById('handler-type').value);
                formData.append('total_amount', document.getElementById('test-amount').value);
                
                const response = await fetch('/orders/test-handler', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const result = data.result;
                    if (result) {
                        const statusClass = result.approved ? 'success' : 'danger';
                        const statusIcon = result.approved ? 'check-circle' : 'times-circle';
                        
                        resultDiv.innerHTML = `
                            <div class="alert alert-${statusClass}">
                                <h6><i class="fas fa-${statusIcon}"></i> Handler Test Completato!</h6>
                                <p><strong>Handler:</strong> <span class="badge bg-${statusClass}">${result.handler_class}</span></p>
                                <p><strong>Risultato:</strong> ${result.approved ? 'Approvato' : 'Rifiutato'}</p>
                                <p><strong>Messaggio:</strong> ${result.message}</p>
                                <pre class="mt-2"><code>${JSON.stringify(result, null, 2)}</code></pre>
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Handler Non Applicabile</h6>
                                <p>Questo handler non può gestire la richiesta corrente.</p>
                            </div>
                        `;
                    }
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${data.error}</div>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Errore: ${error.message}</div>`;
            }
        });

        // Test scenari predefiniti
        async function testScenario(scenario) {
            const scenarios = {
                small: {
                    customer_name: 'Alice Smith',
                    customer_email: 'alice@example.com',
                    total_amount: 100,
                    customer_credit: 500
                },
                medium: {
                    customer_name: 'Bob Johnson',
                    customer_email: 'bob@example.com',
                    total_amount: 2500,
                    customer_credit: 3000
                },
                large: {
                    customer_name: 'Charlie Brown',
                    customer_email: 'charlie@example.com',
                    total_amount: 7500,
                    customer_credit: 8000
                },
                invalid: {
                    customer_name: '',
                    customer_email: 'invalid-email',
                    total_amount: -100,
                    customer_credit: 500
                }
            };
            
            const scenarioData = scenarios[scenario];
            
            // Popola il form
            document.getElementById('customer-name').value = scenarioData.customer_name;
            document.getElementById('customer-email').value = scenarioData.customer_email;
            document.getElementById('total-amount').value = scenarioData.total_amount;
            document.getElementById('customer-credit').value = scenarioData.customer_credit;
            
            // Simula il submit
            document.getElementById('order-form').dispatchEvent(new Event('submit'));
        }
    </script>
</body>
</html>
