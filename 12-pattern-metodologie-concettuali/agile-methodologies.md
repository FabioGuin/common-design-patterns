# Metodologie Agili

Le metodologie agili sono approcci iterativi e incrementali per lo sviluppo software, particolarmente efficaci per progetti Laravel che richiedono flessibilità e adattamento rapido.

## Scrum

### Ruoli e Responsabilità

#### Product Owner
- Definisce e priorizza il Product Backlog
- Collabora con il team per chiarire i requisiti
- Accetta o rifiuta i deliverable

#### Scrum Master
- Facilita le cerimonie Scrum
- Rimuove impedimenti
- Aiuta il team a seguire le pratiche Scrum

#### Development Team
- Sviluppa il prodotto incremento per incremento
- Stima le user story
- Partecipa attivamente alle cerimonie

### Cerimonie Scrum

#### Sprint Planning
```markdown
## Sprint Planning - Sprint 1
**Durata**: 2 ore per sprint di 2 settimane
**Partecipanti**: Product Owner, Scrum Master, Development Team

### Obiettivi
- Definire obiettivi del sprint
- Selezionare user story dal backlog
- Stimare e pianificare il lavoro

### User Story per Sprint 1
1. **US-001**: Come utente, voglio registrarmi per accedere all'applicazione
   - **Story Points**: 5
   - **Tasks**: 
     - Creare migration users
     - Implementare AuthController
     - Creare form di registrazione
     - Scrivere test unitari e feature

2. **US-002**: Come utente, voglio fare login per accedere al mio account
   - **Story Points**: 3
   - **Tasks**:
     - Implementare login endpoint
     - Gestire sessioni utente
     - Creare middleware di autenticazione
```

#### Daily Standup
```markdown
## Daily Standup - 15 Gennaio 2024
**Partecipanti**: Team di sviluppo

### Cosa ho fatto ieri
- **Mario**: Completato form di registrazione, iniziato test
- **Giulia**: Implementato AuthController, creato migration
- **Luca**: Configurato middleware di autenticazione

### Cosa farò oggi
- **Mario**: Completare test di registrazione, iniziare login
- **Giulia**: Implementare validazione form, gestire errori
- **Luca**: Testare middleware, documentare API

### Impedimenti
- **Mario**: Nessuno
- **Giulia**: Aspetto feedback su design form
- **Luca**: Server di test non disponibile
```

#### Sprint Review
```markdown
## Sprint Review - Sprint 1
**Durata**: 1 ora
**Partecipanti**: Product Owner, Stakeholder, Development Team

### Demo
- Registrazione utente funzionante
- Login con validazione
- Interfaccia responsive
- Test automatizzati

### Metriche
- **Velocity**: 8 story points
- **Burndown**: Target raggiunto
- **Quality**: 95% test coverage

### Feedback
- **PO**: Soddisfatto, richiede miglioramenti UX
- **Stakeholder**: Buona base, pronto per prossimo sprint
```

#### Retrospective
```markdown
## Sprint Retrospective - Sprint 1
**Durata**: 1 ora
**Partecipanti**: Development Team

### Cosa è andato bene
- Comunicazione efficace
- Test coverage alta
- Code review sistematiche

### Cosa migliorare
- Stime più accurate
- Documentazione più dettagliata
- Ridurre interruzioni durante lo sviluppo

### Action Items
- Implementare timeboxing per le task
- Creare template per documentazione
- Stabilire orari per code review
```

### Artifacts Scrum

#### Product Backlog
```markdown
## Product Backlog

### Epic: User Management
- **US-001**: Registrazione utente (5 pts)
- **US-002**: Login utente (3 pts)
- **US-003**: Profilo utente (8 pts)
- **US-004**: Reset password (5 pts)

### Epic: Content Management
- **US-005**: Creare articoli (8 pts)
- **US-006**: Modificare articoli (5 pts)
- **US-007**: Eliminare articoli (3 pts)
- **US-008**: Cercare articoli (5 pts)

### Epic: Admin Panel
- **US-009**: Dashboard admin (8 pts)
- **US-010**: Gestione utenti (13 pts)
- **US-011**: Statistiche (8 pts)
```

#### Sprint Backlog
```markdown
## Sprint Backlog - Sprint 1
**Obiettivo**: Implementare autenticazione utente base

### User Stories
1. **US-001**: Registrazione utente (5 pts)
   - [ ] Creare migration users
   - [ ] Implementare AuthController::register
   - [ ] Creare form registrazione
   - [ ] Scrivere test unitari
   - [ ] Scrivere test feature

2. **US-002**: Login utente (3 pts)
   - [ ] Implementare AuthController::login
   - [ ] Creare form login
   - [ ] Gestire sessioni
   - [ ] Test di integrazione

### Burndown Chart
- **Giorno 1**: 8 pts rimanenti
- **Giorno 2**: 6 pts rimanenti
- **Giorno 3**: 4 pts rimanenti
- **Giorno 4**: 2 pts rimanenti
- **Giorno 5**: 0 pts rimanenti
```

