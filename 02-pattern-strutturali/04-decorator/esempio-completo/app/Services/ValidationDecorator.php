<?php

namespace App\Services;

class ValidationDecorator implements NotificationInterface
{
    private NotificationInterface $notification;
    private array $rules;

    public function __construct(NotificationInterface $notification, array $rules = [])
    {
        $this->notification = $notification;
        $this->rules = $rules;
    }

    /**
     * Invia una notifica con validazione
     */
    public function send(string $message, array $data = []): array
    {
        $validationResult = $this->validate($message, $data);

        if (!$validationResult['valid']) {
            return [
                'success' => false,
                'type' => $this->notification->getType(),
                'error' => 'Validation failed',
                'validation_errors' => $validationResult['errors'],
                'cost' => $this->getCost(),
                'timestamp' => now()->toISOString(),
            ];
        }

        return $this->notification->send($message, $data);
    }

    /**
     * Ottiene il tipo di notifica
     */
    public function getType(): string
    {
        return $this->notification->getType() . '_with_validation';
    }

    /**
     * Ottiene il costo della notifica
     */
    public function getCost(): float
    {
        return $this->notification->getCost() + 0.03; // Costo aggiuntivo per validazione
    }

    /**
     * Verifica se la notifica è disponibile
     */
    public function isAvailable(): bool
    {
        return $this->notification->isAvailable();
    }

    /**
     * Ottiene la descrizione della notifica
     */
    public function getDescription(): string
    {
        return $this->notification->getDescription() . ' (with validation)';
    }

    /**
     * Valida il messaggio e i dati
     */
    private function validate(string $message, array $data): array
    {
        $errors = [];

        // Valida il messaggio
        if (empty($message)) {
            $errors[] = 'Message cannot be empty';
        }

        if (strlen($message) > 1000) {
            $errors[] = 'Message too long (max 1000 characters)';
        }

        // Valida i dati
        foreach ($this->rules as $field => $rule) {
            if (isset($data[$field])) {
                $value = $data[$field];
                $fieldErrors = $this->validateField($field, $value, $rule);
                $errors = array_merge($errors, $fieldErrors);
            } elseif (strpos($rule, 'required') !== false) {
                $errors[] = "Field '{$field}' is required";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Valida un singolo campo
     */
    private function validateField(string $field, $value, string $rule): array
    {
        $errors = [];
        $rules = explode('|', $rule);

        foreach ($rules as $singleRule) {
            $ruleParts = explode(':', $singleRule);
            $ruleName = $ruleParts[0];
            $ruleValue = $ruleParts[1] ?? null;

            switch ($ruleName) {
                case 'required':
                    if (empty($value)) {
                        $errors[] = "Field '{$field}' is required";
                    }
                    break;

                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Field '{$field}' must be a valid email";
                    }
                    break;

                case 'min':
                    if (is_string($value) && strlen($value) < $ruleValue) {
                        $errors[] = "Field '{$field}' must be at least {$ruleValue} characters";
                    }
                    break;

                case 'max':
                    if (is_string($value) && strlen($value) > $ruleValue) {
                        $errors[] = "Field '{$field}' must be no more than {$ruleValue} characters";
                    }
                    break;

                case 'numeric':
                    if (!is_numeric($value)) {
                        $errors[] = "Field '{$field}' must be numeric";
                    }
                    break;

                case 'in':
                    $allowedValues = explode(',', $ruleValue);
                    if (!in_array($value, $allowedValues)) {
                        $errors[] = "Field '{$field}' must be one of: " . implode(', ', $allowedValues);
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * Aggiunge una regola di validazione
     */
    public function addRule(string $field, string $rule): void
    {
        $this->rules[$field] = $rule;
    }

    /**
     * Rimuove una regola di validazione
     */
    public function removeRule(string $field): void
    {
        unset($this->rules[$field]);
    }

    /**
     * Ottiene tutte le regole di validazione
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
