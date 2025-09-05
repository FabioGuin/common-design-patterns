<?php

namespace App\Services;

interface NotificationInterface
{
    /**
     * Invia una notifica
     *
     * @param string $message Messaggio da inviare
     * @param array $data Dati aggiuntivi
     * @return array Risultato dell'invio
     */
    public function send(string $message, array $data = []): array;

    /**
     * Ottiene il tipo di notifica
     *
     * @return string Tipo di notifica
     */
    public function getType(): string;

    /**
     * Ottiene il costo della notifica
     *
     * @return float Costo della notifica
     */
    public function getCost(): float;

    /**
     * Verifica se la notifica è disponibile
     *
     * @return bool True se disponibile
     */
    public function isAvailable(): bool;

    /**
     * Ottiene la descrizione della notifica
     *
     * @return string Descrizione della notifica
     */
    public function getDescription(): string;
}