## Kanban

### Board Kanban
```markdown
## Kanban Board - Progetto Laravel

### To Do
- [ ] Implementare API per articoli
- [ ] Creare dashboard admin
- [ ] Aggiungere ricerca avanzata
- [ ] Implementare notifiche push

### In Progress
- [ ] Refactoring UserService (Mario)
- [ ] Ottimizzazione query database (Giulia)

### In Review
- [ ] Implementazione cache Redis (Luca)
- [ ] Test di performance (Mario)

### Done
- [x] Sistema di autenticazione
- [x] CRUD articoli
- [x] Sistema di permessi
- [x] API documentation
```

### Work in Progress (WIP) Limits
```markdown
## WIP Limits
- **To Do**: Nessun limite
- **In Progress**: Massimo 3 task per sviluppatore
- **In Review**: Massimo 2 task
- **Done**: Nessun limite

### Regole
- Non iniziare nuovo task se WIP limit raggiunto
- Priorità: Review > In Progress > To Do
- Bloccare task se impedimenti > 1 giorno
```

## Extreme Programming (XP)

### Pratiche XP

#### Pair Programming
```php
// Esempio di pair programming per implementare UserService
class UserService
{
    // Driver: Mario, Navigator: Giulia
    public function createUser(array $data): User
    {
        // Driver scrive, Navigator guida
        $this->validateUserData($data);
        
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
        
        // Navigator suggerisce: "Dovremmo inviare email di benvenuto"
        $this->sendWelcomeEmail($user);
        
        return $user;
    }
}
```

#### Test-Driven Development
```php
// Test scritto prima del codice
public function test_can_create_user_with_valid_data()
{
    $userService = new UserService();
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123'
    ];
    
    $user = $userService->createUser($userData);
    
    $this->assertInstanceOf(User::class, $user);
    $this->assertEquals('John Doe', $user->name);
}

// Implementazione dopo il test
public function createUser(array $data): User
{
    return User::create($data);
}
```

#### Refactoring Continuo
```php
// Codice iniziale
public function getUserPosts($userId)
{
    $user = User::find($userId);
    $posts = $user->posts()->where('status', 'published')->get();
    
    $result = [];
    foreach ($posts as $post) {
        $result[] = [
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'author' => $post->user->name,
            'created_at' => $post->created_at->format('Y-m-d H:i:s')
        ];
    }
    
    return $result;
}

// Dopo refactoring
public function getUserPosts($userId)
{
    return Post::with('user')
        ->where('user_id', $userId)
        ->where('status', 'published')
        ->get()
        ->map(fn($post) => new PostResource($post));
}
```

#### Integration Continua
```yaml
# .github/workflows/ci.yml
name: Continuous Integration

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        
    - name: Install dependencies
      run: composer install -n --prefer-dist
      
    - name: Run tests
      run: php artisan test --coverage
      
    - name: Run static analysis
      run: ./vendor/bin/phpstan analyse
```

## Lean Development

### Principi Lean

#### Eliminare gli Sprechi
```markdown
## Identificazione Sprechi

### Sprechi Identificati
1. **Over-engineering**: Implementazione di funzionalità non necessarie
2. **Duplicazione**: Codice duplicato in più parti
3. **Attese**: Tempo perso in attesa di feedback o approvazioni
4. **Defetti**: Bug che richiedono correzioni multiple

### Azioni Correttive
1. **YAGNI**: Implementare solo ciò che serve
2. **DRY**: Eliminare duplicazione con refactoring
3. **Automazione**: Automatizzare processi manuali
4. **Testing**: Prevenire bug con test automatizzati
```

#### Valore per il Cliente
```markdown
## Analisi del Valore

### Funzionalità ad Alto Valore
- Autenticazione utente (critica)
- CRUD articoli (core business)
- Ricerca (usata frequentemente)

### Funzionalità a Basso Valore
- Tema personalizzabile (nice-to-have)
- Statistiche avanzate (future)
- Integrazione social (opzionale)

### Priorità
1. Implementare funzionalità ad alto valore
2. Valutare funzionalità a basso valore
3. Eliminare funzionalità senza valore
```

