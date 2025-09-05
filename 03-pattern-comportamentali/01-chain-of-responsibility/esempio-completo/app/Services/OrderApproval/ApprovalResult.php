<?php

namespace App\Services\OrderApproval;

class ApprovalResult
{
    public function __construct(
        public bool $approved,
        public string $message,
        public string $handlerClass,
        public array $metadata = []
    ) {}
    
    /**
     * Crea un risultato di approvazione
     */
    public static function approved(string $handlerClass, string $message = 'Approved', array $metadata = []): self
    {
        return new self(true, $message, $handlerClass, $metadata);
    }
    
    /**
     * Crea un risultato di rifiuto
     */
    public static function rejected(string $handlerClass, string $message = 'Rejected', array $metadata = []): self
    {
        return new self(false, $message, $handlerClass, $metadata);
    }
    
    /**
     * Crea un risultato di errore
     */
    public static function error(string $handlerClass, string $message = 'Error', array $metadata = []): self
    {
        return new self(false, "Error: {$message}", $handlerClass, $metadata);
    }
    
    /**
     * Verifica se il risultato è di approvazione
     */
    public function isApproved(): bool
    {
        return $this->approved;
    }
    
    /**
     * Verifica se il risultato è di rifiuto
     */
    public function isRejected(): bool
    {
        return !$this->approved;
    }
    
    /**
     * Aggiunge metadati al risultato
     */
    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }
    
    /**
     * Ottiene metadati dal risultato
     */
    public function getMetadata(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }
    
    /**
     * Converte il risultato in array
     */
    public function toArray(): array
    {
        return [
            'approved' => $this->approved,
            'message' => $this->message,
            'handler_class' => $this->handlerClass,
            'metadata' => $this->metadata
        ];
    }
}
