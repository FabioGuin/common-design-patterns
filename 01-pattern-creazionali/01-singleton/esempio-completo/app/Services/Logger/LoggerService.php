<?php

namespace App\Services\Logger;

use Illuminate\Support\Facades\Storage;

/**
 * LoggerService - Implementazione Singleton Pattern
 * 
 * Garantisce una sola istanza del logger per tutta l'applicazione
 * Gestisce diversi livelli di log e persiste i dati su file
 */
class LoggerService
{
    private static ?LoggerService $instance = null;
    private array $logs = [];
    private string $logFile;
    private LogLevel $minLevel;

    /**
     * Costruttore privato per impedire istanziazione diretta
     */
    private function __construct(string $logFile = 'custom.log', LogLevel $minLevel = LogLevel::DEBUG)
    {
        $this->logFile = $logFile;
        $this->minLevel = $minLevel;
        $this->loadExistingLogs();
    }

    /**
     * Ottiene l'istanza singleton del logger
     */
    public static function getInstance(string $logFile = null, LogLevel $minLevel = null): LoggerService
    {
        if (self::$instance === null) {
            self::$instance = new self($logFile, $minLevel);
        }
        return self::$instance;
    }

    /**
     * Log generico con livello specificato
     */
    public function log(LogLevel $level, string $message, array $context = []): void
    {
        // Filtra per livello minimo
        if (!$level->isGreaterOrEqual($this->minLevel)) {
            return;
        }

        $logEntry = new LogEntry($level, $message, $context);
        $this->logs[] = $logEntry;
        $this->writeToFile($logEntry);
    }

    /**
     * Log di livello DEBUG
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Log di livello INFO
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Log di livello WARNING
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Log di livello ERROR
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Log di livello CRITICAL
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Ottiene tutti i logs
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * Ottiene i logs filtrati per livello
     */
    public function getLogsByLevel(LogLevel $level): array
    {
        return array_filter($this->logs, fn($log) => $log->level === $level);
    }

    /**
     * Ottiene i logs filtrati per livello minimo
     */
    public function getLogsByMinLevel(LogLevel $minLevel): array
    {
        return array_filter($this->logs, fn($log) => $log->level->isGreaterOrEqual($minLevel));
    }

    /**
     * Ottiene le statistiche dei logs
     */
    public function getStats(): array
    {
        $stats = [];
        foreach (LogLevel::getAllLevels() as $level) {
            $stats[$level] = count($this->getLogsByLevel(LogLevel::from($level)));
        }
        $stats['total'] = count($this->logs);
        return $stats;
    }

    /**
     * Cancella tutti i logs
     */
    public function clearLogs(): void
    {
        $this->logs = [];
        if (Storage::disk('local')->exists($this->logFile)) {
            Storage::disk('local')->delete($this->logFile);
        }
    }

    /**
     * Imposta il livello minimo di log
     */
    public function setMinLevel(LogLevel $minLevel): void
    {
        $this->minLevel = $minLevel;
    }

    /**
     * Ottiene il livello minimo di log
     */
    public function getMinLevel(): LogLevel
    {
        return $this->minLevel;
    }

    /**
     * Carica i logs esistenti dal file
     */
    private function loadExistingLogs(): void
    {
        if (Storage::disk('local')->exists($this->logFile)) {
            $content = Storage::disk('local')->get($this->logFile);
            $lines = explode("\n", $content);
            
            foreach ($lines as $line) {
                if (trim($line)) {
                    $data = json_decode($line, true);
                    if ($data && isset($data['level'], $data['message'], $data['timestamp'])) {
                        $this->logs[] = LogEntry::fromArray($data);
                    }
                }
            }
        }
    }

    /**
     * Scrive una LogEntry su file
     */
    private function writeToFile(LogEntry $logEntry): void
    {
        $logLine = json_encode($logEntry->toArray()) . "\n";
        Storage::disk('local')->append($this->logFile, $logLine);
    }

    /**
     * Impedisce la clonazione dell'istanza
     */
    private function __clone() {}

    /**
     * Impedisce la deserializzazione dell'istanza
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
