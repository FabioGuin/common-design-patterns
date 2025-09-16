<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index()
    {
        return view('orders.index');
    }

    public function store(StoreOrderRequest $request)
    {
        try {
            // Estrai Value Object dal Form Request
            $email = $request->getEmail();
            $price = $request->getPrice();
            $address = $request->getAddress();
            $productName = $request->getProductName();

            // Validazione business con Value Object
            $validationErrors = $this->orderService->validateOrder($email, $price, $address);
            
            if (!empty($validationErrors)) {
                return back()->withErrors(['business' => $validationErrors])->withInput();
            }

            // Crea ordine usando Value Object
            $order = $this->orderService->createOrder($productName, $email, $price, $address);

            return redirect()->route('orders.index')
                ->with('success', 'Ordine creato con successo!')
                ->with('order', $order);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function testValueObjects(Request $request)
    {
        $results = [];

        // Test Email Value Object
        try {
            $email1 = new \App\ValueObjects\Email('mario@example.com');
            $email2 = new \App\ValueObjects\Email('mario@example.com');
            $email3 = new \App\ValueObjects\Email('luigi@example.com');
            
            $results['email'] = [
                'email1' => $email1->getValue(),
                'email2' => $email2->getValue(),
                'email1_equals_email2' => $email1->equals($email2),
                'email1_equals_email3' => $email1->equals($email3),
            ];
        } catch (\Exception $e) {
            $results['email_error'] = $e->getMessage();
        }

        // Test Price Value Object
        try {
            $price1 = \App\ValueObjects\Price::fromEuros(29.99);
            $price2 = \App\ValueObjects\Price::fromEuros(29.99);
            $price3 = \App\ValueObjects\Price::fromEuros(15.50);
            
            $sum = $price1->add($price3);
            $tax = $price1->multiply(0.22);
            
            $results['price'] = [
                'price1' => $price1->__toString(),
                'price2' => $price2->__toString(),
                'price1_equals_price2' => $price1->equals($price2),
                'sum_price1_price3' => $sum->__toString(),
                'tax_22_percent' => $tax->__toString(),
                'price1_greater_than_price3' => $price1->isGreaterThan($price3),
            ];
        } catch (\Exception $e) {
            $results['price_error'] = $e->getMessage();
        }

        // Test Address Value Object
        try {
            $address1 = new \App\ValueObjects\Address(
                'Via Roma 123',
                'Milano',
                '20100',
                'IT'
            );
            $address2 = new \App\ValueObjects\Address(
                'Via Roma 123',
                'Milano',
                '20100',
                'IT'
            );
            $address3 = new \App\ValueObjects\Address(
                '123 Main St',
                'New York',
                '10001',
                'US'
            );
            
            $results['address'] = [
                'address1' => $address1->getFullAddress(),
                'address2' => $address2->getFullAddress(),
                'address1_equals_address2' => $address1->equals($address2),
                'address1_equals_address3' => $address1->equals($address3),
                'address1_in_italy' => $address1->isInCountry('IT'),
                'address3_in_italy' => $address3->isInCountry('IT'),
            ];
        } catch (\Exception $e) {
            $results['address_error'] = $e->getMessage();
        }

        return view('orders.test', compact('results'));
    }
}
