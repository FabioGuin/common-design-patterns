<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mediator Pattern - Esempio Completo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-comments text-primary"></i>
            Mediator Pattern - Esempio Completo
        </h1>
        <p class="text-center text-muted mb-5">
            Sistema di comunicazione centralizzata tra componenti
        </p>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Form Components</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username:</label>
                            <input type="text" class="form-control" id="username" placeholder="Enter username">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="email" placeholder="Enter email">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password:</label>
                            <input type="password" class="form-control" id="password" placeholder="Enter password">
                        </div>
                        <button class="btn btn-primary" id="submit-btn">Submit</button>
                        <button class="btn btn-secondary" id="clear-btn">Clear</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Component Status</h5>
                    </div>
                    <div class="card-body">
                        <div id="component-status">
                            <p><strong>Username:</strong> <span class="text-success">Valid</span></p>
                            <p><strong>Email:</strong> <span class="text-success">Valid</span></p>
                            <p><strong>Password:</strong> <span class="text-success">Valid</span></p>
                            <p><strong>Submit Button:</strong> <span class="text-success">Enabled</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simula Mediator Pattern
        class FormMediator {
            constructor() {
                this.components = {};
            }
            
            registerComponent(name, component) {
                this.components[name] = component;
            }
            
            notify(sender, event, data) {
                Object.keys(this.components).forEach(name => {
                    if (this.components[name] !== sender) {
                        this.components[name].handleEvent(event, data);
                    }
                });
            }
        }
        
        class FormComponent {
            constructor(name, element) {
                this.name = name;
                this.element = element;
                this.mediator = null;
            }
            
            setMediator(mediator) {
                this.mediator = mediator;
            }
            
            handleEvent(event, data) {
                if (event === 'validation_failed') {
                    this.element.classList.add('is-invalid');
                } else if (event === 'validation_passed') {
                    this.element.classList.remove('is-invalid');
                }
            }
            
            notify(event, data) {
                if (this.mediator) {
                    this.mediator.notify(this, event, data);
                }
            }
        }
        
        // Inizializza il sistema
        const mediator = new FormMediator();
        const username = new FormComponent('username', document.getElementById('username'));
        const email = new FormComponent('email', document.getElementById('email'));
        const password = new FormComponent('password', document.getElementById('password'));
        
        mediator.registerComponent('username', username);
        mediator.registerComponent('email', email);
        mediator.registerComponent('password', password);
        
        username.setMediator(mediator);
        email.setMediator(mediator);
        password.setMediator(mediator);
        
        // Event listeners
        document.getElementById('username').addEventListener('input', function() {
            if (this.value.length < 3) {
                username.notify('validation_failed', { field: 'username' });
            } else {
                username.notify('validation_passed', { field: 'username' });
            }
        });
        
        document.getElementById('email').addEventListener('input', function() {
            if (!this.value.includes('@')) {
                email.notify('validation_failed', { field: 'email' });
            } else {
                email.notify('validation_passed', { field: 'email' });
            }
        });
        
        document.getElementById('password').addEventListener('input', function() {
            if (this.value.length < 6) {
                password.notify('validation_failed', { field: 'password' });
            } else {
                password.notify('validation_passed', { field: 'password' });
            }
        });
    </script>
</body>
</html>
