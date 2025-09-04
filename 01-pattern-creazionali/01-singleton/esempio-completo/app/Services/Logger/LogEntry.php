<?php

namespace App\Services\Logger;

/**
 * Classe per rappresentare una entry di log
 * Contiene tutte le informazioni relative a un singolo messaggio di log
 */
class LogEntry
{
    public function __construct(
        public readonly LogLevel $level,
        public readonly string $message,
        public readonly array $context = [],
        public readonly \DateTimeImmutable $timestamp = new \DateTimeImmutable()
    ) {}

    /**
     * Converte la LogEntry in array per serializzazione
     */
    public function toArray(): array
    {
        return [
            'level' => $this->level->value,
            'message' => $this->message,
            'context' => $this->context,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
            'timestamp_iso' => $this->timestamp->format('c'),
        ];
    }

    /**
     * Converte la LogEntry in JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    /**
     * Crea una LogEntry da array (per deserializzazione)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            LogLevel::from($data['level']),
            $data['message'],
            $data['context'] ?? [],
            new \DateTimeImmutable($data['timestamp'])
        );
    }

    /**
     * Formatta il messaggio per visualizzazione
     */
    public function formatMessage(): string
    {
        $contextStr = empty($this->context) ? '' : ' ' . json_encode($this->context);
        return "[{$this->timestamp->format('Y-m-d H:i:s')}] {$this->level->value}: {$this->message}{$contextStr}";
    }
}
