<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proxy Pattern - Esempio Completo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .example-card {
            transition: transform 0.2s;
        }
        .example-card:hover {
            transform: translateY(-5px);
        }
        .code-block {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 1rem;
            margin: 1rem 0;
        }
        .result-area {
            min-height: 200px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-shield-alt text-primary"></i>
                    Proxy Pattern - Esempio Completo
                </h1>
                <p class="text-center text-muted mb-5">
                    Dimostrazione pratica del Proxy Pattern con caching, controllo di accesso e logging
                </p>
            </div>
        </div>

        <!-- Esempi di Proxy -->
        <div class="row mb-5">
            @foreach($examples as $key => $example)
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card example-card h-100">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            <i class="fas fa-{{ $key === 'caching' ? 'database' : ($key === 'access_control' ? 'lock' : ($key === 'logging' ? 'chart-line' : 'cogs')) }}"></i>
                            {{ $example['title'] }}
                        </h5>
                        <p class="card-text">{{ $example['description'] }}</p>
                        <ul class="list-unstyled">
                            @foreach($example['benefits'] as $benefit)
                            <li><i class="fas fa-check text-success me-2"></i>{{ $benefit }}</li>
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
            <!-- Caching Proxy Test -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-database"></i>
                            Caching Proxy Test
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>Testa il caching delle chiamate API. La seconda chiamata dovrebbe essere più veloce.</p>
                        <div class="mb-3">
                            <label for="caching-user-id" class="form-label">User ID:</label>
                            <input type="number" class="form-control" id="caching-user-id" value="1" min="1" max="10">
                        </div>
                        <button class="btn btn-primary" onclick="testCaching()">
                            <i class="fas fa-play"></i> Test Caching
                        </button>
                        <div class="result-area mt-3" id="caching-result">
                            <em class="text-muted">Clicca "Test Caching" per vedere i risultati...</em>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Access Control Proxy Test -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-lock"></i>
                            Access Control Proxy Test
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>Testa il controllo di accesso con diversi ruoli utente.</p>
                        <div class="mb-3">
                            <label for="access-user-id" class="form-label">User ID:</label>
                            <input type="number" class="form-control" id="access-user-id" value="1" min="1" max="10">
                        </div>
                        <div class="mb-3">
                            <label for="access-role" class="form-label">User Role:</label>
                            <select class="form-select" id="access-role">
                                <option value="admin">Admin</option>
                                <option value="moderator">Moderator</option>
                                <option value="user">User</option>
                                <option value="guest">Guest</option>
                            </select>
                        </div>
                        <button class="btn btn-warning" onclick="testAccessControl()">
                            <i class="fas fa-play"></i> Test Access Control
                        </button>
                        <div class="result-area mt-3" id="access-result">
                            <em class="text-muted">Clicca "Test Access Control" per vedere i risultati...</em>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logging Proxy Test -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line"></i>
                            Logging Proxy Test
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>Testa il logging delle operazioni. Controlla i log per i dettagli.</p>
                        <div class="mb-3">
                            <label for="logging-user-id" class="form-label">User ID:</label>
                            <input type="number" class="form-control" id="logging-user-id" value="1" min="1" max="10">
                        </div>
                        <button class="btn btn-info" onclick="testLogging()">
                            <i class="fas fa-play"></i> Test Logging
                        </button>
                        <div class="result-area mt-3" id="logging-result">
                            <em class="text-muted">Clicca "Test Logging" per vedere i risultati...</em>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combined Proxy Test -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs"></i>
                            Combined Proxy Test
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>Testa tutti i proxy combinati insieme.</p>
                        <div class="mb-3">
                            <label for="combined-user-id" class="form-label">User ID:</label>
                            <input type="number" class="form-control" id="combined-user-id" value="1" min="1" max="10">
                        </div>
                        <div class="mb-3">
                            <label for="combined-role" class="form-label">User Role:</label>
                            <select class="form-select" id="combined-role">
                                <option value="admin">Admin</option>
                                <option value="moderator">Moderator</option>
                                <option value="user">User</option>
                                <option value="guest">Guest</option>
                            </select>
                        </div>
                        <button class="btn btn-success" onclick="testCombined()">
                            <i class="fas fa-play"></i> Test Combined
                        </button>
                        <div class="result-area mt-3" id="combined-result">
                            <em class="text-muted">Clicca "Test Combined" per vedere i risultati...</em>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cache Management -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-trash"></i>
                            Cache Management
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>Gestisci la cache del sistema.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="invalidate-user-id" class="form-label">User ID (opzionale):</label>
                                    <input type="number" class="form-control" id="invalidate-user-id" placeholder="Lascia vuoto per invalidare tutta la cache">
                                </div>
                                <button class="btn btn-warning" onclick="invalidateCache()">
                                    <i class="fas fa-trash"></i> Invalida Cache
                                </button>
                            </div>
                            <div class="col-md-6">
                                <div class="result-area" id="invalidate-result">
                                    <em class="text-muted">Usa i pulsanti per gestire la cache...</em>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function testCaching() {
            const userId = document.getElementById('caching-user-id').value;
            const resultDiv = document.getElementById('caching-result');
            
            resultDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Testing caching...</div>';
            
            try {
                const response = await fetch(`/data/caching?user_id=${userId}`);
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <h6><i class="fas fa-check-circle"></i> Test Completato!</h6>
                            <p><strong>Prima chiamata:</strong> ${data.first_call_time}</p>
                            <p><strong>Seconda chiamata:</strong> ${data.second_call_time}</p>
                            <p><strong>Cache:</strong> <span class="badge bg-success">${data.cache_benefit}</span></p>
                            <pre class="mt-2"><code>${JSON.stringify(data.data, null, 2)}</code></pre>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${data.error}</div>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Errore: ${error.message}</div>`;
            }
        }

        async function testAccessControl() {
            const userId = document.getElementById('access-user-id').value;
            const userRole = document.getElementById('access-role').value;
            const resultDiv = document.getElementById('access-result');
            
            resultDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Testing access control...</div>';
            
            try {
                const response = await fetch(`/data/access-control?user_id=${userId}&user_role=${userRole}`);
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <h6><i class="fas fa-check-circle"></i> Accesso Consentito!</h6>
                            <p><strong>Ruolo:</strong> <span class="badge bg-primary">${data.user_role}</span></p>
                            <p><strong>Messaggio:</strong> ${data.message}</p>
                            <pre class="mt-2"><code>${JSON.stringify(data.data, null, 2)}</code></pre>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-times-circle"></i> Accesso Negato!</h6>
                            <p><strong>Ruolo:</strong> <span class="badge bg-danger">${data.user_role}</span></p>
                            <p><strong>Errore:</strong> ${data.error}</p>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Errore: ${error.message}</div>`;
            }
        }

        async function testLogging() {
            const userId = document.getElementById('logging-user-id').value;
            const resultDiv = document.getElementById('logging-result');
            
            resultDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Testing logging...</div>';
            
            try {
                const response = await fetch(`/data/logging?user_id=${userId}`);
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-info">
                            <h6><i class="fas fa-check-circle"></i> Logging Attivo!</h6>
                            <p><strong>Messaggio:</strong> ${data.message}</p>
                            <p><em>Controlla i log dell'applicazione per i dettagli completi</em></p>
                            <pre class="mt-2"><code>${JSON.stringify(data.data, null, 2)}</code></pre>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${data.error}</div>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Errore: ${error.message}</div>`;
            }
        }

        async function testCombined() {
            const userId = document.getElementById('combined-user-id').value;
            const userRole = document.getElementById('combined-role').value;
            const resultDiv = document.getElementById('combined-result');
            
            resultDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Testing combined proxies...</div>';
            
            try {
                const response = await fetch(`/data/combined?user_id=${userId}&user_role=${userRole}`);
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <h6><i class="fas fa-check-circle"></i> Tutti i Proxy Funzionano!</h6>
                            <p><strong>Tempo di esecuzione:</strong> ${data.execution_time}</p>
                            <div class="mt-3">
                                <h6>Funzionalità Utilizzate:</h6>
                                <ul class="list-unstyled">
                                    ${Object.entries(data.features_used).map(([key, value]) => 
                                        `<li><i class="fas fa-check text-success me-2"></i><strong>${key}:</strong> ${value}</li>`
                                    ).join('')}
                                </ul>
                            </div>
                            <pre class="mt-2"><code>${JSON.stringify(data.data, null, 2)}</code></pre>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${data.error}</div>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Errore: ${error.message}</div>`;
            }
        }

        async function invalidateCache() {
            const userId = document.getElementById('invalidate-user-id').value;
            const resultDiv = document.getElementById('invalidate-result');
            
            resultDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Invalidating cache...</div>';
            
            try {
                const formData = new FormData();
                if (userId) {
                    formData.append('user_id', userId);
                }
                
                const response = await fetch('/data/invalidate-cache', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <h6><i class="fas fa-check-circle"></i> Cache Invalidata!</h6>
                            <p>${data.message}</p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ${data.error}</div>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Errore: ${error.message}</div>`;
            }
        }
    </script>
</body>
</html>
