<?php

namespace App\Services;

class HTMLFormatter implements MessageFormatterInterface
{
    /**
     * Formatta un messaggio in HTML
     */
    public function format(string $message, array $data = []): string
    {
        $html = '<div class="notification">';
        $html .= '<h3>' . htmlspecialchars($data['title'] ?? 'Notifica') . '</h3>';
        $html .= '<p>' . htmlspecialchars($message) . '</p>';
        
        if (!empty($data['details'])) {
            $html .= '<div class="details">';
            $html .= '<h4>Dettagli:</h4>';
            $html .= '<ul>';
            foreach ($data['details'] as $key => $value) {
                $html .= '<li><strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($value) . '</li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }
        
        if (!empty($data['action_url'])) {
            $html .= '<div class="actions">';
            $html .= '<a href="' . htmlspecialchars($data['action_url']) . '" class="btn btn-primary">';
            $html .= htmlspecialchars($data['action_text'] ?? 'Visualizza');
            $html .= '</a>';
            $html .= '</div>';
        }
        
        $html .= '<div class="footer">';
        $html .= '<small>Inviato il ' . date('d/m/Y H:i:s') . '</small>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Ottiene il tipo di formattatore
     */
    public function getType(): string
    {
        return 'html';
    }

    /**
     * Verifica se il formattatore supporta un tipo di dato
     */
    public function supports(string $type): bool
    {
        return in_array($type, ['html', 'web', 'email']);
    }
}
