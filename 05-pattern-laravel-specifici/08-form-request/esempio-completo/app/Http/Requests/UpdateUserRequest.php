<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    protected User $userToUpdate;

    public function authorize(): bool
    {
        $this->userToUpdate = $this->route('user');
        
        // L'utente può aggiornare il proprio profilo o gli admin possono aggiornare chiunque
        return $this->user() && (
            $this->user()->id === $this->userToUpdate->id || 
            $this->user()->role === 'admin'
        );
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:2'
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                'unique:users,email,' . $this->userToUpdate->id,
                'max:255'
            ],
            'password' => [
                'sometimes',
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ],
            'role' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['user', 'admin', 'moderator'])
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^\+?[1-9]\d{1,14}$/'
            ],
            'date_of_birth' => [
                'nullable',
                'date',
                'before:today',
                'after:1900-01-01'
            ],
            'current_password' => [
                'required_with:password',
                'string',
                function ($attribute, $value, $fail) {
                    if (!password_verify($value, $this->userToUpdate->password)) {
                        $fail('La password attuale non è corretta.');
                    }
                }
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
            
            'current_password.required_with' => 'La password attuale è obbligatoria per cambiare la password.',
            'current_password.string' => 'La password attuale deve essere una stringa.'
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
            'current_password' => 'password attuale'
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Solo gli admin possono cambiare il ruolo
            if ($this->has('role') && $this->user()->role !== 'admin') {
                $validator->errors()->add('role', 'Non hai i permessi per modificare il ruolo.');
            }

            // Controlla che l'utente non stia cercando di rimuovere il proprio ruolo di admin
            if ($this->has('role') && 
                $this->userToUpdate->role === 'admin' && 
                $this->input('role') !== 'admin' && 
                $this->user()->id === $this->userToUpdate->id) {
                $validator->errors()->add('role', 'Non puoi rimuovere il tuo ruolo di admin.');
            }
        });
    }
}
