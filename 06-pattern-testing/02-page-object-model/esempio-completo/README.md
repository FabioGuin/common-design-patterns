# Esempio Completo: Page Object Model Pattern

## Scopo

Questo esempio dimostra l'utilizzo del Page Object Model pattern in Laravel Dusk per testare un'applicazione e-commerce con pagine multiple.

## Funzionalità

- **LoginPage**: Gestione del login utente
- **ProductPage**: Visualizzazione e gestione prodotti
- **CartPage**: Gestione del carrello
- **CheckoutPage**: Processo di checkout
- **BasePage**: Funzionalità comuni a tutte le pagine

## Struttura

```
esempio-completo/
├── README.md
├── composer.json
├── .env.example
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   └── Order.php
│   └── Http/Controllers/
│       ├── HomeController.php
│       ├── ProductController.php
│       └── CartController.php
├── tests/
│   ├── Browser/
│   │   ├── Pages/
│   │   │   ├── BasePage.php
│   │   │   ├── LoginPage.php
│   │   │   ├── ProductPage.php
│   │   │   ├── CartPage.php
│   │   │   └── CheckoutPage.php
│   │   └── EcommerceTest.php
│   └── Feature/
│       └── EcommerceFeatureTest.php
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php
│   ├── home.blade.php
│   ├── products/
│   │   └── show.blade.php
│   ├── cart/
│   │   └── index.blade.php
│   └── checkout/
│       └── index.blade.php
└── routes/
    └── web.php
```

## Installazione

1. Clona il repository
2. Esegui `composer install`
3. Configura `.env` con i tuoi parametri
4. Esegui `php artisan dusk:install`
5. Esegui `php artisan dusk` per vedere i test in azione

## Test Inclusi

- **Browser Test**: Test end-to-end con Page Objects
- **Feature Test**: Test di integrazione
- **Page Object Test**: Test specifici per ogni pagina

## Pattern Utilizzati

- **Page Object Model**: Per astrazione delle pagine
- **Base Page**: Per funzionalità comuni
- **Component Objects**: Per elementi riutilizzabili
- **Factory Pattern**: Per creazione di dati di test
