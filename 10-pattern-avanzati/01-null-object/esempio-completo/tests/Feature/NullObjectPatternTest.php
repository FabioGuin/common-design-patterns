<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\NotificationServiceFactory;
use App\Services\NullNotificationService;
use App\Services\EmailNotificationService;
use App\Services\SmsNotificationService;

/**
 * Test per il Null Object Pattern
 * 
 * Questi test dimostrano come il Null Object Pattern
 * elimina la necessità di controlli null nel codice.
 */
class NullObjectPatternTest extends TestCase
{
    /** @test */
    public function it_creates_null_object_when_service_is_disabled()
    {
        $service = NotificationServiceFactory::create('disabled');
        
        $this->assertInstanceOf(NullNotificationService::class, $service);
        $this->assertFalse($service->isAvailable());
        $this->assertEquals('null', $service->getType());
    }

    /** @test */
    public function it_creates_null_object_when_service_type_is_invalid()
    {
        $service = NotificationServiceFactory::create('invalid_type');
        
        $this->assertInstanceOf(NullNotificationService::class, $service);
        $this->assertFalse($service->isAvailable());
    }

    /** @test */
    public function it_creates_email_service_when_requested()
    {
        $service = NotificationServiceFactory::create('email');
        
        $this->assertInstanceOf(EmailNotificationService::class, $service);
        $this->assertEquals('email', $service->getType());
    }

    /** @test */
    public function it_creates_sms_service_when_requested()
    {
        $service = NotificationServiceFactory::create('sms');
        
        $this->assertInstanceOf(SmsNotificationService::class, $service);
        $this->assertEquals('sms', $service->getType());
    }

    /** @test */
    public function null_object_does_not_send_notifications()
    {
        $service = new NullNotificationService();
        
        $result = $service->send('Test message', 'user@example.com');
        
        $this->assertFalse($result);
        $this->assertFalse($service->isAvailable());
    }

    /** @test */
    public function null_object_provides_debug_information()
    {
        $service = new NullNotificationService();
        $debugInfo = $service->getDebugInfo();
        
        $this->assertArrayHasKey('type', $debugInfo);
        $this->assertArrayHasKey('available', $debugInfo);
        $this->assertArrayHasKey('description', $debugInfo);
        $this->assertEquals('null', $debugInfo['type']);
        $this->assertFalse($debugInfo['available']);
    }

    /** @test */
    public function null_object_has_additional_methods()
    {
        $service = new NullNotificationService();
        
        $this->assertTrue($service->isNullObject());
        $this->assertStringContains('Null Object Pattern', $service->getDescription());
    }

    /** @test */
    public function factory_returns_available_services()
    {
        $services = NotificationServiceFactory::getAvailableServices();
        
        $this->assertContains('email', $services);
        $this->assertContains('sms', $services);
        $this->assertContains('disabled', $services);
    }

    /** @test */
    public function factory_checks_service_support()
    {
        $this->assertTrue(NotificationServiceFactory::isServiceSupported('email'));
        $this->assertTrue(NotificationServiceFactory::isServiceSupported('sms'));
        $this->assertTrue(NotificationServiceFactory::isServiceSupported('disabled'));
        $this->assertFalse(NotificationServiceFactory::isServiceSupported('invalid'));
    }

    /** @test */
    public function factory_provides_services_info()
    {
        $info = NotificationServiceFactory::getServicesInfo();
        
        $this->assertArrayHasKey('email', $info);
        $this->assertArrayHasKey('sms', $info);
        $this->assertArrayHasKey('disabled', $info);
        
        foreach ($info as $serviceInfo) {
            $this->assertArrayHasKey('type', $serviceInfo);
            $this->assertArrayHasKey('available', $serviceInfo);
            $this->assertArrayHasKey('debug_info', $serviceInfo);
        }
    }

    /** @test */
    public function it_handles_web_interface()
    {
        $response = $this->get('/null-object');
        
        $response->assertStatus(200);
        $response->assertSee('Null Object Pattern');
    }

    /** @test */
    public function it_handles_api_test_endpoint()
    {
        $response = $this->postJson('/api/null-object/test', [
            'type' => 'disabled'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'service_type' => 'null'
            ]
        ]);
    }

    /** @test */
    public function it_handles_api_test_all_endpoint()
    {
        $response = $this->get('/api/null-object/test-all');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $data = $response->json('data');
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('sms', $data);
        $this->assertArrayHasKey('disabled', $data);
    }

    /** @test */
    public function it_handles_api_info_endpoint()
    {
        $response = $this->get('/api/null-object/info');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function null_object_pattern_eliminates_null_checks()
    {
        // Simula un scenario dove il servizio potrebbe essere null
        $serviceType = 'disabled'; // Simula un servizio non configurato
        
        $service = NotificationServiceFactory::create($serviceType);
        
        // Senza Null Object Pattern, dovresti fare:
        // if ($service !== null) {
        //     $service->send('message', 'recipient');
        // }
        
        // Con Null Object Pattern, puoi semplicemente:
        $result = $service->send('Test message', 'user@example.com');
        
        // Il codice funziona sempre, anche se il servizio è "null"
        $this->assertFalse($result); // Null object restituisce false
        $this->assertFalse($service->isAvailable()); // Ma non è un errore
    }
}
