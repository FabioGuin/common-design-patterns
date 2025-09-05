# Blade Templates Pattern

## Panoramica

Il **Blade Templates Pattern** è un pattern architetturale che implementa un sistema di templating per la separazione della logica di presentazione dal codice PHP. Blade è il motore di templating di Laravel che fornisce una sintassi elegante e potente per creare viste dinamiche e riutilizzabili.

## Problema Risolto

### Problemi Comuni
- **Mixing di codice**: Logica PHP mescolata con HTML
- **Duplicazione**: Codice HTML ripetuto in più viste
- **Manutenibilità**: Difficoltà nel modificare layout comuni
- **Sicurezza**: Escape automatico dei dati per prevenire XSS

### Esempi di Problemi
```pseudocodice
// Problema: Codice mescolato con HTML
function renderUserList(users) {
    html = '<div class="user-list">'
    foreach (users as user) {
        html += '<div class="user">'
        html += '<h3>' + user.name + '</h3>'
        html += '<p>' + user.email + '</p>'
        html += '</div>'
    }
    html += '</div>'
    return html
}
```

## Soluzione

### Architettura del Pattern

```pseudocodice
// Struttura base Blade Template
@extends('layouts.app')

@section('title', 'Page Title')

@section('content')
    <div class="container">
        @foreach($users as $user)
            <div class="user">
                <h3>{{ $user->name }}</h3>
                <p>{{ $user->email }}</p>
            </div>
        @endforeach
    </div>
@endsection
```

### Componenti Principali

1. **Layout Master**
   - Template base per tutte le pagine
   - Sezioni comuni (header, footer, sidebar)
   - Gestione delle dipendenze CSS/JS

2. **Template Inheritance**
   - Estensione di layout esistenti
   - Override di sezioni specifiche
   - Composizione gerarchica

3. **Components e Slots**
   - Componenti riutilizzabili
   - Slots per contenuto dinamico
   - Props per parametri

4. **Directives Personalizzate**
   - Estensione della sintassi Blade
   - Logica di business specifica
   - Helper per presentazione

## Vantaggi

### 1. **Separazione delle Responsabilità**
- Logica separata dalla presentazione
- Template riutilizzabili
- Codice più pulito

### 2. **Manutenibilità**
- Modifiche centralizzate
- Layout consistenti
- Debugging semplificato

### 3. **Sicurezza**
- Escape automatico
- Protezione XSS
- Validazione input

### 4. **Performance**
- Compilazione ottimizzata
- Cache dei template
- Minificazione automatica

## Svantaggi

### 1. **Complessità**
- Curva di apprendimento
- Debugging dei template
- Gestione delle dipendenze

### 2. **Performance**
- Overhead di compilazione
- Cache management
- Memory usage

## Caso d'Uso

### Scenario: Sistema di Blog
```pseudocodice
// Layout master
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Blog')</title>
    @stack('styles')
</head>
<body>
    @include('partials.header')
    
    <main>
        @yield('content')
    </main>
    
    @include('partials.footer')
    @stack('scripts')
</body>
</html>

// Template specifico
@extends('layouts.app')

@section('title', 'Lista Post')

@section('content')
    <div class="posts">
        @foreach($posts as $post)
            @include('partials.post-card', ['post' => $post])
        @endforeach
    </div>
@endsection
```

## Implementazione Laravel

### 1. **Layout Master**

```pseudocodice
// resources/views/layouts/app.blade.php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app().getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    
    @stack('styles')
</head>
<body>
    @include('partials.header')
    
    <main class="container">
        @yield('content')
    </main>
    
    @include('partials.footer')
    
    @stack('scripts')
</body>
</html>
```

### 2. **Template Inheritance**

```pseudocodice
// resources/views/blog/index.blade.php
@extends('layouts.app')

@section('title', 'Blog Posts')

@section('content')
    <div class="row">
        <div class="col-md-8">
            @foreach($posts as $post)
                <article class="post">
                    <h2>{{ $post->title }}</h2>
                    <p>{{ $post->excerpt }}</p>
                    <a href="{{ route('blog.show', $post) }}">Read More</a>
                </article>
            @endforeach
        </div>
        
        <div class="col-md-4">
            @include('partials.sidebar')
        </div>
    </div>
@endsection
```

### 3. **Components**

```pseudocodice
// resources/views/components/post-card.blade.php
<div class="post-card">
    <h3>{{ $post->title }}</h3>
    <p>{{ $post->excerpt }}</p>
    <div class="meta">
        <span>By {{ $post->user->name }}</span>
        <span>{{ $post->created_at->format('M d, Y') }}</span>
    </div>
</div>

// Utilizzo
<x-post-card :post="$post" />
```

