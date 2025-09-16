@extends('layouts.app')

@section('title', 'Crea Ordine - Value Object Pattern')

@section('content')
<h1>Value Object Pattern - Sistema E-commerce</h1>

<div class="alert alert-info">
    <strong>Dimostrazione Value Object:</strong> Questo form utilizza Value Object per Email, Prezzo e Indirizzo con validazione automatica e immutabilità.
</div>

@if(session('order'))
    <div class="alert alert-success">
        <h3>Ordine Creato con Successo!</h3>
        <pre>{{ json_encode(session('order'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
@endif

<form method="POST" action="{{ route('orders.store') }}">
    @csrf
    
    <h2>Informazioni Cliente</h2>
    <div class="form-group">
        <label for="customer_email">Email Cliente *</label>
        <input type="email" id="customer_email" name="customer_email" 
               value="{{ old('customer_email') }}" required>
        <small>Value Object Email con validazione automatica</small>
    </div>

    <h2>Prodotto</h2>
    <div class="form-group">
        <label for="product_name">Nome Prodotto *</label>
        <input type="text" id="product_name" name="product_name" 
               value="{{ old('product_name') }}" required>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="price_euros">Prezzo (€) *</label>
            <input type="number" id="price_euros" name="price_euros" 
                   step="0.01" min="0" value="{{ old('price_euros') }}" required>
            <small>Value Object Price con gestione valute</small>
        </div>
        <div class="form-group">
            <label for="currency">Valuta *</label>
            <select id="currency" name="currency" required>
                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP</option>
            </select>
        </div>
    </div>

    <h2>Indirizzo di Spedizione</h2>
    <div class="form-group">
        <label for="street">Via *</label>
        <input type="text" id="street" name="street" 
               value="{{ old('street') }}" required>
        <small>Value Object Address con validazione completa</small>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="city">Città *</label>
            <input type="text" id="city" name="city" 
                   value="{{ old('city') }}" required>
        </div>
        <div class="form-group">
            <label for="postal_code">Codice Postale *</label>
            <input type="text" id="postal_code" name="postal_code" 
                   value="{{ old('postal_code') }}" required>
        </div>
    </div>

    <div class="form-group">
        <label for="country">Paese *</label>
        <select id="country" name="country" required>
            <option value="IT" {{ old('country') == 'IT' ? 'selected' : '' }}>Italia</option>
            <option value="FR" {{ old('country') == 'FR' ? 'selected' : '' }}>Francia</option>
            <option value="DE" {{ old('country') == 'DE' ? 'selected' : '' }}>Germania</option>
            <option value="ES" {{ old('country') == 'ES' ? 'selected' : '' }}>Spagna</option>
            <option value="US" {{ old('country') == 'US' ? 'selected' : '' }}>Stati Uniti</option>
            <option value="GB" {{ old('country') == 'GB' ? 'selected' : '' }}>Regno Unito</option>
        </select>
    </div>

    <button type="submit" class="btn">Crea Ordine</button>
    <a href="{{ route('orders.test') }}" class="btn btn-secondary">Test Value Object</a>
</form>

<div class="test-results">
    <h3>Caratteristiche Value Object Dimostrate:</h3>
    <ul>
        <li><strong>Email:</strong> Validazione formato, normalizzazione, immutabilità</li>
        <li><strong>Prezzo:</strong> Gestione valute, calcoli sicuri, operazioni matematiche</li>
        <li><strong>Indirizzo:</strong> Validazione completa, formattazione, confronti</li>
        <li><strong>Immutabilità:</strong> Oggetti non modificabili dopo la creazione</li>
        <li><strong>Uguaglianza:</strong> Confronto basato sui valori, non sull'identità</li>
    </ul>
</div>
@endsection
