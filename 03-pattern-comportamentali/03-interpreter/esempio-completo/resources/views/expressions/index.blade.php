<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interpreter Pattern - Esempio Completo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-code text-primary"></i>
            Interpreter Pattern - Esempio Completo
        </h1>
        <p class="text-center text-muted mb-5">
            Sistema di interpretazione di espressioni matematiche, query e configurazioni
        </p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Mathematical Expression Evaluator</h5>
                    </div>
                    <div class="card-body">
                        <form id="math-form">
                            <div class="mb-3">
                                <label for="math-expression" class="form-label">Expression:</label>
                                <input type="text" class="form-control" id="math-expression" placeholder="e.g., 5 + 3 * 2" value="5 + 3 * 2">
                            </div>
                            <div class="mb-3">
                                <label for="math-variables" class="form-label">Variables (JSON):</label>
                                <textarea class="form-control" id="math-variables" rows="3" placeholder='{"x": 10, "y": 5}'>{}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Evaluate</button>
                        </form>
                        <div id="math-result" class="mt-3"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Query Language Interpreter</h5>
                    </div>
                    <div class="card-body">
                        <form id="query-form">
                            <div class="mb-3">
                                <label for="query-expression" class="form-label">Query:</label>
                                <input type="text" class="form-control" id="query-expression" placeholder="e.g., SELECT * FROM users WHERE age > 25" value="SELECT * FROM users WHERE age > 25">
                            </div>
                            <div class="mb-3">
                                <label for="query-context" class="form-label">Context (JSON):</label>
                                <textarea class="form-control" id="query-context" rows="3" placeholder='{"data": [{"name": "John", "age": 30}]}'>{}</textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Execute Query</button>
                        </form>
                        <div id="query-result" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Math form handler
        document.getElementById('math-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const expression = document.getElementById('math-expression').value;
            const variables = JSON.parse(document.getElementById('math-variables').value || '{}');
            
            try {
                const response = await fetch('/expressions/evaluate-math', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ expression, variables })
                });
                
                const data = await response.json();
                document.getElementById('math-result').innerHTML = `
                    <div class="alert alert-${data.success ? 'success' : 'danger'}">
                        <strong>Result:</strong> ${data.success ? data.result : data.error}
                    </div>
                `;
            } catch (error) {
                document.getElementById('math-result').innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${error.message}
                    </div>
                `;
            }
        });
        
        // Query form handler
        document.getElementById('query-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const query = document.getElementById('query-expression').value;
            const context = JSON.parse(document.getElementById('query-context').value || '{}');
            
            try {
                const response = await fetch('/expressions/evaluate-query', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ query, context })
                });
                
                const data = await response.json();
                document.getElementById('query-result').innerHTML = `
                    <div class="alert alert-${data.success ? 'success' : 'danger'}">
                        <strong>Result:</strong> ${data.success ? JSON.stringify(data.result) : data.error}
                    </div>
                `;
            } catch (error) {
                document.getElementById('query-result').innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${error.message}
                    </div>
                `;
            }
        });
    </script>
</body>
</html>
