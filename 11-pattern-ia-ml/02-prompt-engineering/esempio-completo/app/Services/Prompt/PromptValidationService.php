<?php

namespace App\Services\Prompt;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PromptValidationService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('prompt.validation', []);
    }

    /**
     * Valida l'output generato dall'AI
     */
    public function validateOutput(string $output, string $templateName, array $variables = []): array
    {
        if (!$this->config['enabled']) {
            return [
                'passed' => true,
                'quality_score' => 1.0,
                'rules_checked' => [],
                'warnings' => []
            ];
        }

        $rules = $this->config['rules'];
        $templateRules = $this->getTemplateValidationRules($templateName);
        
        $validationResult = [
            'passed' => true,
            'quality_score' => 0.0,
            'rules_checked' => [],
            'warnings' => [],
            'errors' => []
        ];

        // Validazione lunghezza
        $lengthResult = $this->validateLength($output, $rules['length'], $templateRules);
        $validationResult['rules_checked']['length'] = $lengthResult;
        if (!$lengthResult['passed']) {
            $validationResult['passed'] = false;
            $validationResult['errors'][] = $lengthResult['message'];
        }

        // Validazione keywords
        $keywordsResult = $this->validateKeywords($output, $rules['keywords'], $templateRules);
        $validationResult['rules_checked']['keywords'] = $keywordsResult;
        if (!$keywordsResult['passed']) {
            $validationResult['warnings'][] = $keywordsResult['message'];
        }

        // Validazione sentiment
        $sentimentResult = $this->validateSentiment($output, $rules['sentiment']);
        $validationResult['rules_checked']['sentiment'] = $sentimentResult;
        if (!$sentimentResult['passed']) {
            $validationResult['warnings'][] = $sentimentResult['message'];
        }

        // Validazione leggibilità
        $readabilityResult = $this->validateReadability($output, $rules['readability']);
        $validationResult['rules_checked']['readability'] = $readabilityResult;
        if (!$readabilityResult['passed']) {
            $validationResult['warnings'][] = $readabilityResult['message'];
        }

        // Validazione struttura
        $structureResult = $this->validateStructure($output, $rules['structure'], $templateRules);
        $validationResult['rules_checked']['structure'] = $structureResult;
        if (!$structureResult['passed']) {
            $validationResult['warnings'][] = $structureResult['message'];
        }

        // Validazione AI (se abilitata)
        if ($this->config['ai_validation']['enabled']) {
            $aiResult = $this->validateWithAI($output, $templateName, $variables);
            $validationResult['rules_checked']['ai_validation'] = $aiResult;
            if (!$aiResult['passed']) {
                $validationResult['warnings'][] = $aiResult['message'];
            }
        }

        // Calcola quality score
        $validationResult['quality_score'] = $this->calculateQualityScore($validationResult['rules_checked']);

        Log::info('Prompt Output Validation Completed', [
            'template' => $templateName,
            'passed' => $validationResult['passed'],
            'quality_score' => $validationResult['quality_score'],
            'rules_checked' => count($validationResult['rules_checked'])
        ]);

        return $validationResult;
    }

    /**
     * Valida la lunghezza dell'output
     */
    private function validateLength(string $output, array $rules, array $templateRules): array
    {
        $length = strlen($output);
        $minLength = $templateRules['min_length'] ?? $rules['min'];
        $maxLength = $templateRules['max_length'] ?? $rules['max'];

        $passed = $length >= $minLength && $length <= $maxLength;

        return [
            'passed' => $passed,
            'actual_length' => $length,
            'min_length' => $minLength,
            'max_length' => $maxLength,
            'message' => $passed ? 'Lunghezza appropriata' : "Lunghezza non valida: {$length} caratteri (richiesti: {$minLength}-{$maxLength})"
        ];
    }

    /**
     * Valida le keywords richieste e vietate
     */
    private function validateKeywords(string $output, array $rules, array $templateRules): array
    {
        $output = strtolower($output);
        $errors = [];

        // Controlla keywords richieste
        $requiredKeywords = $templateRules['required_keywords'] ?? $rules['required'] ?? [];
        foreach ($requiredKeywords as $keyword) {
            if (!str_contains($output, strtolower($keyword))) {
                $errors[] = "Keyword richiesta mancante: {$keyword}";
            }
        }

        // Controlla keywords vietate
        $forbiddenKeywords = $templateRules['forbidden_words'] ?? $rules['forbidden'] ?? [];
        foreach ($forbiddenKeywords as $keyword) {
            if (str_contains($output, strtolower($keyword))) {
                $errors[] = "Keyword vietata trovata: {$keyword}";
            }
        }

        return [
            'passed' => empty($errors),
            'required_keywords' => $requiredKeywords,
            'forbidden_keywords' => $forbiddenKeywords,
            'message' => empty($errors) ? 'Keywords appropriate' : implode('; ', $errors)
        ];
    }

    /**
     * Valida il sentiment dell'output
     */
    private function validateSentiment(string $output, array $rules): array
    {
        $sentimentScore = $this->calculateSentimentScore($output);
        $minScore = $rules['min_score'];
        $maxScore = $rules['max_score'];

        $passed = $sentimentScore >= $minScore && $sentimentScore <= $maxScore;

        return [
            'passed' => $passed,
            'sentiment_score' => $sentimentScore,
            'min_score' => $minScore,
            'max_score' => $maxScore,
            'message' => $passed ? 'Sentiment appropriato' : "Sentiment non valido: {$sentimentScore} (richiesto: {$minScore}-{$maxScore})"
        ];
    }

    /**
     * Valida la leggibilità dell'output
     */
    private function validateReadability(string $output, array $rules): array
    {
        $readabilityGrade = $this->calculateReadabilityGrade($output);
        $maxGrade = $rules['max_grade'];
        $minGrade = $rules['min_grade'] ?? 0;

        $passed = $readabilityGrade >= $minGrade && $readabilityGrade <= $maxGrade;

        return [
            'passed' => $passed,
            'readability_grade' => $readabilityGrade,
            'min_grade' => $minGrade,
            'max_grade' => $maxGrade,
            'message' => $passed ? 'Leggibilità appropriata' : "Leggibilità non valida: grado {$readabilityGrade} (richiesto: {$minGrade}-{$maxGrade})"
        ];
    }

    /**
     * Valida la struttura dell'output
     */
    private function validateStructure(string $output, array $rules, array $templateRules): array
    {
        $errors = [];

        // Controlla paragrafi
        if ($rules['require_paragraphs']) {
            $paragraphs = preg_split('/\n\s*\n/', $output);
            $paragraphCount = count(array_filter($paragraphs, fn($p) => trim($p) !== ''));
            
            $minParagraphs = $templateRules['min_paragraphs'] ?? $rules['min_paragraphs'];
            $maxParagraphs = $templateRules['max_paragraphs'] ?? $rules['max_paragraphs'];

            if ($paragraphCount < $minParagraphs) {
                $errors[] = "Paragrafi insufficienti: {$paragraphCount} (richiesti: {$minParagraphs})";
            }
            
            if ($paragraphCount > $maxParagraphs) {
                $errors[] = "Troppi paragrafi: {$paragraphCount} (massimo: {$maxParagraphs})";
            }
        }

        // Controlla sezioni richieste
        $requiredSections = $templateRules['required_sections'] ?? [];
        foreach ($requiredSections as $section) {
            if (!str_contains(strtolower($output), strtolower($section))) {
                $errors[] = "Sezione richiesta mancante: {$section}";
            }
        }

        return [
            'passed' => empty($errors),
            'paragraph_count' => $paragraphCount ?? 0,
            'required_sections' => $requiredSections,
            'message' => empty($errors) ? 'Struttura appropriata' : implode('; ', $errors)
        ];
    }

    /**
     * Valida usando AI
     */
    private function validateWithAI(string $output, string $templateName, array $variables): array
    {
        try {
            $prompt = $this->buildAIValidationPrompt($output, $templateName, $variables);
            
            $response = Http::timeout($this->config['ai_validation']['timeout'])
                ->post($this->getAIValidationEndpoint(), [
                    'prompt' => $prompt,
                    'model' => $this->config['ai_validation']['model']
                ]);

            if (!$response->successful()) {
                return [
                    'passed' => false,
                    'message' => 'Errore nella validazione AI: ' . $response->body()
                ];
            }

            $data = $response->json();
            $aiResult = json_decode($data['text'] ?? '{}', true);

            return [
                'passed' => $aiResult['valid'] ?? false,
                'ai_score' => $aiResult['score'] ?? 0,
                'ai_feedback' => $aiResult['feedback'] ?? '',
                'message' => $aiResult['valid'] ? 'Validazione AI superata' : 'Validazione AI fallita: ' . ($aiResult['feedback'] ?? 'Nessun feedback')
            ];

        } catch (\Exception $e) {
            Log::error('AI Validation Failed', [
                'template' => $templateName,
                'error' => $e->getMessage()
            ]);

            return [
                'passed' => false,
                'message' => 'Errore nella validazione AI: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calcola il sentiment score
     */
    private function calculateSentimentScore(string $text): float
    {
        // Implementazione semplificata - in produzione useresti librerie specializzate
        $positiveWords = ['ottimo', 'eccellente', 'fantastico', 'perfetto', 'superiore', 'migliore', 'eccellente'];
        $negativeWords = ['terribile', 'orribile', 'pessimo', 'scadente', 'inutile', 'brutto', 'cattivo'];

        $text = strtolower($text);
        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveWords as $word) {
            $positiveCount += substr_count($text, $word);
        }

        foreach ($negativeWords as $word) {
            $negativeCount += substr_count($text, $word);
        }

        $totalWords = str_word_count($text);
        if ($totalWords === 0) {
            return 0.5; // Neutro se nessuna parola
        }

        $positiveRatio = $positiveCount / $totalWords;
        $negativeRatio = $negativeCount / $totalWords;

        return 0.5 + ($positiveRatio - $negativeRatio) * 2; // Range 0-1
    }

    /**
     * Calcola il grado di leggibilità
     */
    private function calculateReadabilityGrade(string $text): float
    {
        // Implementazione semplificata del Flesch Reading Ease
        $sentences = preg_split('/[.!?]+/', $text);
        $words = str_word_count($text);
        $syllables = $this->countSyllables($text);

        $sentenceCount = count(array_filter($sentences, fn($s) => trim($s) !== ''));
        
        if ($sentenceCount === 0 || $words === 0) {
            return 0;
        }

        $avgWordsPerSentence = $words / $sentenceCount;
        $avgSyllablesPerWord = $syllables / $words;

        // Formula semplificata
        $score = 206.835 - (1.015 * $avgWordsPerSentence) - (84.6 * $avgSyllablesPerWord);
        
        // Converti in grade level (0-20)
        return max(0, min(20, (100 - $score) / 5));
    }

    /**
     * Conta le sillabe in un testo
     */
    private function countSyllables(string $text): int
    {
        $words = str_word_count($text, 1);
        $syllables = 0;

        foreach ($words as $word) {
            $syllables += $this->countWordSyllables($word);
        }

        return $syllables;
    }

    /**
     * Conta le sillabe in una singola parola
     */
    private function countWordSyllables(string $word): int
    {
        $word = strtolower($word);
        $vowels = 'aeiouy';
        $syllables = 0;
        $previousWasVowel = false;

        for ($i = 0; $i < strlen($word); $i++) {
            $isVowel = strpos($vowels, $word[$i]) !== false;
            
            if ($isVowel && !$previousWasVowel) {
                $syllables++;
            }
            
            $previousWasVowel = $isVowel;
        }

        // Aggiusta per parole che finiscono con 'e'
        if (substr($word, -1) === 'e' && $syllables > 1) {
            $syllables--;
        }

        return max(1, $syllables);
    }

    /**
     * Calcola il quality score complessivo
     */
    private function calculateQualityScore(array $rulesChecked): float
    {
        $weights = [
            'length' => 0.2,
            'keywords' => 0.2,
            'sentiment' => 0.2,
            'readability' => 0.2,
            'structure' => 0.1,
            'ai_validation' => 0.1
        ];

        $totalScore = 0;
        $totalWeight = 0;

        foreach ($rulesChecked as $rule => $result) {
            $weight = $weights[$rule] ?? 0.1;
            $score = $result['passed'] ? 1.0 : 0.0;
            
            $totalScore += $score * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? $totalScore / $totalWeight : 0.5;
    }

    /**
     * Ottiene le regole di validazione per un template specifico
     */
    private function getTemplateValidationRules(string $templateName): array
    {
        $templates = config('prompt.templates', []);
        return $templates[$templateName]['validation_rules'] ?? [];
    }

    /**
     * Costruisce il prompt per la validazione AI
     */
    private function buildAIValidationPrompt(string $output, string $templateName, array $variables): string
    {
        return "Valuta la qualità del seguente output generato per il template '{$templateName}':\n\n" .
               "OUTPUT:\n{$output}\n\n" .
               "VARIABILI UTILIZZATE:\n" . json_encode($variables, JSON_PRETTY_PRINT) . "\n\n" .
               "Rispondi con un JSON contenente:\n" .
               "- 'valid': true/false\n" .
               "- 'score': 0-1\n" .
               "- 'feedback': commento dettagliato";
    }

    /**
     * Ottiene l'endpoint per la validazione AI
     */
    private function getAIValidationEndpoint(): string
    {
        $provider = $this->config['ai_validation']['provider'];
        
        $endpoints = [
            'openai' => 'https://api.openai.com/v1/chat/completions',
            'claude' => 'https://api.anthropic.com/v1/messages',
            'gemini' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent'
        ];

        return $endpoints[$provider] ?? $endpoints['openai'];
    }
}
