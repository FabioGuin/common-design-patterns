<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurazione per i test
        config([
            'services.notification.email_url' => 'https://api.test-email.com',
            'services.notification.sms_url' => 'https://api.test-sms.com'
        ]);

        $this->notificationService = new NotificationService();
    }

    /** @test */
    public function it_sends_email_successfully()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $subject = 'Test Subject';
        $message = 'Test message content';

        // Act
        $result = $this->notificationService->sendEmail($user, $subject, $message);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_fails_to_send_email_when_user_has_no_email()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => null
        ]);

        $subject = 'Test Subject';
        $message = 'Test message content';

        // Act
        $result = $this->notificationService->sendEmail($user, $subject, $message);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_sends_sms_successfully()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'phone' => '+393401234567'
        ]);

        $message = 'Test SMS message';

        // Act
        $result = $this->notificationService->sendSms($user, $message);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_fails_to_send_sms_when_user_has_no_phone()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'phone' => null
        ]);

        $message = 'Test SMS message';

        // Act
        $result = $this->notificationService->sendSms($user, $message);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_sends_order_confirmation()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $order = new Order([
            'id' => 123,
            'user_id' => $user->id,
            'total_amount' => 99.99,
            'status' => Order::STATUS_PAID
        ]);

        // Act
        $result = $this->notificationService->sendOrderConfirmation($order);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_sends_order_update_via_email()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => null // Solo email
        ]);

        $order = new Order([
            'id' => 123,
            'user_id' => $user->id,
            'total_amount' => 99.99,
            'status' => Order::STATUS_SHIPPED
        ]);

        $updateMessage = 'Il tuo ordine è stato spedito';

        // Act
        $result = $this->notificationService->sendOrderUpdate($order, $updateMessage);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_sends_order_update_via_sms()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => null, // Solo SMS
            'phone' => '+393401234567'
        ]);

        $order = new Order([
            'id' => 123,
            'user_id' => $user->id,
            'total_amount' => 99.99,
            'status' => Order::STATUS_SHIPPED
        ]);

        $updateMessage = 'Il tuo ordine è stato spedito';

        // Act
        $result = $this->notificationService->sendOrderUpdate($order, $updateMessage);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_sends_order_update_via_both_email_and_sms()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+393401234567'
        ]);

        $order = new Order([
            'id' => 123,
            'user_id' => $user->id,
            'total_amount' => 99.99,
            'status' => Order::STATUS_SHIPPED
        ]);

        $updateMessage = 'Il tuo ordine è stato spedito';

        // Act
        $result = $this->notificationService->sendOrderUpdate($order, $updateMessage);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_handles_order_update_when_no_contact_methods()
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => null,
            'phone' => null
        ]);

        $order = new Order([
            'id' => 123,
            'user_id' => $user->id,
            'total_amount' => 99.99,
            'status' => Order::STATUS_SHIPPED
        ]);

        $updateMessage = 'Il tuo ordine è stato spedito';

        // Act
        $result = $this->notificationService->sendOrderUpdate($order, $updateMessage);

        // Assert
        $this->assertFalse($result);
    }
}
