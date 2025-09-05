<?php

namespace App\Services;

interface MessageFormatterInterface
{
    /**
     * Formatta un messaggio con i dati forniti
     *
     * @param string $message Messaggio da formattare
     * @param array $data Dati per la formattazione
     * @return string Messaggio formattato
     */
    public function format(string $message, array $data = []): string;

    /**
     * Ottiene il tipo di formattatore
     *
     * @return string Tipo del formattatore
     */
    public function getType(): string;

    /**
     * Verifica se il formattatore supporta un tipo di dato
     *
     * @param string $type Tipo di dato
     * @return bool True se supportato
     */
    public function supports(string $type): bool;
}
