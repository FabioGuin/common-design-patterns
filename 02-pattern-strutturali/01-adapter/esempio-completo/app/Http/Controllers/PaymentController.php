<?php

namespace App\Http\Controllers;

use App\Services\PaymentProcessorInterface;
use App\Services\StripeAdapter;
use App\Services\PayPalAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    private PaymentProcessorInterface $paymentProcessor;

    public function __construct()
    {
        $this->setPaymentProcessor();
    }

    /**
     * Mostra la pagina principale dei pagamenti
     */
    public function index()
    {
        return view('payments.index', [
            'currentProvider' => $this->getCurrentProvider(),
            'availableProviders' => $this->getAvailableProviders(),
        ]);
    }

    /**
     * Processa un pagamento
     */
    public function processPayment(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'provider' => 'required|string|in:stripe,paypal',
        ]);

        // Cambia provider se richiesto
        if ($request->provider !== $this->getCurrentProvider()) {
            $this->setPaymentProcessor($request->provider);
        }

        $result = $this->paymentProcessor->processPayment(
            $request->amount,
            strtoupper($request->currency),
            [
                'user_id' => auth()->id() ?? 'guest',
                'timestamp' => now()->toISOString(),
                'ip_address' => $request->ip(),
            ]
        );

        return response()->json($result);
    }

    /**
     * Verifica lo stato di un pagamento
     */
    public function getPaymentStatus(Request $request): JsonResponse
    {
        $request->validate([
            'payment_id' => 'required|string',
            'provider' => 'required|string|in:stripe,paypal',
        ]);

        // Cambia provider se richiesto
        if ($request->provider !== $this->getCurrentProvider()) {
            $this->setPaymentProcessor($request->provider);
        }

        $result = $this->paymentProcessor->getPaymentStatus($request->payment_id);

        return response()->json($result);
    }

    /**
     * Rimborsa un pagamento
     */
    public function refundPayment(Request $request): JsonResponse
    {
        $request->validate([
            'payment_id' => 'required|string',
            'amount' => 'nullable|numeric|min:0.01',
            'provider' => 'required|string|in:stripe,paypal',
        ]);

        // Cambia provider se richiesto
        if ($request->provider !== $this->getCurrentProvider()) {
            $this->setPaymentProcessor($request->provider);
        }

        $result = $this->paymentProcessor->refundPayment(
            $request->payment_id,
            $request->amount
        );

        return response()->json($result);
    }

    /**
     * Cambia il provider di pagamento
     */
    public function switchProvider(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|string|in:stripe,paypal',
        ]);

        $this->setPaymentProcessor($request->provider);

        return response()->json([
            'success' => true,
            'provider' => $this->getCurrentProvider(),
            'message' => "Provider cambiato a {$this->paymentProcessor->getProviderName()}",
        ]);
    }

    /**
     * Imposta il processor di pagamento
     */
    private function setPaymentProcessor(?string $provider = null): void
    {
        $provider = $provider ?? config('payments.default_provider', 'stripe');

        $this->paymentProcessor = match ($provider) {
            'stripe' => new StripeAdapter(),
            'paypal' => new PayPalAdapter(),
            default => new StripeAdapter(),
        };
    }

    /**
     * Ottiene il provider corrente
     */
    private function getCurrentProvider(): string
    {
        return strtolower($this->paymentProcessor->getProviderName());
    }

    /**
     * Ottiene i provider disponibili
     */
    private function getAvailableProviders(): array
    {
        return [
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
        ];
    }
}
