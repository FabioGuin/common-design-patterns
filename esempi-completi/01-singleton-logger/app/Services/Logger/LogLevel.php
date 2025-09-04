<?php

namespace App\Services\Logger;

/**
 * Enum per i livelli di log
 * Definisce i diversi livelli di severità per i messaggi di log
 */
enum LogLevel: string
{
    case DEBUG = 'debug';
    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
    case CRITICAL = 'critical';

    /**
     * Ottiene il livello di priorità numerica
     */
    public function getPriority(): int
    {
        return match($this) {
            self::DEBUG => 0,
            self::INFO => 1,
            self::WARNING => 2,
            self::ERROR => 3,
            self::CRITICAL => 4,
        };
    }

    /**
     * Verifica se il livello è maggiore o uguale a un altro livello
     */
    public function isGreaterOrEqual(LogLevel $level): bool
    {
        return $this->getPriority() >= $level->getPriority();
    }

    /**
     * Ottiene tutti i livelli disponibili
     */
    public static function getAllLevels(): array
    {
        return [
            self::DEBUG->value,
            self::INFO->value,
            self::WARNING->value,
            self::ERROR->value,
            self::CRITICAL->value,
        ];
    }
}
