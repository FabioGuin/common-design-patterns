<?php

namespace App\Services\Prompt\Templates;

class ProductDescriptionTemplate extends BaseTemplate
{
    protected string $name = 'Descrizione Prodotto';
    protected string $description = 'Template per generare descrizioni prodotto accattivanti e tecniche';
    protected array $variables = ['product_name', 'features', 'price', 'category'];
    protected array $validationRules = [
        'min_length' => 100,
        'max_length' => 500,
        'required_keywords' => ['caratteristiche', 'prezzo'],
        'forbidden_words' => ['fantastico', 'incredibile', 'migliore']
    ];
    protected float $costEstimate = 0.002;
    protected float $expectedDuration = 2.5;

    public function getTemplate(): string
    {
        return <<<PROMPT
CONTESTO: Sei un copywriter esperto per un e-commerce di elettronica.

OBIETTIVO: Crea una descrizione prodotto accattivante e tecnica.

FORMATO RICHIESTO:
- Titolo accattivante (max 60 caratteri)
- 3 paragrafi descrittivi (max 200 parole totali)
- Linguaggio tecnico ma accessibile
- Tono persuasivo e professionale

PRODOTTO: {{product_name}}
CARATTERISTICHE: {{features}}
PREZZO: {{price}}
CATEGORIA: {{category}}

VINCOLI:
- Evidenzia caratteristiche tecniche specifiche
- Usa linguaggio persuasivo ma realistico
- Includi benefici per l'utente
- Mantieni un tono positivo ma credibile
- Evita superlativi eccessivi
- Concentrati sui vantaggi pratici

ESEMPI DI STRUTTURA:
Titolo: [Nome prodotto] - [Caratteristica principale]
Paragrafo 1: Introduzione e caratteristiche principali
Paragrafo 2: Benefici specifici per l'utente
Paragrafo 3: Conclusione e call-to-action

REQUISITI TECNICI:
- Lunghezza totale: 150-300 parole
- Densità keyword: 2-3% per termini rilevanti
- Leggibilità: livello medio-alto
- Sentiment: positivo ma equilibrato
PROMPT;
    }

    protected function getRequiredVariables(): array
    {
        return ['product_name', 'features', 'price'];
    }

    protected function getOptionalVariables(): array
    {
        return ['category', 'brand', 'warranty', 'availability'];
    }

    public function getExamples(): array
    {
        return [
            [
                'input' => [
                    'product_name' => 'iPhone 15 Pro',
                    'features' => 'A17 Pro chip, 48MP camera, Titanio, 6.1"',
                    'price' => '€1199',
                    'category' => 'Smartphone'
                ],
                'output' => 'iPhone 15 Pro - Potenza e Prestazioni\n\nL\'iPhone 15 Pro rappresenta l\'eccellenza tecnologica con il chip A17 Pro che offre prestazioni senza precedenti. La fotocamera da 48MP cattura dettagli straordinari, mentre il design in titanio garantisce resistenza e leggerezza.\n\nIdeale per professionisti e creativi, questo smartphone offre autonomia prolungata e connettività 5G avanzata. La schermata Super Retina XDR da 6.1" garantisce un\'esperienza visiva immersiva.\n\nScegli l\'iPhone 15 Pro per prestazioni professionali e design iconico. Disponibile ora a €1199.'
            ],
            [
                'input' => [
                    'product_name' => 'MacBook Air M2',
                    'features' => 'Chip M2, 13" Liquid Retina, 8GB RAM, 256GB SSD',
                    'price' => '€1299',
                    'category' => 'Laptop'
                ],
                'output' => 'MacBook Air M2 - Portabilità e Potenza\n\nIl MacBook Air M2 unisce portabilità estrema a prestazioni eccezionali. Il chip M2 offre velocità di elaborazione superiori con consumi energetici ridotti, garantendo fino a 18 ore di autonomia.\n\nLa schermata Liquid Retina da 13" offre colori vividi e dettagli nitidi, perfetta per lavoro e intrattenimento. Con 8GB di RAM e 256GB SSD, gestisce multitasking complessi con fluidità.\n\nPerfetto per studenti e professionisti in movimento. Disponibile a €1299.'
            ]
        ];
    }

    public function getOptimizationTips(): array
    {
        return [
            'Includi sempre il prezzo per creare urgenza',
            'Evidenzia caratteristiche tecniche specifiche e misurabili',
            'Usa benefici tangibili invece di aggettivi generici',
            'Mantieni un equilibrio tra tecnico e accessibile',
            'Includi sempre una call-to-action finale',
            'Testa con prodotti di categorie diverse per validare l\'efficacia'
        ];
    }

    public function postprocessOutput(string $output): string
    {
        $output = parent::postprocessOutput($output);
        
        // Assicurati che ci sia un titolo
        if (!preg_match('/^[A-Z][^\\n]+$/', $output)) {
            $lines = explode("\n", $output);
            if (count($lines) > 1) {
                $firstLine = trim($lines[0]);
                if (strlen($firstLine) > 10 && strlen($firstLine) < 70) {
                    $output = $firstLine . "\n\n" . implode("\n", array_slice($lines, 1));
                }
            }
        }
        
        // Assicurati che ci siano almeno 3 paragrafi
        $paragraphs = preg_split('/\n\s*\n/', $output);
        if (count($paragraphs) < 3) {
            // Aggiungi paragrafi se mancanti
            $output = $this->ensureMinimumParagraphs($output);
        }
        
        return $output;
    }

    private function ensureMinimumParagraphs(string $output): string
    {
        $paragraphs = preg_split('/\n\s*\n/', $output);
        
        if (count($paragraphs) >= 3) {
            return $output;
        }
        
        // Aggiungi paragrafi generici se necessario
        $additionalParagraphs = [
            "Scopri la qualità e l'affidabilità che solo i prodotti di fascia alta possono offrire.",
            "Scegli l'eccellenza tecnologica per le tue esigenze professionali e personali."
        ];
        
        $needed = 3 - count($paragraphs);
        for ($i = 0; $i < $needed && $i < count($additionalParagraphs); $i++) {
            $paragraphs[] = $additionalParagraphs[$i];
        }
        
        return implode("\n\n", $paragraphs);
    }

    public function validateVariables(array $variables): array
    {
        $errors = parent::validateVariables($variables);
        
        // Validazioni specifiche per descrizioni prodotto
        if (isset($variables['price']) && !preg_match('/€?\d+([.,]\d{2})?/', $variables['price'])) {
            $errors[] = "Il prezzo deve essere in formato valido (es. €1199 o 1199.99)";
        }
        
        if (isset($variables['features']) && strlen($variables['features']) < 10) {
            $errors[] = "Le caratteristiche devono essere descritte in modo dettagliato (min 10 caratteri)";
        }
        
        if (isset($variables['product_name']) && strlen($variables['product_name']) < 3) {
            $errors[] = "Il nome del prodotto deve essere specificato (min 3 caratteri)";
        }
        
        return $errors;
    }
}
