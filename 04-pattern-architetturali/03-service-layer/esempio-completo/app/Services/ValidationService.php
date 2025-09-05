<?php

namespace App\Services;

use App\Models\User;
use App\Models\Article;
use Illuminate\Support\Facades\Validator;

class ValidationService
{
    /**
     * Valida i dati di un articolo
     */
    public function validateArticleData(array $data, ?int $articleId = null): void
    {
        $rules = [
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string|min:50',
            'excerpt' => 'nullable|string|max:500',
            'user_id' => 'required|integer|exists:users,id',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date|after_or_equal:now',
        ];

        $messages = [
            'title.required' => 'Il titolo è obbligatorio',
            'title.min' => 'Il titolo deve essere di almeno 3 caratteri',
            'title.max' => 'Il titolo non può superare i 255 caratteri',
            'content.required' => 'Il contenuto è obbligatorio',
            'content.min' => 'Il contenuto deve essere di almeno 50 caratteri',
            'excerpt.max' => 'L\'excerpt non può superare i 500 caratteri',
            'user_id.required' => 'L\'autore è obbligatorio',
            'user_id.exists' => 'L\'autore selezionato non esiste',
            'status.required' => 'Lo stato è obbligatorio',
            'status.in' => 'Lo stato deve essere "draft" o "published"',
            'published_at.date' => 'La data di pubblicazione deve essere una data valida',
            'published_at.after_or_equal' => 'La data di pubblicazione deve essere oggi o nel futuro',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        // Validazioni business aggiuntive
        $this->validateArticleBusinessRules($data, $articleId);
    }

    /**
     * Valida le regole di business per un articolo
     */
    private function validateArticleBusinessRules(array $data, ?int $articleId = null): void
    {
        // Validazione per articoli pubblicati
        if (isset($data['status']) && $data['status'] === 'published') {
            if (strlen($data['content'] ?? '') < 100) {
                throw new \Exception('Un articolo pubblicato deve avere almeno 100 caratteri di contenuto');
            }

            if (strlen($data['title'] ?? '') < 10) {
                throw new \Exception('Un articolo pubblicato deve avere un titolo di almeno 10 caratteri');
            }
        }

        // Validazione slug unico
        if (isset($data['title'])) {
            $slug = \Illuminate\Support\Str::slug($data['title']);
            $query = Article::where('slug', $slug);
            
            if ($articleId) {
                $query->where('id', '!=', $articleId);
            }
            
            if ($query->exists()) {
                throw new \Exception('Esiste già un articolo con questo titolo');
            }
        }
    }

    /**
     * Valida i dati di un utente
     */
    public function validateUserData(array $data, ?int $userId = null): void
    {
        $rules = [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|max:255',
            'password' => $userId ? 'nullable|string|min:8' : 'required|string|min:8',
            'bio' => 'nullable|string|max:1000',
            'role' => 'required|in:user,editor,admin',
            'is_active' => 'boolean',
        ];

        $messages = [
            'name.required' => 'Il nome è obbligatorio',
            'name.min' => 'Il nome deve essere di almeno 2 caratteri',
            'name.max' => 'Il nome non può superare i 255 caratteri',
            'email.required' => 'L\'email è obbligatoria',
            'email.email' => 'L\'email non è valida',
            'email.max' => 'L\'email non può superare i 255 caratteri',
            'password.required' => 'La password è obbligatoria',
            'password.min' => 'La password deve essere di almeno 8 caratteri',
            'bio.max' => 'La biografia non può superare i 1000 caratteri',
            'role.required' => 'Il ruolo è obbligatorio',
            'role.in' => 'Il ruolo deve essere "user", "editor" o "admin"',
            'is_active.boolean' => 'Lo stato attivo deve essere vero o falso',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        // Validazioni business aggiuntive
        $this->validateUserBusinessRules($data, $userId);
    }

    /**
     * Valida le regole di business per un utente
     */
    private function validateUserBusinessRules(array $data, ?int $userId = null): void
    {
        // Validazione email unica
        if (isset($data['email'])) {
            $query = User::where('email', $data['email']);
            
            if ($userId) {
                $query->where('id', '!=', $userId);
            }
            
            if ($query->exists()) {
                throw new \Exception('Esiste già un utente con questa email');
            }
        }

        // Validazione password per nuovi utenti
        if (!$userId && empty($data['password'])) {
            throw new \Exception('La password è obbligatoria per i nuovi utenti');
        }
    }

    /**
     * Valida i dati di ricerca
     */
    public function validateSearchData(array $data): void
    {
        $rules = [
            'q' => 'nullable|string|min:2|max:255',
            'status' => 'nullable|in:draft,published',
            'role' => 'nullable|in:user,editor,admin',
            'is_active' => 'nullable|boolean',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];

        $messages = [
            'q.min' => 'Il termine di ricerca deve essere di almeno 2 caratteri',
            'q.max' => 'Il termine di ricerca non può superare i 255 caratteri',
            'status.in' => 'Lo stato deve essere "draft" o "published"',
            'role.in' => 'Il ruolo deve essere "user", "editor" o "admin"',
            'is_active.boolean' => 'Lo stato attivo deve essere vero o falso',
            'page.integer' => 'La pagina deve essere un numero intero',
            'page.min' => 'La pagina deve essere almeno 1',
            'per_page.integer' => 'Il numero di elementi per pagina deve essere un numero intero',
            'per_page.min' => 'Il numero di elementi per pagina deve essere almeno 1',
            'per_page.max' => 'Il numero di elementi per pagina non può superare 100',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    /**
     * Valida i dati di filtraggio
     */
    public function validateFilterData(array $data): void
    {
        $rules = [
            'sort' => 'nullable|string|in:created_at,updated_at,title,name,email',
            'direction' => 'nullable|string|in:asc,desc',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ];

        $messages = [
            'sort.in' => 'Il campo di ordinamento non è valido',
            'direction.in' => 'La direzione deve essere "asc" o "desc"',
            'date_from.date' => 'La data di inizio deve essere una data valida',
            'date_to.date' => 'La data di fine deve essere una data valida',
            'date_to.after_or_equal' => 'La data di fine deve essere dopo o uguale alla data di inizio',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    /**
     * Valida i dati di paginazione
     */
    public function validatePaginationData(array $data): void
    {
        $rules = [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];

        $messages = [
            'page.integer' => 'La pagina deve essere un numero intero',
            'page.min' => 'La pagina deve essere almeno 1',
            'per_page.integer' => 'Il numero di elementi per pagina deve essere un numero intero',
            'per_page.min' => 'Il numero di elementi per pagina deve essere almeno 1',
            'per_page.max' => 'Il numero di elementi per pagina non può superare 100',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    /**
     * Valida i dati di un commento
     */
    public function validateCommentData(array $data): void
    {
        $rules = [
            'content' => 'required|string|min:10|max:1000',
            'article_id' => 'required|integer|exists:articles,id',
            'user_id' => 'required|integer|exists:users,id',
        ];

        $messages = [
            'content.required' => 'Il contenuto del commento è obbligatorio',
            'content.min' => 'Il commento deve essere di almeno 10 caratteri',
            'content.max' => 'Il commento non può superare i 1000 caratteri',
            'article_id.required' => 'L\'articolo è obbligatorio',
            'article_id.exists' => 'L\'articolo selezionato non esiste',
            'user_id.required' => 'L\'utente è obbligatorio',
            'user_id.exists' => 'L\'utente selezionato non esiste',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    /**
     * Valida i dati di configurazione
     */
    public function validateConfigData(array $data): void
    {
        $rules = [
            'site_name' => 'required|string|min:2|max:255',
            'site_description' => 'nullable|string|max:500',
            'admin_email' => 'required|email|max:255',
            'max_articles_per_user' => 'nullable|integer|min:1|max:1000',
            'allow_registration' => 'boolean',
            'require_email_verification' => 'boolean',
        ];

        $messages = [
            'site_name.required' => 'Il nome del sito è obbligatorio',
            'site_name.min' => 'Il nome del sito deve essere di almeno 2 caratteri',
            'site_name.max' => 'Il nome del sito non può superare i 255 caratteri',
            'site_description.max' => 'La descrizione del sito non può superare i 500 caratteri',
            'admin_email.required' => 'L\'email dell\'amministratore è obbligatoria',
            'admin_email.email' => 'L\'email dell\'amministratore non è valida',
            'max_articles_per_user.integer' => 'Il numero massimo di articoli deve essere un numero intero',
            'max_articles_per_user.min' => 'Il numero massimo di articoli deve essere almeno 1',
            'max_articles_per_user.max' => 'Il numero massimo di articoli non può superare 1000',
            'allow_registration.boolean' => 'Il permesso di registrazione deve essere vero o falso',
            'require_email_verification.boolean' => 'La verifica email deve essere vera o falsa',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }
}
