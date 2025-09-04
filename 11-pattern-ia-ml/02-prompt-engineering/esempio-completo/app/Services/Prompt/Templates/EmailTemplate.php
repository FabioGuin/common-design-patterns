<?php

namespace App\Services\Prompt\Templates;

class EmailTemplate extends BaseTemplate
{
    protected string $name = 'Email Promozionale';
    protected string $description = 'Template per email marketing personalizzate e persuasive';
    protected array $variables = ['customer_name', 'product', 'discount', 'expiry_date'];
    protected array $validationRules = [
        'min_length' => 200,
        'max_length' => 800,
        'required_keywords' => ['sconto', 'offerta'],
        'required_sections' => ['oggetto', 'corpo', 'cta']
    ];
    protected float $costEstimate = 0.003;
    protected float $expectedDuration = 3.0;

    public function getTemplate(): string
    {
        return <<<PROMPT
CONTESTO: Sei un esperto di email marketing per un e-commerce.

OBIETTIVO: Crea un'email promozionale persuasiva e personalizzata.

FORMATO RICHIESTO:
- Oggetto accattivante (max 50 caratteri)
- Corpo email in 2-3 paragrafi
- Call-to-action chiara e visibile
- Tono amichevole e professionale

CLIENTE: {{customer_name}}
PRODOTTO: {{product}}
SCONTO: {{discount}}
SCADENZA: {{expiry_date}}

VINCOLI:
- Personalizza per il cliente specifico
- Crea urgenza con la scadenza dell'offerta
- Evidenzia il valore dello sconto
- Mantieni un tono professionale ma caloroso
- Includi benefici chiari per il cliente
- Usa un linguaggio diretto e persuasivo

STRUTTURA RICHIESTA:
1. OGGETTO: [Titolo accattivante con sconto]
2. SALUTO: Personalizzato per il cliente
3. PARAGRAFO 1: Introduzione offerta e prodotto
4. PARAGRAFO 2: Benefici e valore dello sconto
5. CALL-TO-ACTION: Pulsante o link chiaro
6. CHIUSURA: Urgenza e scadenza

TECNICHE DI PERSUASIONE:
- Scarsità: "Solo per pochi giorni"
- Autorità: "Offerta esclusiva per clienti VIP"
- Reciprocità: "Come ringraziamento per la tua fedeltà"
- Prova sociale: "Migliaia di clienti soddisfatti"

REQUISITI TECNICI:
- Oggetto: 30-50 caratteri, senza emoji eccessivi
- Corpo: 200-600 parole
- CTA: Massimo 3 parole, azione chiara
- Personalizzazione: Nome cliente almeno 2 volte
PROMPT;
    }

    protected function getRequiredVariables(): array
    {
        return ['customer_name', 'product', 'discount'];
    }

    protected function getOptionalVariables(): array
    {
        return ['expiry_date', 'brand', 'previous_purchase', 'loyalty_level'];
    }

    public function getExamples(): array
    {
        return [
            [
                'input' => [
                    'customer_name' => 'Mario Rossi',
                    'product' => 'iPhone 15 Pro',
                    'discount' => '15%',
                    'expiry_date' => '2024-12-31'
                ],
                'output' => 'OGGETTO: Mario, sconto 15% su iPhone 15 Pro!\n\nCiao Mario,\n\nAbbiamo una sorpresa speciale per te! Fino al 31 dicembre, puoi acquistare l\'iPhone 15 Pro con uno sconto esclusivo del 15%.\n\nQuesto smartphone di ultima generazione offre prestazioni eccezionali, fotocamera professionale e design iconico. Con il tuo sconto, risparmierai oltre €180 sul prezzo di listino.\n\nNon perdere questa opportunità! L\'offerta scade il 31 dicembre.\n\nACQUISTA ORA\n\nGrazie per la tua fiducia,\nIl Team E-commerce'
            ],
            [
                'input' => [
                    'customer_name' => 'Laura Bianchi',
                    'product' => 'MacBook Air M2',
                    'discount' => '10%',
                    'expiry_date' => '2024-12-25'
                ],
                'output' => 'OGGETTO: Laura, MacBook Air M2 con 10% di sconto!\n\nCara Laura,\n\nCome cliente VIP, hai accesso a un\'offerta esclusiva: MacBook Air M2 con sconto del 10% fino al 25 dicembre.\n\nQuesto laptop unisce portabilità e potenza, perfetto per il tuo lavoro creativo. Con il chip M2, avrai prestazioni superiori e autonomia prolungata.\n\nL\'offerta è valida solo fino a Natale. Approfittane subito!\n\nORDINA ADESSO\n\nBuone feste,\nIl Team E-commerce'
            ]
        ];
    }