### 4. **Directives Personalizzate**

```pseudocodice
// AppServiceProvider
Blade.directive('datetime', function (expression) {
    return "<?php echo (expression).format('M d, Y H:i'); ?>"
})

// Utilizzo
@datetime($post->created_at)
```

## Esempi Pratici

### 1. **Layout Responsive**

```pseudocodice
// resources/views/layouts/app.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'App')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">App</a>
            <div class="navbar-nav ms-auto">
                @auth
                    <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    <a class="nav-link" href="{{ route('logout') }}">Logout</a>
                @else
                    <a class="nav-link" href="{{ route('login') }}">Login</a>
                    <a class="nav-link" href="{{ route('register') }}">Register</a>
                @endauth
            </div>
        </div>
    </nav>
    
    <main class="container my-4">
        @yield('content')
    </main>
    
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p>&copy; {{ date('Y') }} App. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
```

### 2. **Form Components**

```pseudocodice
// resources/views/components/form-input.blade.php
<div class="form-group mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input 
        type="{{ $type ?? 'text' }}" 
        name="{{ $name }}" 
        id="{{ $name }}" 
        class="form-control @error($name) is-invalid @enderror"
        value="{{ old($name, $value ?? '') }}"
        {{ $required ? 'required' : '' }}
    >
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

// Utilizzo
<x-form-input name="title" label="Post Title" :required="true" />
<x-form-input name="email" label="Email" type="email" />
```

### 3. **Card Components**

```pseudocodice
// resources/views/components/card.blade.php
<div class="card {{ $class ?? '' }}">
    @if(isset($header))
        <div class="card-header">
            {{ $header }}
        </div>
    @endif
    
    <div class="card-body">
        {{ $slot }}
    </div>
    
    @if(isset($footer))
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>

// Utilizzo
<x-card>
    <x-slot name="header">
        <h5>Post Details</h5>
    </x-slot>
    
    <p>Post content here...</p>
    
    <x-slot name="footer">
        <button class="btn btn-primary">Save</button>
    </x-slot>
</x-card>
```

### 4. **Modal Components**

```pseudocodice
// resources/views/components/modal.blade.php
<div class="modal fade" id="{{ $id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            <div class="modal-footer">
                {{ $footer ?? '' }}
            </div>
        </div>
    </div>
</div>

// Utilizzo
<x-modal id="deleteModal" title="Confirm Delete">
    <p>Are you sure you want to delete this item?</p>
    
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger">Delete</button>
    </x-slot>
</x-modal>
```

## Directives Avanzate

### 1. **Directives Personalizzate**

```pseudocodice
// AppServiceProvider
Blade.directive('role', function (expression) {
    return "<?php if(auth().check() && auth().user().hasRole(expression)): ?>"
})

Blade.directive('endrole', function () {
    return "<?php endif; ?>"
})

// Utilizzo
@role('admin')
    <p>Admin content</p>
@endrole
```

### 2. **If Directives**

```pseudocodice
// AppServiceProvider
Blade.if('admin', function () {
    return auth().check() && auth().user().isAdmin()
})

// Utilizzo
@admin
    <p>Admin panel</p>
@endadmin
```

### 3. **Include When**

```pseudocodice
@includeWhen($user->isAdmin(), 'partials.admin-panel')
@includeUnless($user->isGuest(), 'partials.user-menu')
```

## Best Practices

### 1. **Organizzazione**
- Layout master per struttura comune
- Partial per componenti riutilizzabili
- Components per elementi complessi
- Naming convention consistente

### 2. **Performance**
- Usa @once per script unici
- Minifica CSS e JS
- Lazy loading per immagini
- Cache dei template

### 3. **Sicurezza**
- Escape automatico con {{ }}
- Raw output solo quando necessario
- Validazione input
- CSRF protection

### 4. **Manutenibilità**
- Documenta i componenti
- Usa props tipizzate
- Testa i template
- Versioning delle modifiche

## Pattern Correlati

- **Template Method**: Struttura base per template
- **Composite**: Composizione di componenti
- **Decorator**: Aggiunta di funzionalità
- **Observer**: Aggiornamenti dinamici

## Conclusione

Il Blade Templates Pattern è essenziale in Laravel per creare interfacce utente dinamiche e manutenibili. Fornisce un sistema potente per la separazione della logica di presentazione, migliorando la produttività e la qualità del codice.

La chiave per un uso efficace è organizzare i template in modo logico, creare componenti riutilizzabili e seguire le best practices di Laravel per mantenere il codice pulito e performante.
