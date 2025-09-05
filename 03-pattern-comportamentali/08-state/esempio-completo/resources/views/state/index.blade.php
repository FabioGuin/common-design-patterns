<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>State Pattern - Esempio Completo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-cogs text-primary"></i>
            State Pattern - Esempio Completo
        </h1>
        <p class="text-center text-muted mb-5">
            Sistema di gestione stati per ordini
        </p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Order State Machine</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="order-id" class="form-label">Order ID:</label>
                            <input type="text" class="form-control" id="order-id" value="ORD-001" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="current-state" class="form-label">Current State:</label>
                            <input type="text" class="form-control" id="current-state" value="pending" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="state-actions" class="form-label">Available Actions:</label>
                            <div id="state-actions">
                                <button class="btn btn-success me-2" id="confirm-btn">Confirm</button>
                                <button class="btn btn-danger me-2" id="cancel-btn">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">State History</h5>
                    </div>
                    <div class="card-body">
                        <div id="state-history">
                            <p class="text-muted">No state changes yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simula State Pattern
        class OrderStateMachine {
            constructor() {
                this.currentState = 'pending';
                this.history = [];
                this.states = {
                    'pending': {
                        actions: ['confirm', 'cancel'],
                        transitions: {
                            'confirm': 'confirmed',
                            'cancel': 'cancelled'
                        }
                    },
                    'confirmed': {
                        actions: ['ship', 'cancel'],
                        transitions: {
                            'ship': 'shipped',
                            'cancel': 'cancelled'
                        }
                    },
                    'shipped': {
                        actions: ['deliver', 'return'],
                        transitions: {
                            'deliver': 'delivered',
                            'return': 'returned'
                        }
                    },
                    'delivered': {
                        actions: [],
                        transitions: {}
                    },
                    'cancelled': {
                        actions: [],
                        transitions: {}
                    },
                    'returned': {
                        actions: [],
                        transitions: {}
                    }
                };
            }
            
            getCurrentState() {
                return this.currentState;
            }
            
            getAvailableActions() {
                return this.states[this.currentState].actions;
            }
            
            canTransitionTo(action) {
                return this.states[this.currentState].actions.includes(action);
            }
            
            transition(action) {
                if (this.canTransitionTo(action)) {
                    const newState = this.states[this.currentState].transitions[action];
                    if (newState) {
                        this.history.push({
                            from: this.currentState,
                            to: newState,
                            action: action,
                            timestamp: new Date().toLocaleTimeString()
                        });
                        this.currentState = newState;
                        this.updateUI();
                        return true;
                    }
                }
                return false;
            }
            
            updateUI() {
                document.getElementById('current-state').value = this.currentState;
                this.updateActions();
                this.updateHistory();
            }
            
            updateActions() {
                const actionsDiv = document.getElementById('state-actions');
                actionsDiv.innerHTML = '';
                
                this.getAvailableActions().forEach(action => {
                    const button = document.createElement('button');
                    button.className = 'btn btn-primary me-2';
                    button.textContent = action.charAt(0).toUpperCase() + action.slice(1);
                    button.id = action + '-btn';
                    button.addEventListener('click', () => this.transition(action));
                    actionsDiv.appendChild(button);
                });
            }
            
            updateHistory() {
                const historyDiv = document.getElementById('state-history');
                if (this.history.length === 0) {
                    historyDiv.innerHTML = '<p class="text-muted">No state changes yet</p>';
                } else {
                    let html = '<ul class="list-group list-group-flush">';
                    this.history.forEach(change => {
                        html += `<li class="list-group-item">
                            <strong>${change.action}</strong>: ${change.from} → ${change.to}
                            <br><small class="text-muted">${change.timestamp}</small>
                        </li>`;
                    });
                    html += '</ul>';
                    historyDiv.innerHTML = html;
                }
            }
        }
        
        // Inizializza il sistema
        const stateMachine = new OrderStateMachine();
        stateMachine.updateUI();
    </script>
</body>
</html>
