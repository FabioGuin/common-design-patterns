<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memento Pattern - Esempio Completo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-history text-primary"></i>
            Memento Pattern - Esempio Completo
        </h1>
        <p class="text-center text-muted mb-5">
            Sistema di undo/redo e checkpoint per documenti
        </p>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Document Editor</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" id="document-editor" rows="10" placeholder="Start typing your document..."></textarea>
                        <div class="mt-3">
                            <button class="btn btn-success" id="save-checkpoint">Save Checkpoint</button>
                            <button class="btn btn-warning" id="undo-btn" disabled>Undo</button>
                            <button class="btn btn-info" id="redo-btn" disabled>Redo</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Checkpoint History</h5>
                    </div>
                    <div class="card-body">
                        <div id="checkpoint-history">
                            <p class="text-muted">No checkpoints saved yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simula Memento Pattern
        class DocumentMemento {
            constructor(content, timestamp) {
                this.content = content;
                this.timestamp = timestamp;
            }
            
            getContent() {
                return this.content;
            }
            
            getTimestamp() {
                return this.timestamp;
            }
        }
        
        class DocumentCaretaker {
            constructor() {
                this.mementos = [];
                this.currentIndex = -1;
            }
            
            saveMemento(memento) {
                this.mementos = this.mementos.slice(0, this.currentIndex + 1);
                this.mementos.push(memento);
                this.currentIndex = this.mementos.length - 1;
            }
            
            undo() {
                if (this.currentIndex > 0) {
                    this.currentIndex--;
                    return this.mementos[this.currentIndex];
                }
                return null;
            }
            
            redo() {
                if (this.currentIndex < this.mementos.length - 1) {
                    this.currentIndex++;
                    return this.mementos[this.currentIndex];
                }
                return null;
            }
            
            canUndo() {
                return this.currentIndex > 0;
            }
            
            canRedo() {
                return this.currentIndex < this.mementos.length - 1;
            }
            
            getHistory() {
                return this.mementos;
            }
        }
        
        // Inizializza il sistema
        const caretaker = new DocumentCaretaker();
        const editor = document.getElementById('document-editor');
        const undoBtn = document.getElementById('undo-btn');
        const redoBtn = document.getElementById('redo-btn');
        const saveBtn = document.getElementById('save-checkpoint');
        const historyDiv = document.getElementById('checkpoint-history');
        
        // Event listeners
        saveBtn.addEventListener('click', function() {
            const content = editor.value;
            const timestamp = new Date().toLocaleTimeString();
            const memento = new DocumentMemento(content, timestamp);
            caretaker.saveMemento(memento);
            updateHistory();
            updateButtons();
        });
        
        undoBtn.addEventListener('click', function() {
            const memento = caretaker.undo();
            if (memento) {
                editor.value = memento.getContent();
                updateHistory();
                updateButtons();
            }
        });
        
        redoBtn.addEventListener('click', function() {
            const memento = caretaker.redo();
            if (memento) {
                editor.value = memento.getContent();
                updateHistory();
                updateButtons();
            }
        });
        
        function updateHistory() {
            const history = caretaker.getHistory();
            if (history.length === 0) {
                historyDiv.innerHTML = '<p class="text-muted">No checkpoints saved yet</p>';
            } else {
                let html = '<ul class="list-group list-group-flush">';
                history.forEach((memento, index) => {
                    const isActive = index === caretaker.currentIndex;
                    html += `<li class="list-group-item ${isActive ? 'active' : ''}">
                        <small>${memento.getTimestamp()}</small>
                        <br>
                        <small>${memento.getContent().substring(0, 50)}${memento.getContent().length > 50 ? '...' : ''}</small>
                    </li>`;
                });
                html += '</ul>';
                historyDiv.innerHTML = html;
            }
        }
        
        function updateButtons() {
            undoBtn.disabled = !caretaker.canUndo();
            redoBtn.disabled = !caretaker.canRedo();
        }
        
        // Inizializza
        updateHistory();
        updateButtons();
    </script>
</body>
</html>
