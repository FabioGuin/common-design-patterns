<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo gli admin possono creare utenti
        return $this->user() && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2'
            ],
            'email' => [
                'required',
                'email',
                'unique:users,email',
                'max:255'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/' // Almeno una minuscola, una maiuscola e un numero
            ],
            'role' => [
                'required',
                'string',
                Rule::in(['user', 'admin', 'moderator'])
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^\+?[1-9]\d{1,14}$/' // Formato internazionale
            ],
            'date_of_birth' => [
                'nullable',
                'date',
                'before:today',
                'after:1900-01-01'
            ],
            'terms_accepted' => [
                'required',
                'accepted'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Il nome è obbligatorio.',
            'name.min' => 'Il nome deve essere di almeno 2 caratteri.',
            'name.max' => 'Il nome non può superare i 255 caratteri.',
            
            'email.required' => 'L\'email è obbligatoria.',
            'email.email' => 'Inserisci un indirizzo email valido.',
            'email.unique' => 'Questa email è già registrata.',
            'email.max' => 'L\'email non può superare i 255 caratteri.',
            
            'password.required' => 'La password è obbligatoria.',
            'password.min' => 'La password deve essere di almeno 8 caratteri.',
            'password.confirmed' => 'La conferma della password non corrisponde.',
            'password.regex' => 'La password deve contenere almeno una lettera minuscola, una maiuscola e un numero.',
            
            'role.required' => 'Il ruolo è obbligatorio.',
            'role.in' => 'Il ruolo selezionato non è valido.',
            
            'phone.regex' => 'Inserisci un numero di telefono valido.',
            
            'date_of_birth.date' => 'Inserisci una data valida.',
            'date_of_birth.before' => 'La data di nascita deve essere nel passato.',
            'date_of_birth.after' => 'La data di nascita deve essere dopo il 1900.',
            
            'terms_accepted.required' => 'Devi accettare i termini e condizioni.',
            'terms_accepted.accepted' => 'Devi accettare i termini e condizioni.'
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'email',
            'password' => 'password',
            'role' => 'ruolo',
            'phone' => 'telefono',
            'date_of_birth' => 'data di nascita',
            'terms_accepted' => 'termini e condizioni'
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalizza l'email
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->email))
            ]);
        }

        // Normalizza il nome
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name)
            ]);
        }
    }
}