#### Flusso Continuo
```markdown
## Ottimizzazione del Flusso

### Flusso Attuale
1. Sviluppo feature (2 giorni)
2. Code review (0.5 giorni)
3. Testing (0.5 giorni)
4. Deploy (0.5 giorni)
5. Feedback (1 giorno)

### Flusso Ottimizzato
1. Sviluppo + Test (2 giorni)
2. Code review + Deploy (0.5 giorni)
3. Feedback immediato (0.5 giorni)

### Miglioramenti
- Test automatizzati durante sviluppo
- Deploy automatico dopo review
- Feedback in tempo reale
```

## Crystal

### Metodologie Crystal

#### Crystal Clear (Team 2-8 persone)
```markdown
## Crystal Clear per Team Piccoli

### Pratiche Essenziali
- **Delivery frequente**: Deploy ogni settimana
- **Reflection workshop**: Retrospective mensili
- **Personal safety**: Ambiente sicuro per feedback
- **Focus**: Concentrazione su obiettivi chiari

### Adattamento per Laravel
- Deploy automatico su staging
- Code review obbligatorie
- Pair programming per task complesse
- Documentazione minima ma efficace
```

#### Crystal Yellow (Team 9-20 persone)
```markdown
## Crystal Yellow per Team Medi

### Pratiche Aggiuntive
- **Co-location**: Team fisicamente vicini
- **Frequent integration**: Integrazione giornaliera
- **Expert user**: Utente esperto nel team
- **Automated testing**: Test automatizzati estesi

### Struttura Team
- **Architect**: Responsabile architettura
- **Lead Developer**: Coordinamento tecnico
- **UX Designer**: Esperienza utente
- **DevOps**: Infrastruttura e deploy
```

## Applicazione Pratica in Laravel

### Sprint Planning per Laravel
```markdown
## Sprint Planning - Laravel Project

### User Story Template
**Come** [ruolo utente]
**Voglio** [funzionalità]
**Affinché** [beneficio]

### Esempio
**Come** amministratore
**Voglio** gestire gli utenti del sistema
**Affinché** possa controllare l'accesso all'applicazione

### Tasks Tecniche
- [ ] Creare UserController
- [ ] Implementare UserService
- [ ] Creare UserResource
- [ ] Scrivere test unitari
- [ ] Scrivere test feature
- [ ] Documentare API
```

### Daily Standup per Laravel
```markdown
## Daily Standup - Laravel Team

### Template
1. **Ieri**: Cosa ho completato
2. **Oggi**: Cosa farò
3. **Impedimenti**: Cosa mi blocca

### Esempi
**Mario**:
- Ieri: Completato UserController, iniziato test
- Oggi: Finire test, iniziare UserService
- Impedimenti: Nessuno

**Giulia**:
- Ieri: Implementato middleware auth, creato form
- Oggi: Testare middleware, migliorare UX
- Impedimenti: Aspetto feedback su design
```

### Retrospective per Laravel
```markdown
## Retrospective - Laravel Project

### Cosa è andato bene
- **Code review**: Feedback costruttivi
- **Testing**: Coverage alta (95%)
- **Documentazione**: API ben documentate
- **Performance**: Query ottimizzate

### Cosa migliorare
- **Stime**: Più accurate per task complesse
- **Comunicazione**: Più frequente con PO
- **Refactoring**: Più sistematico
- **Deploy**: Meno manuale, più automatico

### Action Items
- [ ] Implementare stime a 3 punti
- [ ] Daily sync con PO
- [ ] Refactoring settimanale
- [ ] Automatizzare deploy
```

## Metriche e KPI

### Velocity
```markdown
## Velocity Tracking

### Sprint 1: 8 story points
### Sprint 2: 12 story points  
### Sprint 3: 10 story points
### Sprint 4: 14 story points

### Media: 11 story points per sprint
### Trend: Crescita costante
```

### Burndown Chart
```markdown
## Burndown Chart - Sprint 4

### Giorno 1: 14 pts (target: 14)
### Giorno 2: 12 pts (target: 12)
### Giorno 3: 8 pts (target: 10)
### Giorno 4: 4 pts (target: 8)
### Giorno 5: 0 pts (target: 6)

### Risultato: Sprint completato in anticipo
```

### Quality Metrics
```markdown
## Quality Metrics

### Test Coverage
- Unit Tests: 95%
- Feature Tests: 85%
- Integration Tests: 80%

### Code Quality
- PHPStan Level: 8
- CodeSniffer: Pass
- Security Scan: Pass

### Performance
- Page Load: < 200ms
- API Response: < 100ms
- Database Queries: < 50ms
```

---

*Le metodologie agili sono particolarmente efficaci per progetti Laravel, permettendo di adattarsi rapidamente ai cambiamenti e mantenere alta la qualità del codice attraverso pratiche come TDD, code review e integrazione continua.*
