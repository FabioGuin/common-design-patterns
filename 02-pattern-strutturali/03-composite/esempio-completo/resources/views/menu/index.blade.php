<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Composite Pattern - Sistema Menu</title>
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
        .menu-section {
            margin-bottom: 30px;
        }
        .menu-item {
            margin-left: 20px;
            margin-bottom: 10px;
            padding: 10px;
            border-left: 3px solid #007bff;
            background: #f8f9fa;
        }
        .menu-category {
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
            padding: 10px;
            background: #e3f2fd;
            border-radius: 5px;
        }
        .menu-item-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .item-name {
            font-weight: bold;
            color: #333;
        }
        .item-price {
            color: #28a745;
            font-weight: bold;
        }
        .item-description {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .stats {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .stats h3 {
            margin-top: 0;
            color: #333;
        }
        .stat-item {
            display: inline-block;
            margin-right: 20px;
            padding: 10px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #007bff;
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
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 14px;
            max-height: 300px;
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
        .search-results {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Composite Pattern - Sistema Menu</h1>
        
        <div class="stats">
            <h3>Statistiche Menu</h3>
            <div id="statsContainer">
                <div class="stat-item">
                    <div class="stat-value" id="totalItems">{{ $menuData['total_count'] }}</div>
                    <div class="stat-label">Elementi Totali</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="totalPrice">€{{ number_format($menuData['total_price'], 2) }}</div>
                    <div class="stat-label">Prezzo Totale</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="categoriesCount">-</div>
                    <div class="stat-label">Categorie</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="itemsCount">-</div>
                    <div class="stat-label">Voci Menu</div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Gestione Menu</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="itemName">Nome Elemento:</label>
                    <input type="text" id="itemName" placeholder="Nome dell'elemento">
                </div>
                <div class="form-group">
                    <label for="itemPrice">Prezzo:</label>
                    <input type="number" id="itemPrice" step="0.01" min="0" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="itemType">Tipo:</label>
                    <select id="itemType">
                        <option value="item">Voce Menu</option>
                        <option value="category">Categoria</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="itemDescription">Descrizione:</label>
                    <input type="text" id="itemDescription" placeholder="Descrizione (opzionale)">
                </div>
                <div class="form-group">
                    <label for="parentName">Categoria Parent:</label>
                    <input type="text" id="parentName" placeholder="Nome categoria parent (opzionale)">
                </div>
            </div>
            
            <button onclick="addItem()">Aggiungi Elemento</button>
            <button onclick="removeItem()" class="danger">Rimuovi Elemento</button>
            <button onclick="searchItem()">Cerca Elemento</button>
            <button onclick="refreshStats()">Aggiorna Statistiche</button>
        </div>

        <div class="menu-section">
            <h3>Struttura Menu</h3>
            <div id="menuStructure">
                {!! renderMenuStructure($menuData) !!}
            </div>
        </div>

        <div id="result"></div>
    </div>

    <script>
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        function showResult(data, type = 'info') {
            const resultDiv = document.getElementById('result');
            resultDiv.className = `result ${type}`;
            resultDiv.textContent = JSON.stringify(data, null, 2);
        }

        function addItem() {
            const data = {
                name: document.getElementById('itemName').value,
                price: parseFloat(document.getElementById('itemPrice').value) || 0,
                description: document.getElementById('itemDescription').value,
                type: document.getElementById('itemType').value,
                parent_name: document.getElementById('parentName').value || null
            };

            if (!data.name) {
                showResult({ error: 'Nome elemento richiesto' }, 'error');
                return;
            }

            fetch('/menu/add', {
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
                    refreshStats();
                    location.reload(); // Ricarica la pagina per mostrare le modifiche
                }
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }

        function removeItem() {
            const name = document.getElementById('itemName').value;
            const parentName = document.getElementById('parentName').value || null;

            if (!name) {
                showResult({ error: 'Nome elemento richiesto' }, 'error');
                return;
            }

            fetch('/menu/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ name, parent_name: parentName })
            })
            .then(response => response.json())
            .then(data => {
                showResult(data, data.success ? 'success' : 'error');
                if (data.success) {
                    refreshStats();
                    location.reload(); // Ricarica la pagina per mostrare le modifiche
                }
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }

        function searchItem() {
            const name = document.getElementById('itemName').value;

            if (!name) {
                showResult({ error: 'Nome elemento richiesto' }, 'error');
                return;
            }

            fetch('/menu/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ name })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult(data, 'success');
                } else {
                    showResult(data, 'error');
                }
            })
            .catch(error => {
                showResult({ error: error.message }, 'error');
            });
        }

        function refreshStats() {
            fetch('/menu/stats', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('totalItems').textContent = data.stats.total_items;
                    document.getElementById('totalPrice').textContent = '€' + data.stats.total_price.toFixed(2);
                    document.getElementById('categoriesCount').textContent = data.stats.categories_count;
                    document.getElementById('itemsCount').textContent = data.stats.items_count;
                }
            })
            .catch(error => {
                console.error('Error refreshing stats:', error);
            });
        }

        // Carica le statistiche all'avvio
        refreshStats();
    </script>
</body>
</html>

<?php
function renderMenuStructure($menuData, $level = 0) {
    $html = '';
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
    
    if ($menuData['type'] === 'category') {
        $html .= '<div class="menu-category">';
        $html .= $indent . '<strong>' . htmlspecialchars($menuData['name']) . '</strong>';
        if ($menuData['description']) {
            $html .= ' - ' . htmlspecialchars($menuData['description']);
        }
        $html .= ' <small>(' . $menuData['children_count'] . ' elementi, €' . number_format($menuData['total_price'], 2) . ')</small>';
        $html .= '</div>';
        
        if (!empty($menuData['children'])) {
            foreach ($menuData['children'] as $child) {
                $html .= renderMenuStructure($child, $level + 1);
            }
        }
    } else {
        $html .= '<div class="menu-item">';
        $html .= '<div class="menu-item-details">';
        $html .= '<div>';
        $html .= '<div class="item-name">' . $indent . htmlspecialchars($menuData['name']) . '</div>';
        if ($menuData['description']) {
            $html .= '<div class="item-description">' . htmlspecialchars($menuData['description']) . '</div>';
        }
        $html .= '</div>';
        $html .= '<div class="item-price">€' . number_format($menuData['price'], 2) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    return $html;
}
?>
