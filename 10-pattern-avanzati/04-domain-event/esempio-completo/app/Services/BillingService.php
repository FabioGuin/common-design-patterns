<?php

namespace App\Services;

/**
 * Servizio per gestire la fatturazione
 * 
 * Gestisce la creazione di fatture, gestione
 * dei pagamenti e report finanziari.
 */
class BillingService
{
    private array $invoices = [];
    private array $payments = [];

    /**
     * Crea una fattura per un ordine
     */
    public function createInvoice(
        string $orderId,
        string $customerId,
        float $total,
        array $items
    ): string {
        $invoiceId = 'INV-' . uniqid();
        
        $invoice = [
            'id' => $invoiceId,
            'orderId' => $orderId,
            'customerId' => $customerId,
            'total' => $total,
            'items' => $items,
            'status' => 'PENDING',
            'createdAt' => (new \DateTime())->format('Y-m-d H:i:s'),
            'dueDate' => (new \DateTime('+30 days'))->format('Y-m-d H:i:s')
        ];
        
        $this->invoices[$invoiceId] = $invoice;
        
        return $invoiceId;
    }

    /**
     * Processa un pagamento
     */
    public function processPayment(
        string $invoiceId,
        string $paymentMethod,
        float $amount,
        string $transactionId
    ): bool {
        if (!isset($this->invoices[$invoiceId])) {
            throw new \InvalidArgumentException("Invoice {$invoiceId} not found");
        }

        $invoice = $this->invoices[$invoiceId];
        
        if ($amount !== $invoice['total']) {
            throw new \InvalidArgumentException("Payment amount does not match invoice total");
        }

        $payment = [
            'id' => uniqid('PAY_'),
            'invoiceId' => $invoiceId,
            'orderId' => $invoice['orderId'],
            'customerId' => $invoice['customerId'],
            'amount' => $amount,
            'paymentMethod' => $paymentMethod,
            'transactionId' => $transactionId,
            'status' => 'COMPLETED',
            'processedAt' => (new \DateTime())->format('Y-m-d H:i:s')
        ];
        
        $this->payments[] = $payment;
        
        // Aggiorna lo status della fattura
        $this->invoices[$invoiceId]['status'] = 'PAID';
        $this->invoices[$invoiceId]['paidAt'] = $payment['processedAt'];
        
        return true;
    }

    /**
     * Restituisce una fattura per ID
     */
    public function getInvoice(string $invoiceId): ?array
    {
        return $this->invoices[$invoiceId] ?? null;
    }

    /**
     * Restituisce tutte le fatture
     */
    public function getAllInvoices(): array
    {
        return $this->invoices;
    }

    /**
     * Restituisce le fatture per customer
     */
    public function getInvoicesByCustomer(string $customerId): array
    {
        return array_filter($this->invoices, function ($invoice) use ($customerId) {
            return $invoice['customerId'] === $customerId;
        });
    }

    /**
     * Restituisce le fatture per status
     */
    public function getInvoicesByStatus(string $status): array
    {
        return array_filter($this->invoices, function ($invoice) use ($status) {
            return $invoice['status'] === $status;
        });
    }

    /**
     * Restituisce tutti i pagamenti
     */
    public function getAllPayments(): array
    {
        return $this->payments;
    }

    /**
     * Restituisce i pagamenti per customer
     */
    public function getPaymentsByCustomer(string $customerId): array
    {
        return array_filter($this->payments, function ($payment) use ($customerId) {
            return $payment['customerId'] === $customerId;
        });
    }

    /**
     * Restituisce le statistiche finanziarie
     */
    public function getStatistics(): array
    {
        $totalInvoices = count($this->invoices);
        $totalPayments = count($this->payments);
        $totalRevenue = array_sum(array_column($this->payments, 'amount'));
        $pendingInvoices = count($this->getInvoicesByStatus('PENDING'));
        $paidInvoices = count($this->getInvoicesByStatus('PAID'));

        return [
            'totalInvoices' => $totalInvoices,
            'totalPayments' => $totalPayments,
            'totalRevenue' => $totalRevenue,
            'pendingInvoices' => $pendingInvoices,
            'paidInvoices' => $paidInvoices,
            'averageInvoiceValue' => $totalInvoices > 0 ? round($totalRevenue / $totalInvoices, 2) : 0
        ];
    }

    /**
     * Pulisce le fatture e i pagamenti
     */
    public function clear(): void
    {
        $this->invoices = [];
        $this->payments = [];
    }
}
