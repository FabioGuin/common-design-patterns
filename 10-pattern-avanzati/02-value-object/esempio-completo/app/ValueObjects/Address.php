<?php

namespace App\ValueObjects;

/**
 * Value Object per rappresentare indirizzi
 * 
 * Immutabile, validato e type-safe per operazioni geografiche.
 */
class Address extends ValueObject
{
    private readonly string $street;
    private readonly string $city;
    private readonly string $postalCode;
    private readonly string $country;
    private readonly ?string $state;

    private const SUPPORTED_COUNTRIES = ['IT', 'US', 'GB', 'FR', 'DE', 'ES'];
    private const COUNTRY_NAMES = [
        'IT' => 'Italy',
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'FR' => 'France',
        'DE' => 'Germany',
        'ES' => 'Spain'
    ];

    private function __construct(
        string $street,
        string $city,
        string $postalCode,
        string $country,
        ?string $state = null
    ) {
        $this->validateInput($street, $city, $postalCode, $country, $state);

        $this->street = trim($street);
        $this->city = trim($city);
        $this->postalCode = trim($postalCode);
        $this->country = strtoupper(trim($country));
        $this->state = $state ? trim($state) : null;
    }

    /**
     * Crea un nuovo Address
     */
    public static function create(
        string $street,
        string $city,
        string $postalCode,
        string $country,
        ?string $state = null
    ): self {
        return new self($street, $city, $postalCode, $country, $state);
    }

    /**
     * Restituisce la via
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * Restituisce la città
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Restituisce il codice postale
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * Restituisce il paese
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Restituisce lo stato/provincia
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * Restituisce il nome completo del paese
     */
    public function getCountryName(): string
    {
        return self::COUNTRY_NAMES[$this->country] ?? $this->country;
    }

    /**
     * Restituisce l'indirizzo formattato su una riga
     */
    public function getFormatted(): string
    {
        $address = $this->street . ', ' . $this->city . ', ' . $this->postalCode;
        
        if ($this->state) {
            $address .= ', ' . $this->state;
        }
        
        $address .= ', ' . $this->getCountryName();
        
        return $address;
    }

    /**
     * Restituisce l'indirizzo formattato su più righe
     */
    public function getFormattedMultiline(): string
    {
        $lines = [
            $this->street,
            $this->city . ', ' . $this->postalCode
        ];
        
        if ($this->state) {
            $lines[] = $this->state;
        }
        
        $lines[] = $this->getCountryName();
        
        return implode("\n", $lines);
    }

    /**
     * Verifica se l'indirizzo è valido per la spedizione
     */
    public function isValidForShipping(): bool
    {
        return !empty($this->street) && 
               !empty($this->city) && 
               !empty($this->postalCode) && 
               in_array($this->country, self::SUPPORTED_COUNTRIES);
    }

    /**
     * Verifica se due indirizzi sono nello stesso paese
     */
    public function isSameCountry(Address $other): bool
    {
        return $this->country === $other->country;
    }

    /**
     * Verifica se due indirizzi sono nella stessa città
     */
    public function isSameCity(Address $other): bool
    {
        return $this->isSameCountry($other) && 
               strtolower($this->city) === strtolower($other->city);
    }

    /**
     * Restituisce il valore primitivo per il confronto
     */
    public function getValue(): mixed
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
            'postalCode' => $this->postalCode,
            'country' => $this->country,
            'state' => $this->state
        ];
    }

    /**
     * Restituisce una rappresentazione stringa
     */
    public function __toString(): string
    {
        return $this->getFormatted();
    }

    /**
     * Serializza per JSON
     */
    public function jsonSerialize(): mixed
    {
        return [
            'street' => $this->street,
            'city' => $this->city,
            'postalCode' => $this->postalCode,
            'country' => $this->country,
            'countryName' => $this->getCountryName(),
            'state' => $this->state,
            'formatted' => $this->getFormatted(),
            'formattedMultiline' => $this->getFormattedMultiline(),
            'validForShipping' => $this->isValidForShipping()
        ];
    }

    /**
     * Valida l'input
     */
    private function validateInput(
        string $street,
        string $city,
        string $postalCode,
        string $country,
        ?string $state
    ): void {
        if (empty(trim($street))) {
            throw new \InvalidArgumentException('Street cannot be empty');
        }

        if (empty(trim($city))) {
            throw new \InvalidArgumentException('City cannot be empty');
        }

        if (empty(trim($postalCode))) {
            throw new \InvalidArgumentException('Postal code cannot be empty');
        }

        if (empty(trim($country))) {
            throw new \InvalidArgumentException('Country cannot be empty');
        }

        if (!in_array(strtoupper(trim($country)), self::SUPPORTED_COUNTRIES)) {
            throw new \InvalidArgumentException("Unsupported country: {$country}");
        }

        if (strlen($street) > 255) {
            throw new \InvalidArgumentException('Street is too long');
        }

        if (strlen($city) > 100) {
            throw new \InvalidArgumentException('City is too long');
        }

        if (strlen($postalCode) > 20) {
            throw new \InvalidArgumentException('Postal code is too long');
        }
    }
}
