<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backend for Frontend Pattern - Esempio</title>
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
        .bff-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .bff-tab {
            padding: 15px 25px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            font-weight: bold;
        }
        .bff-tab.active {
            border-bottom-color: #007bff;
            color: #007bff;
        }
        .bff-tab.web { border-bottom-color: #28a745; }
        .bff-tab.mobile { border-bottom-color: #ffc107; }
        .bff-tab.desktop { border-bottom-color: #dc3545; }
        .bff-tab.active.web { border-bottom-color: #28a745; color: #28a745; }
        .bff-tab.active.mobile { border-bottom-color: #ffc107; color: #ffc107; }
        .bff-tab.active.desktop { border-bottom-color: #dc3545; color: #dc3545; }
        .bff-content {
            display: none;
        }
        .bff-content.active {
            display: block;
        }
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .comparison-table th,
        .comparison-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .comparison-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .comparison-table .web { background-color: #d4edda; }
        .comparison-table .mobile { background-color: #fff3cd; }
        .comparison-table .desktop { background-color: #f8d7da; }
        .api-test {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .api-test h4 {
            margin-top: 0;
        }
        .api-test pre {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .response-data {
            background: #e8f5e8;
            border: 1px solid #c3e6c3;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .response-data.error {
            background: #f8d7da;
            border-color: #f5c6cb;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #0056b3;
        }
        button.web { background: #28a745; }
        button.mobile { background: #ffc107; color: #000; }
        button.desktop { background: #dc3545; }
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
        .stat-card.web { border-left-color: #28a745; }
        .stat-card.mobile { border-left-color: #ffc107; }
        .stat-card.desktop { border-left-color: #dc3545; }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Backend for Frontend Pattern - Esempio</h1>
        
        <div class="pattern-info">
            <h3>Come funziona il Backend for Frontend Pattern</h3>
            <p>Il Backend for Frontend (BFF) Pattern crea API specifiche per ogni tipo di frontend, ottimizzando i dati e le operazioni per le esigenze specifiche di ogni interfaccia utente. Ogni BFF è progettato per massimizzare le performance e l'usabilità del frontend corrispondente.</p>
        </div>

        <!-- Tabs per i diversi BFF -->
        <div class="bff-tabs">
            <div class="bff-tab web active" onclick="showBFF('web')">Web BFF</div>
            <div class="bff-tab mobile" onclick="showBFF('mobile')">Mobile BFF</div>
            <div class="bff-tab desktop" onclick="showBFF('desktop')">Desktop BFF</div>
        </div>

        <!-- Web BFF Content -->
        <div id="web" class="bff-content active">
            <h3>Web BFF - Ottimizzato per Browser Desktop</h3>
            <p>Il Web BFF è progettato per browser desktop con connessioni stabili. Include dati completi e dettagliati per supportare operazioni complesse.</p>
            
            <div class="api-test">
                <h4>Test API Web BFF</h4>
                <button class="web" onclick="testWebAPI('orders')">Test Orders</button>
                <button class="web" onclick="testWebAPI('dashboard')">Test Dashboard</button>
                <button class="web" onclick="testWebAPI('products')">Test Products</button>
                <div id="web-response" class="response-data" style="display: none;"></div>
            </div>
        </div>

        <!-- Mobile BFF Content -->
        <div id="mobile" class="bff-content">
            <h3>Mobile BFF - Ottimizzato per Dispositivi Mobili</h3>
            <p>Il Mobile BFF è progettato per dispositivi mobili con connessioni potenzialmente lente. Include solo i dati essenziali e supporta funzionalità offline.</p>
            
            <div class="api-test">
                <h4>Test API Mobile BFF</h4>
                <button class="mobile" onclick="testMobileAPI('orders')">Test Orders</button>
                <button class="mobile" onclick="testMobileAPI('dashboard')">Test Dashboard</button>
                <button class="mobile" onclick="testMobileAPI('offline')">Test Offline Data</button>
                <div id="mobile-response" class="response-data" style="display: none;"></div>
            </div>
        </div>

        <!-- Desktop BFF Content -->
        <div id="desktop" class="bff-content">
            <h3>Desktop BFF - Ottimizzato per Applicazioni Desktop</h3>
            <p>Il Desktop BFF è progettato per applicazioni desktop native. Include dati strutturati per operazioni batch e integrazione con sistemi locali.</p>
            
            <div class="api-test">
                <h4>Test API Desktop BFF</h4>
                <button class="desktop" onclick="testDesktopAPI('orders')">Test Orders</button>
                <button class="desktop" onclick="testDesktopAPI('dashboard')">Test Dashboard</button>
                <button class="desktop" onclick="testDesktopAPI('export')">Test Export Data</button>
                <div id="desktop-response" class="response-data" style="display: none;"></div>
            </div>
        </div>

        <!-- Confronto BFF -->
        <div class="container">
            <h3>Confronto tra i diversi BFF</h3>
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th>Caratteristica</th>
                        <th class="web">Web BFF</th>
                        <th class="mobile">Mobile BFF</th>
                        <th class="desktop">Desktop BFF</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Dati inclusi</strong></td>
                        <td class="web">Completi e dettagliati</td>
                        <td class="mobile">Essenziali e compatti</td>
                        <td class="desktop">Strutturati e completi</td>
                    </tr>
                    <tr>
                        <td><strong>Cache TTL</strong></td>
                        <td class="web">5 minuti</td>
                        <td class="mobile">3 minuti</td>
                        <td class="desktop">10 minuti</td>
                    </tr>
                    <tr>
                        <td><strong>Limite risultati</strong></td>
                        <td class="web">50-100</td>
                        <td class="mobile">20-50</td>
                        <td class="desktop">100-200</td>
                    </tr>
                    <tr>
                        <td><strong>Supporto offline</strong></td>
                        <td class="web">No</td>
                        <td class="mobile">Sì</td>
                        <td class="desktop">Limitato</td>
                    </tr>
                    <tr>
                        <td><strong>Export dati</strong></td>
                        <td class="web">No</td>
                        <td class="mobile">No</td>
                        <td class="desktop">Sì</td>
                    </tr>
                    <tr>
                        <td><strong>Formattazione</strong></td>
                        <td class="web">Completa</td>
                        <td class="mobile">Semplificata</td>
                        <td class="desktop">Strutturata</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Statistiche -->
        <div class="container">
            <h3>Statistiche BFF</h3>
            <div class="stats-grid">
                <div class="stat-card web">
                    <div class="stat-number" id="web-orders">0</div>
                    <div class="stat-label">Ordini Web</div>
                </div>
                <div class="stat-card mobile">
                    <div class="stat-number" id="mobile-orders">0</div>
                    <div class="stat-label">Ordini Mobile</div>
                </div>
                <div class="stat-card desktop">
                    <div class="stat-number" id="desktop-orders">0</div>
                    <div class="stat-label">Ordini Desktop</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showBFF(bffType) {
            // Nascondi tutti i contenuti
            document.querySelectorAll('.bff-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelectorAll('.bff-tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Mostra il contenuto selezionato
            document.getElementById(bffType).classList.add('active');
            event.target.classList.add('active');
        }

        function testWebAPI(endpoint) {
            const responseDiv = document.getElementById('web-response');
            responseDiv.style.display = 'block';
            responseDiv.innerHTML = '<p>Caricamento...</p>';

            fetch(`/api/web/${endpoint}`)
            .then(response => response.json())
            .then(data => {
                responseDiv.innerHTML = `
                    <h5>Risposta Web BFF - ${endpoint.toUpperCase()}</h5>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
                updateStats('web', data);
            })
            .catch(error => {
                responseDiv.innerHTML = `
                    <h5>Errore Web BFF - ${endpoint.toUpperCase()}</h5>
                    <p class="error">${error.message}</p>
                `;
            });
        }

        function testMobileAPI(endpoint) {
            const responseDiv = document.getElementById('mobile-response');
            responseDiv.style.display = 'block';
            responseDiv.innerHTML = '<p>Caricamento...</p>';

            fetch(`/api/mobile/${endpoint}`)
            .then(response => response.json())
            .then(data => {
                responseDiv.innerHTML = `
                    <h5>Risposta Mobile BFF - ${endpoint.toUpperCase()}</h5>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
                updateStats('mobile', data);
            })
            .catch(error => {
                responseDiv.innerHTML = `
                    <h5>Errore Mobile BFF - ${endpoint.toUpperCase()}</h5>
                    <p class="error">${error.message}</p>
                `;
            });
        }

        function testDesktopAPI(endpoint) {
            const responseDiv = document.getElementById('desktop-response');
            responseDiv.style.display = 'block';
            responseDiv.innerHTML = '<p>Caricamento...</p>';

            const url = endpoint === 'export' ? '/api/desktop/export?type=orders' : `/api/desktop/${endpoint}`;
            
            fetch(url)
            .then(response => response.json())
            .then(data => {
                responseDiv.innerHTML = `
                    <h5>Risposta Desktop BFF - ${endpoint.toUpperCase()}</h5>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
                updateStats('desktop', data);
            })
            .catch(error => {
                responseDiv.innerHTML = `
                    <h5>Errore Desktop BFF - ${endpoint.toUpperCase()}</h5>
                    <p class="error">${error.message}</p>
                `;
            });
        }

        function updateStats(bffType, data) {
            if (data.success && data.data) {
                if (Array.isArray(data.data)) {
                    document.getElementById(`${bffType}-orders`).textContent = data.data.length;
                } else if (data.data.total_orders) {
                    document.getElementById(`${bffType}-orders`).textContent = data.data.total_orders;
                }
            }
        }

        // Carica dati iniziali
        document.addEventListener('DOMContentLoaded', function() {
            testWebAPI('orders');
            testMobileAPI('orders');
            testDesktopAPI('orders');
        });
    </script>
</body>
</html>
