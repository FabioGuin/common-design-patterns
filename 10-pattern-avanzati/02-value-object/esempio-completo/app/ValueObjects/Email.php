<?php

namespace App\ValueObjects;

/**
 * Value Object per rappresentare indirizzi email
 * 
 * Immutabile, validato e type-safe per operazioni di comunicazione.
 */
class Email extends ValueObject
{
    private readonly string $value;

    private function __construct(string $email)
    {
        $this->validateInput($email);
        $this->value = strtolower(trim($email));
    }

    /**
     * Crea un nuovo Email
     */
    public static function create(string $email): self
    {
        return new self($email);
    }

    /**
     * Restituisce il valore dell'email
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Restituisce l'email come stringa
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Restituisce la parte locale (prima della @)
     */
    public function getLocalPart(): string
    {
        return explode('@', $this->value)[0];
    }

    /**
     * Restituisce il dominio (dopo la @)
     */
    public function getDomain(): string
    {
        return explode('@', $this->value)[1];
    }

    /**
     * Verifica se l'email è valida
     */
    public function isValid(): bool
    {
        return filter_var($this->value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Verifica se l'email appartiene a un dominio specifico
     */
    public function belongsToDomain(string $domain): bool
    {
        return strtolower($this->getDomain()) === strtolower($domain);
    }

    /**
     * Verifica se l'email è un indirizzo aziendale
     */
    public function isCorporate(): bool
    {
        $corporateDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
        return !in_array($this->getDomain(), $corporateDomains);
    }

    /**
     * Maschera l'email per la privacy
     */
    public function getMasked(): string
    {
        $localPart = $this->getLocalPart();
        $domain = $this->getDomain();
        
        if (strlen($localPart) <= 2) {
            $maskedLocal = str_repeat('*', strlen($localPart));
        } else {
            $maskedLocal = $localPart[0] . str_repeat('*', strlen($localPart) - 2) . $localPart[-1];
        }
        
        return $maskedLocal . '@' . $domain;
    }

    /**
     * Restituisce una rappresentazione stringa
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Serializza per JSON
     */
    public function jsonSerialize(): mixed
    {
        return [
            'value' => $this->value,
            'localPart' => $this->getLocalPart(),
            'domain' => $this->getDomain(),
            'isValid' => $this->isValid(),
            'isCorporate' => $this->isCorporate(),
            'masked' => $this->getMasked()
        ];
    }

    /**
     * Valida l'input
     */
    private function validateInput(string $email): void
    {
        $email = trim($email);

        if (empty($email)) {
            throw new \InvalidArgumentException('Email cannot be empty');
        }

        if (strlen($email) > 254) {
            throw new \InvalidArgumentException('Email is too long');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('Email format is invalid');
        }

        // Controlli aggiuntivi
        if (strpos($email, '..') !== false) {
            throw new \InvalidArgumentException('Email cannot contain consecutive dots');
        }

        if (strpos($email, '@') === false) {
            throw new \InvalidArgumentException('Email must contain @ symbol');
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('Email must contain exactly one @ symbol');
        }

        if (empty($parts[0]) || empty($parts[1])) {
            throw new \InvalidArgumentException('Email local part and domain cannot be empty');
        }
    }
}
