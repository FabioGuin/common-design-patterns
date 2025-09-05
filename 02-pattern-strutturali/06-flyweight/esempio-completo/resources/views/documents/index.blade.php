<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flyweight Pattern - Sistema Template Documenti</title>
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
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .stats-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #666;
            font-size: 0.9em;
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
        .document-preview {
            background: #e9ecef;
            padding: 20px;
            border-radius: 5px;
            margin-top: 15px;
            border: 1px solid #dee2e6;
        }
        .document-preview h4 {
            margin-top: 0;
            color: #495057;
        }
        .document-preview .content {
            background: white;
            padding: 15px;
            border-radius: 3px;
            border: 1px solid #ced4da;
            max-height: 300px;
            overflow-y: auto;
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
        .template-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .template-info h4 {
            margin-top: 0;
            color: #1976d2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Flyweight Pattern - Sistema Template Documenti</h1>
        
        <div class="stats-section">
            <h3>Statistiche Template</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">{{ $templateStats['total_templates'] }}</div>
                    <div class="stat-label">Template Creati</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $templateStats['created_count'] }}</div>
                    <div class="stat-label">Template Nuovi</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ $templateStats['reused_count'] }}</div>
                    <div class="stat-label">Template Riutilizzati</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($templateStats['reuse_ratio'], 2) }}</div>
                    <div class="stat-label">Rapporto Riutilizzo</div>
                </div>
            </div>
        </div>

        <div class="examples">
            <h3>Esempi Rapidi:</h3>
            <button class="example-button" onclick="loadExample('business-formal')">Business Formal</button>
            <button class="example-button" onclick="loadExample('business-casual')">Business Casual</button>
            <button class="example-button" onclick="loadExample('creative-modern')">Creative Modern</button>
            <button class="example-button" onclick="loadExample('technical')">Technical</button>
            <button class="example-button" onclick="loadExample('reuse-test')">Test Riutilizzo</button>
        </div>

        <div class="form-section">
            <h3>Crea Nuovo Documento</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="templateName">Tipo Template:</label>
                    <select id="templateName" onchange="updateTemplateOptions()">
                        <option value="">Seleziona tipo</option>
                        @foreach($availableTemplates as $key => $template)
                            <option value="{{ $key }}">{{ $template['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="templateLayout">Layout:</label>
                    <select id="templateLayout">
                        <option value="">Seleziona layout</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="templateStyle">Stile:</label>
                    <select id="templateStyle">
                        <option value="">Seleziona stile</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="documentTitle">Titolo Documento:</label>
                    <input type="text" id="documentTitle" placeholder="Titolo del documento">
                </div>
                <div class="form-group">
                    <label for="documentAuthor">Autore:</label>
                    <input type="text" id="documentAuthor" placeholder="Nome autore">
                </div>
            </div>

            <div class="form-group full-width">
                <label for="documentData">Dati del Documento (JSON):</label>
                <textarea id="documentData" placeholder='{"subject": "Argomento", "content": "Contenuto", "company_name": "Nome Azienda"}'></textarea>
            </div>

            <button onclick="createDocument()">Crea Documento</button>
            <button onclick="renderDocument()" class="success">Renderizza Documento</button>
            <button onclick="getDocumentInfo()">Info Documento</button>
            <button onclick="refreshStats()" class="success">Aggiorna Statistiche</button>
            <button onclick="clearCache()" class="danger">Pulisci Cache</button>
        </div>

        <div id="documentPreview" class="document-preview hidden">
            <h4>Anteprima Documento</h4>
            <div id="documentContent" class="content"></div>
        </div>

        <div id="result"></div>
    </div>

    <script>
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const availableTemplates = @json($availableTemplates);
        let currentDocumentId = null;
        
        const examples = {
            'business-formal': {
                template_name: 'business',
                template_layout: 'single-column',
                template_style: 'formal',
                title: 'Report Aziendale',
                author: 'Mario Rossi',
                data: {
                    subject: 'Analisi delle vendite Q1',
                    company_name: 'Acme Corp',
                    data: 'i dati mostrano un aumento del 15%',
                    recommendation: 'continuare con la strategia attuale',
                    conclusion: 'i risultati sono positivi'
                }
            },
            'business-casual': {
                template_name: 'business',
                template_layout: 'single-column',
                template_style: 'casual',
                title: 'Note di Riunione',
                author: 'Giulia Bianchi',
                data: {
                    subject: 'Riunione settimanale',
                    key_points: '1. Nuovo progetto 2. Budget approvato 3. Scadenze aggiornate'
                }
            },
            'creative-modern': {
                template_name: 'creative',
                template_layout: 'single-column',
                template_style: 'modern',
                title: 'Progetto Design',
                author: 'Luca Verdi',
                data: {
                    project_name: 'Nuovo Logo',
                    concept: 'minimalismo e eleganza',
                    inspiration: 'arte contemporanea',
                    implementation: 'uso di forme geometriche'
                }
            },
            'technical': {
                template_name: 'technical',
                template_layout: 'single-column',
                template_style: 'monospace',
                title: 'Documentazione API',
                author: 'Anna Neri',
                data: {
                    project_name: 'API REST',
                    version: '1.2.0',
                    specifications: 'endpoint GET /users',
                    implementation: 'Laravel 11',
                    testing: 'PHPUnit'
                }
            },
            'reuse-test': {
                template_name: 'business',
                template_layout: 'single-column',
                template_style: 'formal',
                title: 'Test Riutilizzo',
                author: 'Test User',
                data: {
                    subject: 'Test di riutilizzo template',
                    company_name: 'Test Corp',
                    data: 'dati di test',
                    recommendation: 'test riuscito',
                    conclusion: 'template riutilizzato correttamente'
                }
            }
        };

        function updateTemplateOptions() {
            const templateName = document.getElementById('templateName').value;
            const layoutSelect = document.getElementById('templateLayout');
            const styleSelect = document.getElementById('templateStyle');
            
            // Reset options
            layoutSelect.innerHTML = '<option value="">Seleziona layout</option>';
            styleSelect.innerHTML = '<option value="">Seleziona stile</option>';
            
            if (templateName && availableTemplates[templateName]) {
                const template = availableTemplates[templateName];
                
                // Add layout options
                template.layouts.forEach(layout => {
                    const option = document.createElement('option');
                    option.value = layout;
                    option.textContent = layout;
                    layoutSelect.appendChild(option);
                });
                
                // Add style options
                template.styles.forEach(style => {
                    const option = document.createElement('option');
                    option.value = style;
                    option.textContent = style;
                    styleSelect.appendChild(option);
                });
            }
        }

        function loadExample(exampleKey) {
            const example = examples[exampleKey];
            if (example) {
                document.getElementById('templateName').value = example.template_name;
                updateTemplateOptions();
                document.getElementById('templateLayout').value = example.template_layout;
                document.getElementById('templateStyle').value = example.template_style;
                document.getElementById('documentTitle').value = example.title;
                document.getElementById('documentAuthor').value = example.author;
                document.getElementById('documentData').value = JSON.stringify(example.data, null, 2);
            }
        }

        function showResult(data, type = 'info') {
            const resultDiv = document.getElementById('result');
            resultDiv.className = `result ${type}`;
            resultDiv.textContent = JSON.stringify(data, null, 2);
        }

        function showDocumentPreview(content) {
            const previewDiv = document.getElementById('documentPreview');
            const contentDiv = document.getElementById('documentContent');
            
            contentDiv.innerHTML = content;
            previewDiv.classList.remove('hidden');
        }

        function createDocument() {
            const data = {
                template_name: document.getElementById('templateName').value,
                template_layout: document.getElementById('templateLayout').value,
                template_style: document.getElementById('templateStyle').value,
                title: document.getElementById('documentTitle').value,
                author: document.getElementById('documentAuthor').value,
                data: JSON.parse(document.getElementById('documentData').value || '{}')
            };

            if (!data.template_name || !data.template_layout || !data.template_style) {
                showResult({ error: 'Seleziona template, layout e stile' }, 'error');
                return;
            }

            if (!data.title || !data.author) {
                showResult({ error: 'Titolo e autore richiesti' }, 'error');
                return;
            }

            fetch('/documents/create', {
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
                    currentDocumentId = data.document_id;
                    refreshStats();
                }
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }

        function renderDocument() {
            if (!currentDocumentId) {
                showResult({ error: 'Nessun documento selezionato' }, 'error');
                return;
            }

            fetch('/documents/render', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ document_id: currentDocumentId })
            })
            .then(response => response.json())
            .then(data => {
                showResult(data, data.success ? 'success' : 'error');
                if (data.success) {
                    showDocumentPreview(data.rendered_content);
                }
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }

        function getDocumentInfo() {
            if (!currentDocumentId) {
                showResult({ error: 'Nessun documento selezionato' }, 'error');
                return;
            }

            fetch('/documents/info', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ document_id: currentDocumentId })
            })
            .then(response => response.json())
            .then(data => {
                showResult(data, data.success ? 'success' : 'error');
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }

        function refreshStats() {
            fetch('/documents/template-stats', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult(data, 'success');
                    location.reload(); // Ricarica la pagina per mostrare le statistiche aggiornate
                } else {
                    showResult(data, 'error');
                }
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }

        function clearCache() {
            fetch('/documents/clear-cache', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => response.json())
            .then(data => {
                showResult(data, data.success ? 'success' : 'error');
                if (data.success) {
                    refreshStats();
                }
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }
    </script>
</body>
</html>
