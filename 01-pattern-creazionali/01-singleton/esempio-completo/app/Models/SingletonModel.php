<?php

namespace App\Models;

class SingletonModel
{
    private static ?SingletonModel $instance = null;
    private string $id;
    private int $accessCount = 0;
    private array $data = [];

    /**
     * Costruttore privato per impedire l'istanziazione diretta
     */
    private function __construct()
    {
        $this->id = uniqid('singleton_', true);
        $this->data = [
            'created_at' => $this->getCurrentDateTime(),
            'version' => '1.0.0',
            'description' => 'Singleton Pattern Implementation'
        ];
    }

    /**
     * Metodo per ottenere l'istanza unica del Singleton
     */
    public static function getInstance(): SingletonModel
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    /**
     * Metodo per ottenere l'ID univoco dell'istanza
     */
    public function getId(): string
    {
        $this->accessCount++;
        return $this->id;
    }

    /**
     * Metodo per ottenere il numero di accessi
     */
    public function getAccessCount(): int
    {
        return $this->accessCount;
    }

    /**
     * Metodo per ottenere i dati dell'istanza
     */
    public function getData(): array
    {
        $this->accessCount++;
        return $this->data;
    }

    /**
     * Metodo per aggiungere dati all'istanza
     */
    public function addData(string $key, mixed $value): void
    {
        $this->accessCount++;
        $this->data[$key] = $value;
    }

    /**
     * Metodo per ottenere informazioni complete dell'istanza
     */
    public function getInfo(): array
    {
        $this->accessCount++;
        return [
            'id' => $this->id,
            'access_count' => $this->accessCount,
            'data' => $this->data,
            'memory_usage' => memory_get_usage(true),
            'timestamp' => $this->getCurrentDateTime()
        ];
    }

    /**
     * Helper per ottenere la data corrente
     */
    private function getCurrentDateTime(): string
    {
        // Usa la funzione helper di Laravel
        return now()->toDateTimeString();
    }

    /**
     * Impedisce la clonazione dell'istanza
     */
    private function __clone()
    {
        throw new \Exception('Cannot clone a singleton instance');
    }

    /**
     * Impedisce la deserializzazione dell'istanza
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize a singleton instance');
    }
}
