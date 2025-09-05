<?php

namespace App\DTOs\Base;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class BaseDTO
{
    /**
     * Valida i dati del DTO
     */
    protected function validate(): void
    {
        $validator = Validator::make($this->toArray(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Regole di validazione per il DTO
     */
    abstract protected function rules(): array;

    /**
     * Messaggi di validazione personalizzati
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Converte il DTO in array
     */
    abstract public function toArray(): array;

    /**
     * Converte il DTO in JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Crea un'istanza del DTO da array
     */
    public static function fromArray(array $data): static
    {
        $reflection = new \ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();
        
        if (!$constructor) {
            throw new \Exception('DTO must have a constructor');
        }

        $parameters = $constructor->getParameters();
        $args = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            $value = $data[$name] ?? null;

            if ($value === null && !$parameter->isOptional()) {
                throw new \Exception("Required parameter '{$name}' is missing");
            }

            $args[] = $value;
        }

        return new static(...$args);
    }

    /**
     * Crea un'istanza del DTO da request
     */
    public static function fromRequest($request): static
    {
        return static::fromArray($request->all());
    }

    /**
     * Verifica se il DTO è valido
     */
    public function isValid(): bool
    {
        try {
            $this->validate();
            return true;
        } catch (ValidationException $e) {
            return false;
        }
    }

    /**
     * Ottiene gli errori di validazione
     */
    public function getValidationErrors(): array
    {
        try {
            $this->validate();
            return [];
        } catch (ValidationException $e) {
            return $e->errors();
        }
    }

    /**
     * Ottiene un valore specifico del DTO
     */
    public function get(string $key, $default = null)
    {
        $data = $this->toArray();
        return $data[$key] ?? $default;
    }

    /**
     * Verifica se il DTO ha un valore specifico
     */
    public function has(string $key): bool
    {
        $data = $this->toArray();
        return array_key_exists($key, $data);
    }

    /**
     * Ottiene tutti i valori del DTO
     */
    public function all(): array
    {
        return $this->toArray();
    }

    /**
     * Filtra i valori del DTO
     */
    public function only(array $keys): array
    {
        $data = $this->toArray();
        return array_intersect_key($data, array_flip($keys));
    }

    /**
     * Esclude valori specifici dal DTO
     */
    public function except(array $keys): array
    {
        $data = $this->toArray();
        return array_diff_key($data, array_flip($keys));
    }

    /**
     * Merge con altri dati
     */
    public function merge(array $data): array
    {
        return array_merge($this->toArray(), $data);
    }

    /**
     * Converte il DTO in stringa
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Serializza il DTO
     */
    public function __serialize(): array
    {
        return $this->toArray();
    }

    /**
     * Deserializza il DTO
     */
    public function __unserialize(array $data): void
    {
        $instance = static::fromArray($data);
        
        // Copia le proprietà
        $reflection = new \ReflectionClass($this);
        foreach ($reflection->getProperties() as $property) {
            if ($property->isPublic()) {
                $property->setValue($this, $property->getValue($instance));
            }
        }
    }
}
