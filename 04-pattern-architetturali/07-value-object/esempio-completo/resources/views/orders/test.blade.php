@extends('layouts.app')

@section('title', 'Test Value Object')

@section('content')
<h1>Test Value Object Pattern</h1>

<div class="alert alert-info">
    <strong>Test Automatici:</strong> Questa pagina esegue test automatici sui Value Object per dimostrare il loro funzionamento.
</div>

@foreach($results as $type => $data)
    @if($type === 'email')
        <div class="test-section">
            <h4>Email Value Object</h4>
            @if(isset($data['email1']))
                <p><strong>Email 1:</strong> {{ $data['email1'] }}</p>
                <p><strong>Email 2:</strong> {{ $data['email2'] }}</p>
                <p><strong>Email 1 == Email 2:</strong> {{ $data['email1_equals_email2'] ? 'true' : 'false' }}</p>
                <p><strong>Email 1 == Email 3:</strong> {{ $data['email1_equals_email3'] ? 'true' : 'false' }}</p>
            @else
                <div class="alert alert-danger">
                    <strong>Errore Email:</strong> {{ $data['email_error'] }}
                </div>
            @endif
        </div>
    @endif

    @if($type === 'price')
        <div class="test-section">
            <h4>Price Value Object</h4>
            @if(isset($data['price1']))
                <p><strong>Prezzo 1:</strong> {{ $data['price1'] }}</p>
                <p><strong>Prezzo 2:</strong> {{ $data['price2'] }}</p>
                <p><strong>Prezzo 1 == Prezzo 2:</strong> {{ $data['price1_equals_price2'] ? 'true' : 'false' }}</p>
                <p><strong>Somma Prezzo 1 + Prezzo 3:</strong> {{ $data['sum_price1_price3'] }}</p>
                <p><strong>IVA 22% su Prezzo 1:</strong> {{ $data['tax_22_percent'] }}</p>
                <p><strong>Prezzo 1 > Prezzo 3:</strong> {{ $data['price1_greater_than_price3'] ? 'true' : 'false' }}</p>
            @else
                <div class="alert alert-danger">
                    <strong>Errore Prezzo:</strong> {{ $data['price_error'] }}
                </div>
            @endif
        </div>
    @endif

    @if($type === 'address')
        <div class="test-section">
            <h4>Address Value Object</h4>
            @if(isset($data['address1']))
                <p><strong>Indirizzo 1:</strong> {{ $data['address1'] }}</p>
                <p><strong>Indirizzo 2:</strong> {{ $data['address2'] }}</p>
                <p><strong>Indirizzo 1 == Indirizzo 2:</strong> {{ $data['address1_equals_address2'] ? 'true' : 'false' }}</p>
                <p><strong>Indirizzo 1 == Indirizzo 3:</strong> {{ $data['address1_equals_address3'] ? 'true' : 'false' }}</p>
                <p><strong>Indirizzo 1 in Italia:</strong> {{ $data['address1_in_italy'] ? 'true' : 'false' }}</p>
                <p><strong>Indirizzo 3 in Italia:</strong> {{ $data['address3_in_italy'] ? 'true' : 'false' }}</p>
            @else
                <div class="alert alert-danger">
                    <strong>Errore Indirizzo:</strong> {{ $data['address_error'] }}
                </div>
            @endif
        </div>
    @endif
@endforeach

<div class="test-results">
    <h3>Principi Value Object Dimostrati:</h3>
    <ul>
        <li><strong>Immutabilità:</strong> Gli oggetti non possono essere modificati dopo la creazione</li>
        <li><strong>Uguaglianza per Valore:</strong> Due oggetti con gli stessi valori sono considerati uguali</li>
        <li><strong>Validazione:</strong> I valori vengono validati al momento della creazione</li>
        <li><strong>Comportamento del Dominio:</strong> Metodi specifici per operazioni logiche</li>
        <li><strong>Type Safety:</strong> Prevenzione di errori con tipi primitivi</li>
    </ul>
</div>

<a href="{{ route('orders.index') }}" class="btn">Torna al Form</a>
@endsection
