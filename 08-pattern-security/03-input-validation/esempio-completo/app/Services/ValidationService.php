<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ValidationService
{
    private array $validationStats = [
        'total_validations' => 0,
        'successful_validations' => 0,
        'failed_validations' => 0,
        'sanitizations' => 0
    ];

    /**
     * Validate data with rules
     */
    public function validate(array $data, array $rules, array $messages = [], array $attributes = []): array
    {
        $this->validationStats['total_validations']++;
        
        $validator = Validator::make($data, $rules, $messages, $attributes);
        
        if ($validator->fails()) {
            $this->validationStats['failed_validations']++;
            
            Log::warning('Validation failed', [
                'data' => $data,
                'rules' => $rules,
                'errors' => $validator->errors()->toArray()
            ]);
            
            throw new ValidationException($validator);
        }
        
        $this->validationStats['successful_validations']++;
        
        Log::info('Validation successful', [
            'data' => $data,
            'rules' => $rules
        ]);
        
        return $validator->validated();
    }

    /**
     * Sanitize input data
     */
    public function sanitize(array $data): array
    {
        $this->validationStats['sanitizations']++;
        
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            $sanitized[$key] = $this->sanitizeValue($value);
        }
        
        Log::info('Input sanitized', [
            'original' => $data,
            'sanitized' => $sanitized
        ]);
        
        return $sanitized;
    }

    /**
     * Sanitize individual value
     */
    private function sanitizeValue($value)
    {
        if (is_string($value)) {
            // Remove HTML tags
            $value = strip_tags($value);
            
            // Trim whitespace
            $value = trim($value);
            
            // Remove null bytes
            $value = str_replace("\0", '', $value);
            
            // Escape special characters
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        } elseif (is_array($value)) {
            // Recursively sanitize arrays
            $value = array_map([$this, 'sanitizeValue'], $value);
        }
        
        return $value;
    }

    /**
     * Test validation rules
     */
    public function testRules(array $data, array $rules): array
    {
        $results = [];
        
        foreach ($rules as $field => $rule) {
            $fieldRules = is_array($rule) ? $rule : explode('|', $rule);
            $fieldResults = [];
            
            foreach ($fieldRules as $singleRule) {
                $ruleParts = explode(':', $singleRule);
                $ruleName = $ruleParts[0];
                $ruleParams = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];
                
                $fieldResults[] = [
                    'rule' => $singleRule,
                    'rule_name' => $ruleName,
                    'parameters' => $ruleParams,
                    'applies' => $this->ruleApplies($ruleName, $data[$field] ?? null, $ruleParams)
                ];
            }
            
            $results[$field] = $fieldResults;
        }
        
        return $results;
    }

    /**
     * Check if rule applies to value
     */
    private function ruleApplies(string $ruleName, $value, array $parameters = []): bool
    {
        switch ($ruleName) {
            case 'required':
                return !empty($value);
            case 'string':
                return is_string($value);
            case 'integer':
                return is_int($value) || ctype_digit($value);
            case 'numeric':
                return is_numeric($value);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
            case 'min':
                $min = (int) $parameters[0];
                if (is_string($value)) {
                    return strlen($value) >= $min;
                } elseif (is_numeric($value)) {
                    return $value >= $min;
                }
                return false;
            case 'max':
                $max = (int) $parameters[0];
                if (is_string($value)) {
                    return strlen($value) <= $max;
                } elseif (is_numeric($value)) {
                    return $value <= $max;
                }
                return false;
            case 'in':
                return in_array($value, $parameters);
            case 'not_in':
                return !in_array($value, $parameters);
            case 'regex':
                return preg_match($parameters[0], $value);
            case 'unique':
                // Simplified unique check
                return true;
            case 'exists':
                // Simplified exists check
                return true;
            case 'confirmed':
                return true; // Simplified
            case 'accepted':
                return in_array($value, [1, '1', true, 'true', 'on', 'yes']);
            case 'boolean':
                return in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false']);
            case 'date':
                return strtotime($value) !== false;
            case 'before':
                $beforeDate = $parameters[0];
                return strtotime($value) < strtotime($beforeDate);
            case 'after':
                $afterDate = $parameters[0];
                return strtotime($value) > strtotime($afterDate);
            case 'array':
                return is_array($value);
            case 'image':
                return true; // Simplified
            case 'mimes':
                return true; // Simplified
            default:
                return true; // Unknown rules pass by default
        }
    }

    /**
     * Validate file upload
     */
    public function validateFile($file, array $rules = []): array
    {
        $defaultRules = [
            'max' => 2048, // 2MB
            'mimes' => ['jpeg', 'jpg', 'png', 'gif'],
            'image' => true
        ];
        
        $rules = array_merge($defaultRules, $rules);
        
        $validator = Validator::make(['file' => $file], [
            'file' => 'required|file|max:' . $rules['max'] . '|mimes:' . implode(',', $rules['mimes'])
        ]);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $validator->validated();
    }

    /**
     * Validate JSON input
     */
    public function validateJson(string $json, array $rules): array
    {
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }
        
        return $this->validate($data, $rules);
    }

    /**
     * Validate API request
     */
    public function validateApiRequest(array $data, array $rules, string $endpoint = null): array
    {
        $apiRules = $this->addApiRules($rules, $endpoint);
        
        return $this->validate($data, $apiRules);
    }

    /**
     * Add API-specific rules
     */
    private function addApiRules(array $rules, ?string $endpoint): array
    {
        $apiRules = $rules;
        
        // Add rate limiting rules based on endpoint
        if ($endpoint) {
            $apiRules['_rate_limit'] = 'required|integer|min:1|max:1000';
        }
        
        return $apiRules;
    }

    /**
     * Get validation statistics
     */
    public function getValidationStats(): array
    {
        return array_merge($this->validationStats, [
            'success_rate' => $this->validationStats['total_validations'] > 0 
                ? round(($this->validationStats['successful_validations'] / $this->validationStats['total_validations']) * 100, 2)
                : 0,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ]);
    }

    /**
     * Reset validation statistics
     */
    public function resetStats(): void
    {
        $this->validationStats = [
            'total_validations' => 0,
            'successful_validations' => 0,
            'failed_validations' => 0,
            'sanitizations' => 0
        ];
        
        Log::info('Validation statistics reset');
    }

    /**
     * Get common validation rules
     */
    public function getCommonRules(): array
    {
        return [
            'user' => [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
                'age' => 'required|integer|min:18|max:120'
            ],
            'product' => [
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'price' => 'required|numeric|min:0.01',
                'sku' => 'required|string|unique:products,sku',
                'stock' => 'required|integer|min:0'
            ],
            'order' => [
                'customer_id' => 'required|integer|exists:users,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1|max:100'
            ]
        ];
    }
}
