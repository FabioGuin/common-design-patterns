<?php

namespace App\ValueObjects;

use InvalidArgumentException;

class Address
{
    private readonly string $street;
    private readonly string $city;
    private readonly string $postalCode;
    private readonly string $country;

    public function __construct(
        string $street,
        string $city,
        string $postalCode,
        string $country
    ) {
        $this->validate($street, $city, $postalCode, $country);
        $this->street = trim($street);
        $this->city = trim($city);
        $this->postalCode = trim($postalCode);
        $this->country = strtoupper(trim($country));
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getFullAddress(): string
    {
        return "{$this->street}, {$this->postalCode} {$this->city}, {$this->country}";
    }

    public function equals(Address $other): bool
    {
        return $this->street === $other->street &&
               $this->city === $other->city &&
               $this->postalCode === $other->postalCode &&
               $this->country === $other->country;
    }

    public function isInCountry(string $country): bool
    {
        return $this->country === strtoupper($country);
    }

    public function __toString(): string
    {
        return $this->getFullAddress();
    }

    private function validate(string $street, string $city, string $postalCode, string $country): void
    {
        if (empty($street)) {
            throw new InvalidArgumentException('La via non può essere vuota');
        }

        if (empty($city)) {
            throw new InvalidArgumentException('La città non può essere vuota');
        }

        if (empty($postalCode)) {
            throw new InvalidArgumentException('Il codice postale non può essere vuoto');
        }

        if (empty($country)) {
            throw new InvalidArgumentException('Il paese non può essere vuoto');
        }

        if (strlen($street) > 255) {
            throw new InvalidArgumentException('La via è troppo lunga');
        }

        if (strlen($city) > 100) {
            throw new InvalidArgumentException('La città è troppo lunga');
        }

        if (strlen($postalCode) > 20) {
            throw new InvalidArgumentException('Il codice postale è troppo lungo');
        }

        if (strlen($country) !== 2) {
            throw new InvalidArgumentException('Il paese deve essere un codice a 2 lettere (es: IT, US)');
        }
    }
}