    public function getOptimizationTips(): array
    {
        return [
            'Usa sempre il nome del cliente nell\'oggetto e nel corpo',
            'Crea urgenza con scadenze specifiche e concrete',
            'Evidenzia il valore monetario dello sconto',
            'Includi benefici specifici del prodotto',
            'Usa call-to-action dirette e azionabili',
            'Testa diversi oggetti per migliorare l\'open rate',
            'Personalizza in base alla cronologia acquisti del cliente'
        ];
    }

    public function postprocessOutput(string $output): string
    {
        $output = parent::postprocessOutput($output);
        
        // Assicurati che ci sia un oggetto separato
        if (!preg_match('/^OGGETTO:/m', $output)) {
            $lines = explode("\n", $output);
            if (count($lines) > 1) {
                $firstLine = trim($lines[0]);
                if (strlen($firstLine) <= 50) {
                    $output = "OGGETTO: " . $firstLine . "\n\n" . implode("\n", array_slice($lines, 1));
                }
            }
        }
        
        // Assicurati che ci sia una CTA chiara
        if (!preg_match('/\b(ACQUISTA|ORDINA|COMPRA|SCEGLI|PRENOTA)\b/i', $output)) {
            $output .= "\n\nACQUISTA ORA";
        }
        
        // Formatta correttamente le sezioni
        $output = preg_replace('/\n(OGGETTO:)/', "\n\n$1", $output);
        $output = preg_replace('/\n(ACQUISTA|ORDINA|COMPRA|SCEGLI|PRENOTA)/', "\n\n$1", $output);
        
        return $output;
    }

    public function validateVariables(array $variables): array
    {
        $errors = parent::validateVariables($variables);
        
        // Validazioni specifiche per email
        if (isset($variables['customer_name']) && strlen($variables['customer_name']) < 2) {
            $errors[] = "Il nome del cliente deve essere specificato (min 2 caratteri)";
        }
        
        if (isset($variables['discount']) && !preg_match('/\d+%/', $variables['discount'])) {
            $errors[] = "Lo sconto deve essere in formato percentuale (es. 15%)";
        }
        
        if (isset($variables['expiry_date']) && !strtotime($variables['expiry_date'])) {
            $errors[] = "La data di scadenza deve essere valida (formato YYYY-MM-DD)";
        }
        
        if (isset($variables['product']) && strlen($variables['product']) < 3) {
            $errors[] = "Il prodotto deve essere specificato (min 3 caratteri)";
        }
        
        return $errors;
    }

    protected function preprocessVariable(string $key, $value): string
    {
        $value = parent::preprocessVariable($key, $value);
        
        // Preprocessing specifico per email
        if ($key === 'customer_name') {
            // Capitalizza correttamente il nome
            $value = ucwords(strtolower($value));
        }
        
        if ($key === 'discount') {
            // Assicurati che lo sconto abbia il simbolo %
            if (!str_contains($value, '%')) {
                $value = $value . '%';
            }
        }
        
        if ($key === 'expiry_date') {
            // Formatta la data in modo leggibile
            if (strtotime($value)) {
                $value = date('d/m/Y', strtotime($value));
            }
        }
        
        return $value;
    }
}
