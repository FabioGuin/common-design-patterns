<?php

namespace App\Services\Prompt\Templates;

abstract class BaseTemplate
{
    protected string $name;
    protected string $description;
    protected array $variables = [];
    protected array $validationRules = [];
    protected float $costEstimate = 0.0;
    protected float $expectedDuration = 0.0;

    abstract public function getTemplate(): string;

    public function getName(): string
    {
        return $this->name ?? class_basename($this);
    }

    public function getDescription(): string
    {
        return $this->description ?? '';
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    public function getCostEstimate(): float
    {
        return $this->costEstimate;
    }

    public function getExpectedDuration(): float
    {
        return $this->expectedDuration;
    }

    /**
     * Valida le variabili fornite
     */
    public function validateVariables(array $variables): array
    {
        $errors = [];
        $requiredVariables = $this->getRequiredVariables();

        // Controlla variabili richieste
        foreach ($requiredVariables as $variable) {
            if (!isset($variables[$variable]) || empty($variables[$variable])) {
                $errors[] = "Variabile richiesta mancante: {$variable}";
            }
        }

        // Controlla variabili non riconosciute
        $allowedVariables = array_merge($this->variables, $this->getOptionalVariables());
        foreach ($variables as $key => $value) {
            if (!in_array($key, $allowedVariables)) {
                $errors[] = "Variabile non riconosciuta: {$key}";
            }
        }

        // Valida lunghezza variabili
        foreach ($variables as $key => $value) {
            if (is_string($value) && strlen($value) > 1000) {
                $errors[] = "Variabile '{$key}' troppo lunga (max 1000 caratteri)";
            }
        }

        return $errors;
    }

    /**
     * Ottiene le variabili richieste
     */
    protected function getRequiredVariables(): array
    {
        return $this->variables;
    }

    /**
     * Ottiene le variabili opzionali
     */
    protected function getOptionalVariables(): array
    {
        return [];
    }

    /**
     * Preprocessa le variabili prima della sostituzione
     */
    public function preprocessVariables(array $variables): array
    {
        $processed = [];

        foreach ($variables as $key => $value) {
            $processed[$key] = $this->preprocessVariable($key, $value);
        }

        return $processed;
    }

    /**
     * Preprocessa una singola variabile
     */
    protected function preprocessVariable(string $key, $value): string
    {
        if (!is_string($value)) {
            return (string) $value;
        }

        // Rimuovi caratteri speciali pericolosi
        $value = strip_tags($value);
        
        // Limita lunghezza
        $value = substr($value, 0, 1000);
        
        // Normalizza spazi
        $value = preg_replace('/\s+/', ' ', trim($value));

        return $value;
    }

    /**
     * Postprocessa l'output generato
     */
    public function postprocessOutput(string $output): string
    {
        // Rimuovi spazi extra
        $output = preg_replace('/\s+/', ' ', trim($output));
        
        // Rimuovi caratteri di controllo
        $output = preg_replace('/[\x00-\x1F\x7F]/', '', $output);
        
        // Normalizza linee
        $output = preg_replace('/\n\s*\n/', "\n\n", $output);

        return $output;
    }

    /**
     * Ottiene esempi per il template
     */
    public function getExamples(): array
    {
        return [];
    }

    /**
     * Ottiene suggerimenti per l'ottimizzazione
     */
    public function getOptimizationTips(): array
    {
        return [
            'Usa variabili specifiche e descrittive',
            'Includi esempi nel template quando possibile',
            'Definisci vincoli chiari per l\'output',
            'Testa con dati reali per validare l\'efficacia'
        ];
    }

    /**
     * Calcola la complessità del template
     */
    public function getComplexityScore(): float
    {
        $template = $this->getTemplate();
        $score = 0;

        // Lunghezza del template
        $score += strlen($template) / 1000;

        // Numero di variabili
        $score += count($this->variables) * 0.1;

        // Numero di istruzioni
        $instructionCount = substr_count($template, '-') + substr_count($template, '•');
        $score += $instructionCount * 0.05;

        // Numero di vincoli
        $constraintCount = substr_count($template, 'VINCOLI') + substr_count($template, 'REQUIREMENTS');
        $score += $constraintCount * 0.2;

        return min(1.0, $score);
    }

    /**
     * Verifica se il template è ottimizzato
     */
    public function isOptimized(): bool
    {
        $complexity = $this->getComplexityScore();
        $variableCount = count($this->variables);
        
        // Template ottimizzato se ha complessità moderata e variabili appropriate
        return $complexity < 0.7 && $variableCount <= 10;
    }

    /**
     * Ottiene metriche del template
     */
    public function getMetrics(): array
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'variable_count' => count($this->variables),
            'complexity_score' => $this->getComplexityScore(),
            'is_optimized' => $this->isOptimized(),
            'cost_estimate' => $this->getCostEstimate(),
            'expected_duration' => $this->getExpectedDuration(),
            'template_length' => strlen($this->getTemplate())
        ];
    }
}
