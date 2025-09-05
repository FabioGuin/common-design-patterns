<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo gli utenti autenticati possono creare post
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:5'
            ],
            'content' => [
                'required',
                'string',
                'min:50',
                'max:10000'
            ],
            'category' => [
                'required',
                'string',
                Rule::in(['technology', 'business', 'lifestyle', 'travel', 'food', 'sports'])
            ],
            'tags' => [
                'nullable',
                'array',
                'max:5'
            ],
            'tags.*' => [
                'string',
                'max:50',
                'distinct'
            ],
            'is_published' => [
                'boolean'
            ],
            'featured_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048' // 2MB
            ],
            'meta_description' => [
                'nullable',
                'string',
                'max:160'
            ],
            'allow_comments' => [
                'boolean'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Il titolo è obbligatorio.',
            'title.min' => 'Il titolo deve essere di almeno 5 caratteri.',
            'title.max' => 'Il titolo non può superare i 255 caratteri.',
            
            'content.required' => 'Il contenuto è obbligatorio.',
            'content.min' => 'Il contenuto deve essere di almeno 50 caratteri.',
            'content.max' => 'Il contenuto non può superare i 10000 caratteri.',
            
            'category.required' => 'La categoria è obbligatoria.',
            'category.in' => 'La categoria selezionata non è valida.',
            
            'tags.array' => 'I tag devono essere un array.',
            'tags.max' => 'Puoi inserire al massimo 5 tag.',
            'tags.*.string' => 'Ogni tag deve essere una stringa.',
            'tags.*.max' => 'Ogni tag non può superare i 50 caratteri.',
            'tags.*.distinct' => 'I tag devono essere unici.',
            
            'is_published.boolean' => 'Lo stato di pubblicazione deve essere vero o falso.',
            
            'featured_image.image' => 'Il file deve essere un\'immagine.',
            'featured_image.mimes' => 'L\'immagine deve essere in formato JPEG, PNG, JPG o GIF.',
            'featured_image.max' => 'L\'immagine non può superare i 2MB.',
            
            'meta_description.max' => 'La meta descrizione non può superare i 160 caratteri.',
            
            'allow_comments.boolean' => 'Il permesso per i commenti deve essere vero o falso.'
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'titolo',
            'content' => 'contenuto',
            'category' => 'categoria',
            'tags' => 'tag',
            'is_published' => 'pubblicato',
            'featured_image' => 'immagine in evidenza',
            'meta_description' => 'meta descrizione',
            'allow_comments' => 'permetti commenti'
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalizza il titolo
        if ($this->has('title')) {
            $this->merge([
                'title' => trim($this->title)
            ]);
        }

        // Normalizza i tag
        if ($this->has('tags')) {
            $tags = array_map('trim', $this->tags);
            $tags = array_filter($tags); // Rimuove tag vuoti
            $this->merge([
                'tags' => array_values($tags) // Re-indexa l'array
            ]);
        }

        // Imposta valori di default
        $this->merge([
            'is_published' => $this->boolean('is_published', false),
            'allow_comments' => $this->boolean('allow_comments', true)
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Controlla che il contenuto non contenga parole inappropriate
            $inappropriateWords = ['spam', 'scam', 'fake'];
            $content = strtolower($this->input('content', ''));
            
            foreach ($inappropriateWords as $word) {
                if (strpos($content, $word) !== false) {
                    $validator->errors()->add('content', 'Il contenuto contiene parole inappropriate.');
                    break;
                }
            }

            // Controlla che il titolo non sia troppo generico
            $genericTitles = ['post', 'articolo', 'nuovo', 'test'];
            $title = strtolower($this->input('title', ''));
            
            if (in_array($title, $genericTitles)) {
                $validator->errors()->add('title', 'Il titolo è troppo generico. Sii più specifico.');
            }

            // Controlla che la meta descrizione sia presente se il post è pubblicato
            if ($this->boolean('is_published') && empty($this->input('meta_description'))) {
                $validator->errors()->add('meta_description', 'La meta descrizione è obbligatoria per i post pubblicati.');
            }
        });
    }
}
