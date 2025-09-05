<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strategy Pattern - Esempio Completo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-credit-card text-primary"></i>
            Strategy Pattern - Esempio Completo
        </h1>
        <p class="text-center text-muted mb-5">
            Sistema di pagamento con strategie intercambiabili
        </p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Payment Processing</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount (€):</label>
                            <input type="number" class="form-control" id="amount" value="100" min="1">
                        </div>
                        <div class="mb-3">
                            <label for="payment-method" class="form-label">Payment Method:</label>
                            <select class="form-select" id="payment-method">
                                <option value="credit_card">Credit Card</option>
                                <option value="paypal">PayPal</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <button class="btn btn-primary" id="process-payment">Process Payment</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Payment Results</h5>
                    </div>
                    <div class="card-body">
                        <div id="payment-results">
                            <p class="text-muted">No payments processed yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simula Strategy Pattern
        class PaymentStrategy {
            execute(amount) {
                throw new Error('Method must be implemented');
            }
        }
        
        class CreditCardStrategy extends PaymentStrategy {
            execute(amount) {
                return {
                    success: Math.random() > 0.3,
                    message: `Credit Card payment of €${amount} processed`,
                    method: 'credit_card'
                };
            }
        }
        
        class PayPalStrategy extends PaymentStrategy {
            execute(amount) {
                return {
                    success: Math.random() > 0.2,
                    message: `PayPal payment of €${amount} processed`,
                    method: 'paypal'
                };
            }
        }
        
        class BankTransferStrategy extends PaymentStrategy {
            execute(amount) {
                return {
                    success: Math.random() > 0.1,
                    message: `Bank Transfer of €${amount} processed`,
                    method: 'bank_transfer'
                };
            }
        }
        
        class PaymentProcessor {
            constructor() {
                this.strategies = {
                    'credit_card': new CreditCardStrategy(),
                    'paypal': new PayPalStrategy(),
                    'bank_transfer': new BankTransferStrategy()
                };
            }
            
            processPayment(amount, method) {
                const strategy = this.strategies[method];
                if (strategy) {
                    return strategy.execute(amount);
                }
                throw new Error('Invalid payment method');
            }
        }
        
        // Inizializza il sistema
        const paymentProcessor = new PaymentProcessor();
        
        // Event listeners
        document.getElementById('process-payment').addEventListener('click', function() {
            const amount = parseFloat(document.getElementById('amount').value);
            const method = document.getElementById('payment-method').value;
            
            try {
                const result = paymentProcessor.processPayment(amount, method);
                displayResult(result);
            } catch (error) {
                displayResult({
                    success: false,
                    message: error.message,
                    method: method
                });
            }
        });
        
        function displayResult(result) {
            const resultsDiv = document.getElementById('payment-results');
            const resultDiv = document.createElement('div');
            resultDiv.className = `alert alert-${result.success ? 'success' : 'danger'} alert-dismissible fade show`;
            resultDiv.innerHTML = `
                <strong>${result.method.toUpperCase()}:</strong> ${result.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            resultsDiv.insertBefore(resultDiv, resultsDiv.firstChild);
        }
    </script>
</body>
</html>
