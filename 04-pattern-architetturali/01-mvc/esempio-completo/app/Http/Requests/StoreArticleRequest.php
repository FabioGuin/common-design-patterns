<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
{
    /**
     * Determina se l'utente è autorizzato a fare questa richiesta
     */
    public function authorize(): bool
    {
        return true; // In un'app reale, implementare logica di autorizzazione
    }

    /**
     * Regole di validazione per la richiesta
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'content' => [
                'required',
                'string',
                'min:50',
            ],
            'excerpt' => [
                'nullable',
                'string',
                'max:500',
            ],
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['draft', 'published']),
            ],
            'published_at' => [
                'nullable',
                'date',
                'after_or_equal:now',
            ],
        ];
    }

    /**
     * Messaggi di errore personalizzati
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Il titolo è obbligatorio.',
            'title.string' => 'Il titolo deve essere una stringa.',
            'title.max' => 'Il titolo non può superare i 255 caratteri.',
            'title.min' => 'Il titolo deve essere di almeno 3 caratteri.',
            
            'content.required' => 'Il contenuto è obbligatorio.',
            'content.string' => 'Il contenuto deve essere una stringa.',
            'content.min' => 'Il contenuto deve essere di almeno 50 caratteri.',
            
            'excerpt.string' => 'L\'excerpt deve essere una stringa.',
            'excerpt.max' => 'L\'excerpt non può superare i 500 caratteri.',
            
            'user_id.required' => 'L\'autore è obbligatorio.',
            'user_id.integer' => 'L\'autore deve essere un numero intero.',
            'user_id.exists' => 'L\'autore selezionato non esiste.',
            
            'status.required' => 'Lo stato è obbligatorio.',
            'status.in' => 'Lo stato deve essere "draft" o "published".',
            
            'published_at.date' => 'La data di pubblicazione deve essere una data valida.',
            'published_at.after_or_equal' => 'La data di pubblicazione deve essere oggi o nel futuro.',
        ];
    }

    /**
     * Attributi personalizzati per i messaggi di errore
     */
    public function attributes(): array
    {
        return [
            'title' => 'titolo',
            'content' => 'contenuto',
            'excerpt' => 'excerpt',
            'user_id' => 'autore',
            'status' => 'stato',
            'published_at' => 'data di pubblicazione',
        ];
    }

    /**
     * Prepara i dati per la validazione
     */
    protected function prepareForValidation(): void
    {
        // Se lo stato è "published" e non è stata fornita una data di pubblicazione,
        // imposta la data di pubblicazione a ora
        if ($this->status === 'published' && !$this->published_at) {
            $this->merge([
                'published_at' => now(),
            ]);
        }

        // Se lo stato è "draft", rimuovi la data di pubblicazione
        if ($this->status === 'draft') {
            $this->merge([
                'published_at' => null,
            ]);
        }
    }

    /**
     * Configura il validator dopo che le regole sono state applicate
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validazione aggiuntiva: se l'articolo è pubblicato, deve avere un contenuto significativo
            if ($this->status === 'published' && strlen(strip_tags($this->content)) < 100) {
                $validator->errors()->add(
                    'content',
                    'Un articolo pubblicato deve avere almeno 100 caratteri di contenuto.'
                );
            }

            // Validazione aggiuntiva: se l'articolo è pubblicato, deve avere un titolo significativo
            if ($this->status === 'published' && strlen($this->title) < 10) {
                $validator->errors()->add(
                    'title',
                    'Un articolo pubblicato deve avere un titolo di almeno 10 caratteri.'
                );
            }
        });
    }

    /**
     * Ottiene i dati validati dalla richiesta
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Pulisci e formatta i dati
        if (isset($validated['title'])) {
            $validated['title'] = trim($validated['title']);
        }

        if (isset($validated['content'])) {
            $validated['content'] = trim($validated['content']);
        }

        if (isset($validated['excerpt'])) {
            $validated['excerpt'] = trim($validated['excerpt']);
        }

        return $validated;
    }
}
