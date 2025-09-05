<?php

namespace App\Services\Prompt;

use App\Models\PromptTemplate;

class PromptTemplateService
{
    public function generatePrompt(string $type, array $variables = []): array
    {
        $templates = [
            'chat' => 'Sei un assistente AI utile. Rispondi in modo chiaro e conciso. Domanda: {question}',
            'code' => 'Genera codice {language} per: {description}. Assicurati che sia ben commentato e seguire le best practices.',
            'translation' => 'Traduci il seguente testo da {from_language} a {to_language}: {text}',
            'summary' => 'Riassumi il seguente testo in {max_words} parole: {text}',
            'analysis' => 'Analizza il seguente testo e fornisci: {analysis_type}. Testo: {text}'
        ];

        $template = $templates[$type] ?? $templates['chat'];
        
        // Sostituisce le variabili nel template
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }

        return [
            'success' => true,
            'prompt' => $template,
            'type' => $type,
            'variables_used' => array_keys($variables),
            'word_count' => str_word_count($template),
            'character_count' => strlen($template)
        ];
    }

    public function validatePrompt(string $prompt): array
    {
        $errors = [];
        
        if (empty(trim($prompt))) {
            $errors[] = 'Il prompt non può essere vuoto';
        }
        
        if (strlen($prompt) < 10) {
            $errors[] = 'Il prompt deve essere di almeno 10 caratteri';
        }
        
        if (strlen($prompt) > 4000) {
            $errors[] = 'Il prompt non può superare i 4000 caratteri';
        }
        
        if (str_word_count($prompt) < 3) {
            $errors[] = 'Il prompt deve contenere almeno 3 parole';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'word_count' => str_word_count($prompt),
            'character_count' => strlen($prompt)
        ];
    }

    public function optimizePrompt(string $prompt): array
    {
        $optimized = $prompt;
        
        // Rimuove spazi extra
        $optimized = preg_replace('/\s+/', ' ', $optimized);
        $optimized = trim($optimized);
        
        // Aggiunge istruzioni di qualità se mancanti
        if (!str_contains(strtolower($optimized), 'rispondi') && !str_contains(strtolower($optimized), 'genera')) {
            $optimized = 'Rispondi in modo chiaro e dettagliato. ' . $optimized;
        }
        
        return [
            'original' => $prompt,
            'optimized' => $optimized,
            'improvements' => [
                'rimossi_spazi_extra' => $prompt !== $optimized,
                'aggiunte_istruzioni' => !str_contains(strtolower($prompt), 'rispondi') && str_contains(strtolower($optimized), 'rispondi')
            ],
            'word_count_original' => str_word_count($prompt),
            'word_count_optimized' => str_word_count($optimized)
        ];
    }

    public function getAvailableTypes(): array
    {
        return [
            'chat' => 'Chat e conversazione',
            'code' => 'Generazione codice',
            'translation' => 'Traduzione',
            'summary' => 'Riassunto',
            'analysis' => 'Analisi'
        ];
    }
}
