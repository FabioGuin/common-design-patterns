<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Pattern - Esempio Completo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .example-card {
            transition: transform 0.2s;
        }
        .example-card:hover {
            transform: translateY(-5px);
        }
        .document-area {
            min-height: 300px;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
        }
        .command-history {
            max-height: 200px;
            overflow-y: auto;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
        }
        .macro-command {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 0.5rem;
            margin: 0.25rem 0;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-terminal text-primary"></i>
                    Command Pattern - Esempio Completo
                </h1>
                <p class="text-center text-muted mb-5">
                    Sistema di undo/redo, macro commands e queue di comandi
                </p>
            </div>
        </div>

        <!-- Esempi di Pattern -->
        <div class="row mb-5">
            @foreach($examples as $key => $example)
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card example-card h-100">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            <i class="fas fa-{{ $key === 'undo_redo' ? 'undo' : ($key === 'macro_commands' ? 'cogs' : ($key === 'command_queue' ? 'list' : 'chart-line')) }}"></i>
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

        <!-- Editor di Documento -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-edit"></i>
                            Document Editor
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="document-area" id="document-content">
                            Il documento è vuoto. Inizia a scrivere!
                        </div>
                        
                        <div class="mt-3">
                            <div class="btn-group" role="group">
                                <button class="btn btn-outline-primary" onclick="showCommandModal('write')">
                                    <i class="fas fa-plus"></i> Write Text
                                </button>
                                <button class="btn btn-outline-warning" onclick="showCommandModal('delete')">
                                    <i class="fas fa-trash"></i> Delete Text
                                </button>
                                <button class="btn btn-outline-info" onclick="showCommandModal('format')">
                                    <i class="fas fa-bold"></i> Format Text
                                </button>
                                <button class="btn btn-outline-success" onclick="showMacroModal()">
                                    <i class="fas fa-cogs"></i> Macro
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history"></i>
                            Command History
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="command-history" id="command-history">
                            <em class="text-muted">Nessun comando eseguito</em>
                        </div>
                        
                        <div class="mt-3">
                            <div class="btn-group w-100" role="group">
                                <button class="btn btn-warning" id="undo-btn" onclick="undoCommand()" disabled>
                                    <i class="fas fa-undo"></i> Undo
                                </button>
                                <button class="btn btn-info" id="redo-btn" onclick="redoCommand()" disabled>
                                    <i class="fas fa-redo"></i> Redo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal per Comandi -->
    <div class="modal fade" id="commandModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commandModalTitle">Execute Command</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="commandForm">
                        <input type="hidden" id="command-type" name="command_type">
                        
                        <div class="mb-3" id="text-group">
                            <label for="command-text" class="form-label">Text:</label>
                            <input type="text" class="form-control" id="command-text" name="text">
                        </div>
                        
                        <div class="mb-3">
                            <label for="command-position" class="form-label">Position:</label>
                            <input type="number" class="form-control" id="command-position" name="position" value="0" min="0">
                        </div>
                        
                        <div class="mb-3" id="length-group" style="display: none;">
                            <label for="command-length" class="form-label">Length:</label>
                            <input type="number" class="form-control" id="command-length" name="length" value="1" min="1">
                        </div>
                        
                        <div class="mb-3" id="format-group" style="display: none;">
                            <label for="command-format" class="form-label">Format:</label>
                            <select class="form-select" id="command-format" name="format">
                                <option value="bold">Bold</option>
                                <option value="italic">Italic</option>
                                <option value="underline">Underline</option>
                                <option value="strikethrough">Strikethrough</option>
                                <option value="uppercase">Uppercase</option>
                                <option value="lowercase">Lowercase</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="executeCommand()">Execute</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal per Macro -->
    <div class="modal fade" id="macroModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Execute Macro Commands</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="macro-commands">
                        <div class="macro-command">
                            <div class="row">
                                <div class="col-md-3">
                                    <select class="form-select" name="macro_type[]">
                                        <option value="write">Write Text</option>
                                        <option value="delete">Delete Text</option>
                                        <option value="format">Format Text</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="macro_text[]" placeholder="Text">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" name="macro_position[]" placeholder="Position" value="0">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" name="macro_length[]" placeholder="Length" value="1">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeMacroCommand(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-success mt-3" onclick="addMacroCommand()">
                        <i class="fas fa-plus"></i> Add Command
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="executeMacro()">Execute Macro</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let documentContent = '';
        
        // Mostra modal per comandi
        function showCommandModal(type) {
            document.getElementById('command-type').value = type;
            document.getElementById('commandModalTitle').textContent = `Execute ${type.charAt(0).toUpperCase() + type.slice(1)} Command`;
            
            // Mostra/nascondi campi in base al tipo
            const textGroup = document.getElementById('text-group');
            const lengthGroup = document.getElementById('length-group');
            const formatGroup = document.getElementById('format-group');
            
            if (type === 'write' || type === 'format') {
                textGroup.style.display = 'block';
            } else {
                textGroup.style.display = 'none';
            }
            
            if (type === 'delete') {
                lengthGroup.style.display = 'block';
            } else {
                lengthGroup.style.display = 'none';
            }
            
            if (type === 'format') {
                formatGroup.style.display = 'block';
            } else {
                formatGroup.style.display = 'none';
            }
            
            new bootstrap.Modal(document.getElementById('commandModal')).show();
        }
        
        // Mostra modal per macro
        function showMacroModal() {
            new bootstrap.Modal(document.getElementById('macroModal')).show();
        }
        
        // Esegui comando
        async function executeCommand() {
            const form = document.getElementById('commandForm');
            const formData = new FormData(form);
            
            try {
                const response = await fetch('/documents/execute-command', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    updateDocument(data.document_content);
                    updateButtons(data.can_undo, data.can_redo);
                    addToHistory(data.command_description);
                    bootstrap.Modal.getInstance(document.getElementById('commandModal')).hide();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        // Esegui macro
        async function executeMacro() {
            const commands = [];
            const macroCommands = document.querySelectorAll('#macro-commands .macro-command');
            
            macroCommands.forEach(cmd => {
                const type = cmd.querySelector('select[name="macro_type[]"]').value;
                const text = cmd.querySelector('input[name="macro_text[]"]').value;
                const position = parseInt(cmd.querySelector('input[name="macro_position[]"]').value);
                const length = parseInt(cmd.querySelector('input[name="macro_length[]"]').value);
                
                commands.push({ type, text, position, length });
            });
            
            try {
                const response = await fetch('/documents/execute-macro', {
                    method: 'POST',
                    body: JSON.stringify({ commands }),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    updateDocument(data.document_content);
                    updateButtons(data.can_undo, data.can_redo);
                    addToHistory(data.macro_description);
                    bootstrap.Modal.getInstance(document.getElementById('macroModal')).hide();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        // Annulla comando
        async function undoCommand() {
            try {
                const response = await fetch('/documents/undo', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    updateDocument(data.document_content);
                    updateButtons(data.can_undo, data.can_redo);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        // Ripristina comando
        async function redoCommand() {
            try {
                const response = await fetch('/documents/redo', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    updateDocument(data.document_content);
                    updateButtons(data.can_undo, data.can_redo);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        // Aggiorna documento
        function updateDocument(content) {
            documentContent = content;
            const docElement = document.getElementById('document-content');
            docElement.textContent = content || 'Il documento è vuoto. Inizia a scrivere!';
        }
        
        // Aggiorna pulsanti
        function updateButtons(canUndo, canRedo) {
            document.getElementById('undo-btn').disabled = !canUndo;
            document.getElementById('redo-btn').disabled = !canRedo;
        }
        
        // Aggiungi alla cronologia
        function addToHistory(description) {
            const historyElement = document.getElementById('command-history');
            const historyItem = document.createElement('div');
            historyItem.className = 'mb-2 p-2 bg-light rounded';
            historyItem.textContent = description;
            historyElement.insertBefore(historyItem, historyElement.firstChild);
        }
        
        // Aggiungi comando macro
        function addMacroCommand() {
            const container = document.getElementById('macro-commands');
            const newCommand = document.createElement('div');
            newCommand.className = 'macro-command mt-2';
            newCommand.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <select class="form-select" name="macro_type[]">
                            <option value="write">Write Text</option>
                            <option value="delete">Delete Text</option>
                            <option value="format">Format Text</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="macro_text[]" placeholder="Text">
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control" name="macro_position[]" placeholder="Position" value="0">
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control" name="macro_length[]" placeholder="Length" value="1">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeMacroCommand(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newCommand);
        }
        
        // Rimuovi comando macro
        function removeMacroCommand(button) {
            button.closest('.macro-command').remove();
        }
    </script>
</body>
</html>
