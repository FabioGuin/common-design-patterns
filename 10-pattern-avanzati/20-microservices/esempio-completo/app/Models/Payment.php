<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'gateway_transaction_id',
        'gateway_response'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Verifica se il pagamento è completato
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Verifica se il pagamento è fallito
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Verifica se il pagamento è rimborsato
     */
    public function isRefunded()
    {
        return $this->status === 'refunded';
    }

    /**
     * Verifica se il pagamento è in sospeso
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Verifica se il pagamento può essere rimborsato
     */
    public function canBeRefunded()
    {
        return $this->isCompleted();
    }

    /**
     * Aggiorna lo status del pagamento
     */
    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();
    }

    /**
     * Completa il pagamento
     */
    public function complete()
    {
        $this->status = 'completed';
        $this->save();
    }

    /**
     * Fallisce il pagamento
     */
    public function fail()
    {
        $this->status = 'failed';
        $this->save();
    }

    /**
     * Rimborsa il pagamento
     */
    public function refund()
    {
        $this->status = 'refunded';
        $this->save();
    }

    /**
     * Ottiene l'importo del pagamento
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Ottiene la valuta del pagamento
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Ottiene il metodo di pagamento
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * Ottiene l'ID della transazione del gateway
     */
    public function getGatewayTransactionId()
    {
        return $this->gateway_transaction_id;
    }

    /**
     * Ottiene la risposta del gateway
     */
    public function getGatewayResponse()
    {
        return $this->gateway_response;
    }

    /**
     * Imposta la risposta del gateway
     */
    public function setGatewayResponse($response)
    {
        $this->gateway_response = $response;
        $this->save();
    }

    /**
     * Verifica se il pagamento è positivo (non rimborso)
     */
    public function isPositive()
    {
        return $this->amount > 0;
    }

    /**
     * Verifica se il pagamento è negativo (rimborso)
     */
    public function isNegative()
    {
        return $this->amount < 0;
    }

    /**
     * Ottiene l'importo assoluto
     */
    public function getAbsoluteAmount()
    {
        return abs($this->amount);
    }

    /**
     * Converte il modello in array per API
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'gateway_transaction_id' => $this->gateway_transaction_id,
            'gateway_response' => $this->gateway_response,
            'is_completed' => $this->isCompleted(),
            'is_failed' => $this->isFailed(),
            'is_refunded' => $this->isRefunded(),
            'is_pending' => $this->isPending(),
            'can_be_refunded' => $this->canBeRefunded(),
            'is_positive' => $this->isPositive(),
            'is_negative' => $this->isNegative(),
            'absolute_amount' => $this->getAbsoluteAmount(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString()
        ];
    }
}
