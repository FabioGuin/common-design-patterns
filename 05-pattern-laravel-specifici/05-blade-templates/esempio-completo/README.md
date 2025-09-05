# Blade Templates Pattern - Esempio Completo

## Panoramica

Questo esempio dimostra l'implementazione del **Blade Templates Pattern** in Laravel, mostrando come utilizzare il sistema di templating Blade per creare interfacce utente dinamiche, riutilizzabili e manutenibili.

## Struttura del Progetto

```
esempio-completo/
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php
│       │   ├── admin.blade.php
│       │   └── auth.blade.php
│       ├── components/
│       │   ├── form-input.blade.php
│       │   ├── form-select.blade.php
│       │   ├── form-textarea.blade.php
│       │   ├── card.blade.php
│       │   ├── modal.blade.php
│       │   ├── alert.blade.php
│       │   ├── button.blade.php
│       │   ├── post-card.blade.php
│       │   └── user-avatar.blade.php
│       ├── partials/
│       │   ├── header.blade.php
│       │   ├── footer.blade.php
│       │   ├── sidebar.blade.php
│       │   ├── navigation.blade.php
│       │   └── breadcrumb.blade.php
│       ├── pages/
│       │   ├── home.blade.php
│       │   ├── blog/
│       │   │   ├── index.blade.php
│       │   │   ├── show.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── edit.blade.php
│       │   ├── users/
│       │   │   ├── index.blade.php
│       │   │   ├── show.blade.php
│       │   │   └── profile.blade.php
│       │   └── admin/
│       │       ├── dashboard.blade.php
│       │       └── settings.blade.php
│       └── emails/
│           ├── welcome.blade.php
│           └── notification.blade.php
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── HomeController.php
│   │       ├── BlogController.php
│   │       └── UserController.php
│   └── View/
│       └── Components/
│           ├── FormInput.php
│           ├── Card.php
│           └── Modal.php
├── routes/
│   ├── web.php
│   └── admin.php
├── composer.json
└── env.example
```

## Installazione

1. **Clona il repository**
```bash
git clone <repository-url>
cd esempio-completo
```

2. **Installa le dipendenze**
```bash
composer install
```

3. **Configura l'ambiente**
```bash
cp env.example .env
php artisan key:generate
```

4. **Configura il database**
```bash
php artisan migrate
php artisan db:seed
```

5. **Avvia il server**
```bash
php artisan serve
```

## Componenti Implementati

### 1. **Layout Master (app.blade.php)**
- Struttura HTML base
- Gestione meta tag
- Stack per CSS e JS
- Sezioni per header, content, footer

### 2. **Layout Specializzati**
- **admin.blade.php**: Layout per area amministrativa
- **auth.blade.php**: Layout per pagine di autenticazione

### 3. **Components Riutilizzabili**
- **Form Components**: Input, Select, Textarea
- **UI Components**: Card, Modal, Alert, Button
- **Content Components**: PostCard, UserAvatar

### 4. **Partials**
- **Header**: Navigazione principale
- **Footer**: Informazioni di chiusura
- **Sidebar**: Contenuto laterale
- **Navigation**: Menu di navigazione

## Utilizzo

### Layout Master

```php
// resources/views/layouts/app.blade.php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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

### Template Inheritance

```php
// resources/views/pages/blog/index.blade.php
@extends('layouts.app')

@section('title', 'Blog Posts')

@section('content')
    <div class="row">
        <div class="col-md-8">
            @foreach($posts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
        
        <div class="col-md-4">
            @include('partials.sidebar')
        </div>
    </div>
@endsection
```

### Components

```php
// resources/views/components/post-card.blade.php
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">{{ $post->title }}</h5>
        <p class="card-text">{{ $post->excerpt }}</p>
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                By {{ $post->user->name }} • {{ $post->created_at->format('M d, Y') }}
            </small>
            <a href="{{ route('blog.show', $post) }}" class="btn btn-primary btn-sm">Read More</a>
        </div>
    </div>
</div>

// Utilizzo
<x-post-card :post="$post" />
```

### Form Components

```php
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
        {{ $attributes->merge(['placeholder' => $placeholder ?? '']) }}
    >
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    @if(isset($help))
        <div class="form-text">{{ $help }}</div>
    @endif
</div>

// Utilizzo
<x-form-input name="title" label="Post Title" :required="true" />
<x-form-input name="email" label="Email" type="email" placeholder="Enter your email" />
```

### Card Components

```php
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

### Modal Components

```php
// resources/views/components/modal.blade.php
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog {{ $size ?? '' }}">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @if(isset($footer))
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endif
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

## Directives Personalizzate

### 1. **Role Directives**

```php
// AppServiceProvider
Blade::directive('role', function ($expression) {
    return "<?php if(auth()->check() && auth()->user()->hasRole($expression)): ?>";
});

Blade::directive('endrole', function () {
    return "<?php endif; ?>";
});

// Utilizzo
@role('admin')
    <p>Admin content</p>
@endrole
```

### 2. **If Directives**

```php
// AppServiceProvider
Blade::if('admin', function () {
    return auth()->check() && auth()->user()->isAdmin();
});

Blade::if('guest', function () {
    return !auth()->check();
});

// Utilizzo
@admin
    <p>Admin panel</p>
@endadmin

@guest
    <p>Please login</p>
@endguest
```

### 3. **Custom Directives**

```php
// AppServiceProvider
Blade::directive('datetime', function ($expression) {
    return "<?php echo ($expression)->format('M d, Y H:i'); ?>";
});

Blade::directive('currency', function ($expression) {
    return "<?php echo number_format($expression, 2) . ' €'; ?>";
});

// Utilizzo
@datetime($post->created_at)
@currency($product->price)
```

## Best Practices

### 1. **Organizzazione File**
- Layout master in `layouts/`
- Components in `components/`
- Partials in `partials/`
- Pagine in `pages/`

### 2. **Naming Convention**
- Layout: `app.blade.php`, `admin.blade.php`
- Components: `form-input.blade.php`, `post-card.blade.php`
- Partials: `header.blade.php`, `footer.blade.php`

### 3. **Performance**
- Usa `@once` per script unici
- Minifica CSS e JS
- Lazy loading per immagini
- Cache dei template

### 4. **Sicurezza**
- Escape automatico con `{{ }}`
- Raw output solo con `{!! !!}`
- Validazione input
- CSRF protection

## Test

### Test Components

```php
// tests/Feature/BladeComponentsTest.php
public function test_form_input_component()
{
    $view = $this->blade('<x-form-input name="test" label="Test Label" />');
    
    $view->assertSee('name="test"');
    $view->assertSee('Test Label');
}
```

### Test Layout

```php
public function test_app_layout_renders()
{
    $view = $this->view('layouts.app', ['title' => 'Test Page']);
    
    $view->assertSee('Test Page');
    $view->assertSee('</html>');
}
```

## Debugging

### Debug Template

```php
// Abilita debug mode
APP_DEBUG=true

// Log delle query
DB::enableQueryLog();
```

### Laravel Debugbar

```bash
composer require barryvdh/laravel-debugbar --dev
```

## Conclusione

Questo esempio dimostra come utilizzare efficacemente il Blade Templates Pattern in Laravel per creare interfacce utente dinamiche e manutenibili. Blade fornisce un sistema potente per la separazione della logica di presentazione, migliorando la produttività e la qualità del codice.

La chiave per un uso efficace è organizzare i template in modo logico, creare componenti riutilizzabili e seguire le best practices di Laravel per mantenere il codice pulito e performante.
