<?php

namespace App\Services;

class TextFormatter implements MessageFormatterInterface
{
    /**
     * Formatta un messaggio in testo semplice
     */
    public function format(string $message, array $data = []): string
    {
        $text = "=" . str_repeat("=", 50) . "\n";
        $text .= "NOTIFICA\n";
        $text .= "=" . str_repeat("=", 50) . "\n\n";
        
        $text .= "Titolo: " . ($data['title'] ?? 'Notifica') . "\n";
        $text .= "Messaggio: " . $message . "\n\n";
        
        if (!empty($data['details'])) {
            $text .= "Dettagli:\n";
            $text .= "-" . str_repeat("-", 30) . "\n";
            foreach ($data['details'] as $key => $value) {
                $text .= "• " . $key . ": " . $value . "\n";
            }
            $text .= "\n";
        }
        
        if (!empty($data['action_url'])) {
            $text .= "Azione: " . ($data['action_text'] ?? 'Visualizza') . "\n";
            $text .= "URL: " . $data['action_url'] . "\n\n";
        }
        
        $text .= "Inviato il: " . date('d/m/Y H:i:s') . "\n";
        $text .= "=" . str_repeat("=", 50) . "\n";
        
        return $text;
    }

    /**
     * Ottiene il tipo di formattatore
     */
    public function getType(): string
    {
        return 'text';
    }

    /**
     * Verifica se il formattatore supporta un tipo di dato
     */
    public function supports(string $type): bool
    {
        return in_array($type, ['text', 'sms', 'console']);
    }
}
