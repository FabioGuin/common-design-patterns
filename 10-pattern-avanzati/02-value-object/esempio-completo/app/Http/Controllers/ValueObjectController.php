<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\ValueObjects\Price;
use App\ValueObjects\Address;
use App\ValueObjects\ProductSku;
use App\ValueObjects\Email;

/**
 * Controller per dimostrare il Value Object Pattern
 * 
 * Questo controller mostra come i Value Object forniscono
 * type safety, validazione centralizzata e immutabilità.
 */
class ValueObjectController extends Controller
{
    /**
     * Endpoint principale - mostra l'interfaccia web
     */
    public function index()
    {
        return view('value_object.example');
    }

    /**
     * Endpoint di test - dimostra il pattern
     */
    public function test(Request $request): JsonResponse
    {
        $testType = $request->input('type', 'all');
        
        $results = [];
        
        switch ($testType) {
            case 'price':
                $results = $this->testPriceValueObjects();
                break;
            case 'address':
                $results = $this->testAddressValueObjects();
                break;
            case 'sku':
                $results = $this->testSkuValueObjects();
                break;
            case 'email':
                $results = $this->testEmailValueObjects();
                break;
            default:
                $results = $this->testAllValueObjects();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Value Object Pattern test completed',
            'data' => $results
        ]);
    }

    /**
     * Test calcolo prezzi
     */
    public function calculatePrice(Request $request): JsonResponse
    {
        $request->validate([
            'amount1' => 'required|numeric',
            'currency1' => 'required|string',
            'amount2' => 'required|numeric',
            'currency2' => 'required|string',
            'operation' => 'required|in:add,subtract,multiply'
        ]);

        try {
            $price1 = Price::fromDecimal($request->amount1, $request->currency1);
            $price2 = Price::fromDecimal($request->amount2, $request->currency2);
            
            $result = match($request->operation) {
                'add' => $price1->add($price2),
                'subtract' => $price1->subtract($price2),
                'multiply' => $price1->multiply($request->amount2)
            };
            
            return response()->json([
                'success' => true,
                'data' => [
                    'price1' => $price1->jsonSerialize(),
                    'price2' => $price2->jsonSerialize(),
                    'operation' => $request->operation,
                    'result' => $result->jsonSerialize()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Test validazione indirizzi
     */
    public function validateAddress(Request $request): JsonResponse
    {
        $request->validate([
            'street' => 'required|string',
            'city' => 'required|string',
            'postalCode' => 'required|string',
            'country' => 'required|string',
            'state' => 'nullable|string'
        ]);

        try {
            $address = Address::create(
                $request->street,
                $request->city,
                $request->postalCode,
                $request->country,
                $request->state
            );
            
            return response()->json([
                'success' => true,
                'data' => $address->jsonSerialize()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Test tutti i Value Object
     */
    private function testAllValueObjects(): array
    {
        return [
            'price' => $this->testPriceValueObjects(),
            'address' => $this->testAddressValueObjects(),
            'sku' => $this->testSkuValueObjects(),
            'email' => $this->testEmailValueObjects()
        ];
    }

    /**
     * Test Value Object per prezzi
     */
    private function testPriceValueObjects(): array
    {
        $price1 = Price::fromDecimal(10.50, 'EUR');
        $price2 = Price::fromDecimal(5.25, 'EUR');
        $price3 = Price::fromDecimal(15.75, 'USD');
        
        return [
            'creation' => [
                'price1' => $price1->jsonSerialize(),
                'price2' => $price2->jsonSerialize(),
                'price3' => $price3->jsonSerialize()
            ],
            'operations' => [
                'add' => $price1->add($price2)->jsonSerialize(),
                'subtract' => $price1->subtract($price2)->jsonSerialize(),
                'multiply' => $price1->multiply(2)->jsonSerialize()
            ],
            'comparisons' => [
                'price1_equals_price2' => $price1->equals($price2),
                'price1_greater_than_price2' => $price1->isGreaterThan($price2),
                'price1_less_than_price3' => $price1->isLessThan($price3)
            ],
            'validation' => [
                'price1_is_zero' => $price1->isZero(),
                'price1_formatted' => $price1->getFormatted()
            ]
        ];
    }

    /**
     * Test Value Object per indirizzi
     */
    private function testAddressValueObjects(): array
    {
        $address1 = Address::create('Via Roma 123', 'Milano', '20100', 'IT', 'Lombardia');
        $address2 = Address::create('Via Roma 456', 'Milano', '20100', 'IT', 'Lombardia');
        $address3 = Address::create('123 Main St', 'New York', '10001', 'US', 'NY');
        
        return [
            'creation' => [
                'address1' => $address1->jsonSerialize(),
                'address2' => $address2->jsonSerialize(),
                'address3' => $address3->jsonSerialize()
            ],
            'comparisons' => [
                'address1_equals_address2' => $address1->equals($address2),
                'address1_same_country_as_address2' => $address1->isSameCountry($address2),
                'address1_same_city_as_address2' => $address1->isSameCity($address2),
                'address1_same_country_as_address3' => $address1->isSameCountry($address3)
            ],
            'validation' => [
                'address1_valid_for_shipping' => $address1->isValidForShipping(),
                'address1_formatted' => $address1->getFormatted(),
                'address1_formatted_multiline' => $address1->getFormattedMultiline()
            ]
        ];
    }

    /**
     * Test Value Object per SKU
     */
    private function testSkuValueObjects(): array
    {
        $sku1 = ProductSku::create('ELC-PHO-ABC');
        $sku2 = ProductSku::create('ELC-PHO-ABC');
        $sku3 = ProductSku::create('CLO-SHI-XYZ');
        $sku4 = ProductSku::generate('Electronics', 'Phone');
        
        return [
            'creation' => [
                'sku1' => $sku1->jsonSerialize(),
                'sku2' => $sku2->jsonSerialize(),
                'sku3' => $sku3->jsonSerialize(),
                'sku4_generated' => $sku4->jsonSerialize()
            ],
            'comparisons' => [
                'sku1_equals_sku2' => $sku1->equals($sku2),
                'sku1_equals_sku3' => $sku1->equals($sku3)
            ],
            'validation' => [
                'sku1_is_valid' => $sku1->isValid(),
                'sku1_belongs_to_electronics' => $sku1->belongsToCategory('Electronics'),
                'sku1_parts' => $sku1->getParts()
            ]
        ];
    }

    /**
     * Test Value Object per email
     */
    private function testEmailValueObjects(): array
    {
        $email1 = Email::create('user@example.com');
        $email2 = Email::create('user@example.com');
        $email3 = Email::create('admin@company.com');
        
        return [
            'creation' => [
                'email1' => $email1->jsonSerialize(),
                'email2' => $email2->jsonSerialize(),
                'email3' => $email3->jsonSerialize()
            ],
            'comparisons' => [
                'email1_equals_email2' => $email1->equals($email2),
                'email1_equals_email3' => $email1->equals($email3)
            ],
            'validation' => [
                'email1_is_valid' => $email1->isValid(),
                'email1_is_corporate' => $email1->isCorporate(),
                'email1_belongs_to_example' => $email1->belongsToDomain('example.com'),
                'email1_masked' => $email1->getMasked()
            ]
        ];
    }
}
