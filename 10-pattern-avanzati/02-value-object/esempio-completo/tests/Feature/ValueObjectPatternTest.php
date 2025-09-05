<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\ValueObjects\Price;
use App\ValueObjects\Address;
use App\ValueObjects\ProductSku;
use App\ValueObjects\Email;

/**
 * Test per il Value Object Pattern
 * 
 * Questi test dimostrano come i Value Object forniscono
 * type safety, validazione centralizzata e immutabilità.
 */
class ValueObjectPatternTest extends TestCase
{
    /** @test */
    public function it_creates_price_value_objects()
    {
        $price = Price::fromDecimal(10.50, 'EUR');
        
        $this->assertEquals(1050, $price->getCents());
        $this->assertEquals('EUR', $price->getCurrency());
        $this->assertEquals(10.50, $price->getDecimal());
        $this->assertEquals('€10.50', $price->getFormatted());
    }

    /** @test */
    public function it_performs_price_operations()
    {
        $price1 = Price::fromDecimal(10.50, 'EUR');
        $price2 = Price::fromDecimal(5.25, 'EUR');
        
        $sum = $price1->add($price2);
        $this->assertEquals(15.75, $sum->getDecimal());
        
        $subtract = $price1->subtract($price2);
        $this->assertEquals(5.25, $subtract->getDecimal());
        
        $multiply = $price1->multiply(2);
        $this->assertEquals(21.00, $multiply->getDecimal());
    }

    /** @test */
    public function it_compares_prices()
    {
        $price1 = Price::fromDecimal(10.50, 'EUR');
        $price2 = Price::fromDecimal(10.50, 'EUR');
        $price3 = Price::fromDecimal(15.00, 'EUR');
        
        $this->assertTrue($price1->equals($price2));
        $this->assertFalse($price1->equals($price3));
        $this->assertTrue($price3->isGreaterThan($price1));
        $this->assertTrue($price1->isLessThan($price3));
    }

    /** @test */
    public function it_validates_price_creation()
    {
        $this->expectException(\InvalidArgumentException::class);
        Price::fromDecimal(-10.50, 'EUR');
    }

    /** @test */
    public function it_creates_address_value_objects()
    {
        $address = Address::create('Via Roma 123', 'Milano', '20100', 'IT', 'Lombardia');
        
        $this->assertEquals('Via Roma 123', $address->getStreet());
        $this->assertEquals('Milano', $address->getCity());
        $this->assertEquals('20100', $address->getPostalCode());
        $this->assertEquals('IT', $address->getCountry());
        $this->assertEquals('Lombardia', $address->getState());
    }

    /** @test */
    public function it_compares_addresses()
    {
        $address1 = Address::create('Via Roma 123', 'Milano', '20100', 'IT', 'Lombardia');
        $address2 = Address::create('Via Roma 123', 'Milano', '20100', 'IT', 'Lombardia');
        $address3 = Address::create('Via Roma 456', 'Milano', '20100', 'IT', 'Lombardia');
        
        $this->assertTrue($address1->equals($address2));
        $this->assertFalse($address1->equals($address3));
        $this->assertTrue($address1->isSameCountry($address2));
        $this->assertTrue($address1->isSameCity($address2));
    }

    /** @test */
    public function it_validates_address_creation()
    {
        $this->expectException(\InvalidArgumentException::class);
        Address::create('', 'Milano', '20100', 'IT');
    }

    /** @test */
    public function it_creates_sku_value_objects()
    {
        $sku = ProductSku::create('ELC-PHO-ABC');
        
        $this->assertEquals('ELC-PHO-ABC', $sku->toString());
        $this->assertEquals(['ELC', 'PHO', 'ABC'], $sku->getParts());
        $this->assertEquals('ELC', $sku->getCategoryCode());
        $this->assertEquals('PHO', $sku->getProductCode());
        $this->assertEquals('ABC', $sku->getRandomCode());
    }

    /** @test */
    public function it_generates_sku_value_objects()
    {
        $sku = ProductSku::generate('Electronics', 'Phone');
        
        $this->assertTrue($sku->isValid());
        $this->assertTrue($sku->belongsToCategory('Electronics'));
        $this->assertCount(3, $sku->getParts());
    }

    /** @test */
    public function it_compares_sku_value_objects()
    {
        $sku1 = ProductSku::create('ELC-PHO-ABC');
        $sku2 = ProductSku::create('ELC-PHO-ABC');
        $sku3 = ProductSku::create('CLO-SHI-XYZ');
        
        $this->assertTrue($sku1->equals($sku2));
        $this->assertFalse($sku1->equals($sku3));
    }

    /** @test */
    public function it_validates_sku_creation()
    {
        $this->expectException(\InvalidArgumentException::class);
        ProductSku::create('INVALID-SKU');
    }

    /** @test */
    public function it_creates_email_value_objects()
    {
        $email = Email::create('user@example.com');
        
        $this->assertEquals('user@example.com', $email->toString());
        $this->assertEquals('user', $email->getLocalPart());
        $this->assertEquals('example.com', $email->getDomain());
        $this->assertTrue($email->isValid());
    }

    /** @test */
    public function it_compares_email_value_objects()
    {
        $email1 = Email::create('user@example.com');
        $email2 = Email::create('user@example.com');
        $email3 = Email::create('admin@example.com');
        
        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }

    /** @test */
    public function it_validates_email_creation()
    {
        $this->expectException(\InvalidArgumentException::class);
        Email::create('invalid-email');
    }

    /** @test */
    public function it_handles_web_interface()
    {
        $response = $this->get('/value-object');
        
        $response->assertStatus(200);
        $response->assertSee('Value Object Pattern');
    }

    /** @test */
    public function it_handles_api_test_endpoint()
    {
        $response = $this->postJson('/api/value-object/test', [
            'type' => 'price'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_handles_price_calculation_api()
    {
        $response = $this->postJson('/api/value-object/price/calculate', [
            'amount1' => 10.50,
            'currency1' => 'EUR',
            'amount2' => 5.25,
            'currency2' => 'EUR',
            'operation' => 'add'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function it_handles_address_validation_api()
    {
        $response = $this->postJson('/api/value-object/address/validate', [
            'street' => 'Via Roma 123',
            'city' => 'Milano',
            'postalCode' => '20100',
            'country' => 'IT',
            'state' => 'Lombardia'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function value_objects_are_immutable()
    {
        $price = Price::fromDecimal(10.50, 'EUR');
        $originalCents = $price->getCents();
        
        // I Value Object sono immutabili, non possiamo modificarli
        $newPrice = $price->add(Price::fromDecimal(5.00, 'EUR'));
        
        $this->assertEquals($originalCents, $price->getCents());
        $this->assertEquals(1550, $newPrice->getCents());
    }

    /** @test */
    public function value_objects_serialize_correctly()
    {
        $price = Price::fromDecimal(10.50, 'EUR');
        $serialized = $price->jsonSerialize();
        
        $this->assertArrayHasKey('amount', $serialized);
        $this->assertArrayHasKey('cents', $serialized);
        $this->assertArrayHasKey('currency', $serialized);
        $this->assertArrayHasKey('formatted', $serialized);
    }

    /** @test */
    public function value_objects_handle_currency_mismatch()
    {
        $price1 = Price::fromDecimal(10.50, 'EUR');
        $price2 = Price::fromDecimal(10.50, 'USD');
        
        $this->expectException(\InvalidArgumentException::class);
        $price1->add($price2);
    }
}
