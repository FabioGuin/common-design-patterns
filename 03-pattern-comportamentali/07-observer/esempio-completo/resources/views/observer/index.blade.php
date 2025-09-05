<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Observer Pattern - Esempio Completo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-bell text-primary"></i>
            Observer Pattern - Esempio Completo
        </h1>
        <p class="text-center text-muted mb-5">
            Sistema di notifiche e eventi per ordini
        </p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Order Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="order-id" class="form-label">Order ID:</label>
                            <input type="text" class="form-control" id="order-id" value="ORD-001" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="order-status" class="form-label">Status:</label>
                            <select class="form-select" id="order-status">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <button class="btn btn-primary" id="update-status">Update Status</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Notifications</h5>
                    </div>
                    <div class="card-body">
                        <div id="notifications" style="max-height: 300px; overflow-y: auto;">
                            <p class="text-muted">No notifications yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simula Observer Pattern
        class Order {
            constructor() {
                this.observers = [];
                this.status = 'pending';
                this.data = {};
            }
            
            attach(observer) {
                this.observers.push(observer);
            }
            
            detach(observer) {
                const index = this.observers.indexOf(observer);
                if (index > -1) {
                    this.observers.splice(index, 1);
                }
            }
            
            notify() {
                this.observers.forEach(observer => {
                    observer.update(this);
                });
            }
            
            setStatus(status) {
                this.status = status;
                this.notify();
            }
            
            getStatus() {
                return this.status;
            }
        }
        
        class EmailObserver {
            constructor() {
                this.name = 'Email Notification';
            }
            
            update(subject) {
                this.addNotification(`📧 ${this.name}: Order status changed to ${subject.getStatus()}`);
            }
            
            addNotification(message) {
                const notificationsDiv = document.getElementById('notifications');
                const notification = document.createElement('div');
                notification.className = 'alert alert-info alert-dismissible fade show';
                notification.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                notificationsDiv.insertBefore(notification, notificationsDiv.firstChild);
            }
        }
        
        class SMSObserver {
            constructor() {
                this.name = 'SMS Notification';
            }
            
            update(subject) {
                this.addNotification(`📱 ${this.name}: Order status changed to ${subject.getStatus()}`);
            }
            
            addNotification(message) {
                const notificationsDiv = document.getElementById('notifications');
                const notification = document.createElement('div');
                notification.className = 'alert alert-warning alert-dismissible fade show';
                notification.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                notificationsDiv.insertBefore(notification, notificationsDiv.firstChild);
            }
        }
        
        class LogObserver {
            constructor() {
                this.name = 'System Log';
            }
            
            update(subject) {
                this.addNotification(`📝 ${this.name}: Order status changed to ${subject.getStatus()}`);
            }
            
            addNotification(message) {
                const notificationsDiv = document.getElementById('notifications');
                const notification = document.createElement('div');
                notification.className = 'alert alert-secondary alert-dismissible fade show';
                notification.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                notificationsDiv.insertBefore(notification, notificationsDiv.firstChild);
            }
        }
        
        // Inizializza il sistema
        const order = new Order();
        const emailObserver = new EmailObserver();
        const smsObserver = new SMSObserver();
        const logObserver = new LogObserver();
        
        order.attach(emailObserver);
        order.attach(smsObserver);
        order.attach(logObserver);
        
        // Event listeners
        document.getElementById('update-status').addEventListener('click', function() {
            const status = document.getElementById('order-status').value;
            order.setStatus(status);
        });
        
        // Inizializza
        document.getElementById('notifications').innerHTML = '<p class="text-muted">No notifications yet</p>';
    </script>
</body>
</html>
