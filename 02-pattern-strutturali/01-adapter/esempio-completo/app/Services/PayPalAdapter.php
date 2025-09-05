<?php

namespace App\Services;

use PayPal\Rest\ApiContext;
use PayPal\Api\Payment;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\Payer;
use PayPal\Api\RedirectUrls;
use PayPal\Exception\PayPalConnectionException;

class PayPalAdapter implements PaymentProcessorInterface
{
    private ApiContext $apiContext;

    public function __construct()
    {
        $this->apiContext = new ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                config('services.paypal.client_id'),
                config('services.paypal.client_secret')
            )
        );

        $this->apiContext->setConfig([
            'mode' => config('services.paypal.mode', 'sandbox'),
        ]);
    }

    /**
     * Processa un pagamento tramite PayPal
     */
    public function processPayment(float $amount, string $currency = 'USD', array $metadata = []): array
    {
        try {
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');

            $amountObj = new Amount();
            $amountObj->setTotal(number_format($amount, 2, '.', ''));
            $amountObj->setCurrency($currency);

            $transaction = new Transaction();
            $transaction->setAmount($amountObj);
            $transaction->setDescription('Pagamento tramite PayPal');

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl(route('payments.success'))
                        ->setCancelUrl(route('payments.cancel'));

            $payment = new Payment();
            $payment->setIntent('sale')
                   ->setPayer($payer)
                   ->setTransactions([$transaction])
                   ->setRedirectUrls($redirectUrls);

            $payment->create($this->apiContext);

            return [
                'success' => true,
                'payment_id' => $payment->getId(),
                'status' => $payment->getState(),
                'amount' => $amount,
                'currency' => $currency,
                'provider' => $this->getProviderName(),
                'approval_url' => $this->getApprovalUrl($payment),
            ];
        } catch (PayPalConnectionException $e) {
            return [
                'success' => false,
                'error' => $e->getData(),
                'provider' => $this->getProviderName(),
            ];
        }
    }

    /**
     * Verifica lo stato di un pagamento PayPal
     */
    public function getPaymentStatus(string $paymentId): array
    {
        try {
            $payment = Payment::get($paymentId, $this->apiContext);

            return [
                'success' => true,
                'payment_id' => $payment->getId(),
                'status' => $payment->getState(),
                'amount' => $this->getPaymentAmount($payment),
                'currency' => $this->getPaymentCurrency($payment),
                'provider' => $this->getProviderName(),
            ];
        } catch (PayPalConnectionException $e) {
            return [
                'success' => false,
                'error' => $e->getData(),
                'provider' => $this->getProviderName(),
            ];
        }
    }

    /**
     * Rimborsa un pagamento PayPal
     */
    public function refundPayment(string $paymentId, ?float $amount = null): array
    {
        try {
            // Per semplicità, simuliamo un rimborso
            // In un'implementazione reale, useresti l'API di rimborso di PayPal
            return [
                'success' => true,
                'refund_id' => 'refund_' . uniqid(),
                'status' => 'completed',
                'amount' => $amount,
                'provider' => $this->getProviderName(),
                'note' => 'Rimborso simulato per demo',
            ];
        } catch (PayPalConnectionException $e) {
            return [
                'success' => false,
                'error' => $e->getData(),
                'provider' => $this->getProviderName(),
            ];
        }
    }

    /**
     * Ottiene il nome del provider
     */
    public function getProviderName(): string
    {
        return 'PayPal';
    }

    /**
     * Ottiene l'URL di approvazione per PayPal
     */
    private function getApprovalUrl(Payment $payment): string
    {
        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() === 'approval_url') {
                return $link->getHref();
            }
        }
        return '';
    }

    /**
     * Estrae l'importo dal pagamento PayPal
     */
    private function getPaymentAmount(Payment $payment): float
    {
        $transactions = $payment->getTransactions();
        if (!empty($transactions)) {
            $amount = $transactions[0]->getAmount();
            return (float) $amount->getTotal();
        }
        return 0.0;
    }

    /**
     * Estrae la valuta dal pagamento PayPal
     */
    private function getPaymentCurrency(Payment $payment): string
    {
        $transactions = $payment->getTransactions();
        if (!empty($transactions)) {
            $amount = $transactions[0]->getAmount();
            return strtoupper($amount->getCurrency());
        }
        return 'USD';
    }
}
