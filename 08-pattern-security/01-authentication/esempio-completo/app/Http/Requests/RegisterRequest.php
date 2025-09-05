<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
            'terms' => 'required|accepted'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Il nome è obbligatorio.',
            'name.max' => 'Il nome non può superare i 255 caratteri.',
            'email.required' => 'L\'indirizzo email è obbligatorio.',
            'email.email' => 'L\'indirizzo email deve essere valido.',
            'email.unique' => 'Questo indirizzo email è già registrato.',
            'password.required' => 'La password è obbligatoria.',
            'password.confirmed' => 'La conferma della password non corrisponde.',
            'password.min' => 'La password deve essere di almeno 8 caratteri.',
            'terms.required' => 'Devi accettare i termini e condizioni.',
            'terms.accepted' => 'Devi accettare i termini e condizioni.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'indirizzo email',
            'password' => 'password',
            'terms' => 'termini e condizioni'
        ];
    }
}
