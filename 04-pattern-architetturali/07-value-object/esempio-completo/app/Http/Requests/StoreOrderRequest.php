<?php

namespace App\Http\Requests;

use App\ValueObjects\Address;
use App\ValueObjects\Email;
use App\ValueObjects\Price;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_email' => 'required|string|email|max:254',
            'product_name' => 'required|string|max:255',
            'price_euros' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|size:2',
        ];
    }

    public function getEmail(): Email
    {
        try {
            return new Email($this->input('customer_email'));
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'customer_email' => [$e->getMessage()]
            ]);
        }
    }

    public function getPrice(): Price
    {
        try {
            return Price::fromEuros(
                (float) $this->input('price_euros'),
                $this->input('currency')
            );
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'price_euros' => [$e->getMessage()]
            ]);
        }
    }

    public function getAddress(): Address
    {
        try {
            return new Address(
                $this->input('street'),
                $this->input('city'),
                $this->input('postal_code'),
                $this->input('country')
            );
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'address' => [$e->getMessage()]
            ]);
        }
    }

    public function getProductName(): string
    {
        return $this->input('product_name');
    }
}
