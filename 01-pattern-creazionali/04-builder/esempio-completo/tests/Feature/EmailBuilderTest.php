<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\EmailDirector;
use App\Services\WelcomeEmailBuilder;
use App\Services\NewsletterEmailBuilder;
use App\Services\NotificationEmailBuilder;

class EmailBuilderTest extends TestCase
{
    public function test_welcome_email_builder_creates_correct_email()
    {
        $builder = new WelcomeEmailBuilder();
        $director = new EmailDirector();
        
        $email = $director->buildWelcomeEmail($builder, 'user@example.com', 'noreply@example.com');
        
        $this->assertEquals('user@example.com', $email->to);
        $this->assertEquals('noreply@example.com', $email->from);
        $this->assertEquals('Benvenuto!', $email->subject);
        $this->assertStringContains('Benvenuto nella nostra piattaforma!', $email->body);
        $this->assertEquals('welcome', $email->type);
        $this->assertTrue($email->isHtml);
        $this->assertArrayHasKey('X-Email-Type', $email->headers);
    }

    public function test_newsletter_email_builder_creates_correct_email()
    {
        $builder = new NewsletterEmailBuilder();
        $director = new EmailDirector();
        
        $email = $director->buildNewsletterEmail($builder, 'subscriber@example.com', 'newsletter@example.com', '<h2>Newsletter</h2><p>Contenuto...</p>');
        
        $this->assertEquals('subscriber@example.com', $email->to);
        $this->assertEquals('newsletter@example.com', $email->from);
        $this->assertEquals('Newsletter Settimanale', $email->subject);
        $this->assertStringContains('Newsletter', $email->body);
        $this->assertEquals('newsletter', $email->type);
        $this->assertTrue($email->isHtml);
        $this->assertArrayHasKey('List-Unsubscribe', $email->headers);
    }

    public function test_notification_email_builder_creates_correct_email()
    {
        $builder = new NotificationEmailBuilder();
        $director = new EmailDirector();
        
        $email = $director->buildNotificationEmail($builder, 'admin@example.com', 'system@example.com', 'Notifica importante');
        
        $this->assertEquals('admin@example.com', $email->to);
        $this->assertEquals('system@example.com', $email->from);
        $this->assertEquals('Notifica Importante', $email->subject);
        $this->assertEquals('Notifica importante', $email->body);
        $this->assertEquals('notification', $email->type);
        $this->assertFalse($email->isHtml);
        $this->assertArrayHasKey('X-Priority', $email->headers);
    }

    public function test_builder_fluent_interface_works()
    {
        $builder = new WelcomeEmailBuilder();
        
        $email = $builder
            ->setRecipient('test@example.com')
            ->setSender('sender@example.com')
            ->setSubject('Test Subject')
            ->setBody('Test Body')
            ->addAttachment('file.pdf')
            ->addHeader('X-Custom', 'value')
            ->build();
        
        $this->assertEquals('test@example.com', $email->to);
        $this->assertEquals('sender@example.com', $email->from);
        $this->assertEquals('Test Subject', $email->subject);
        $this->assertEquals('Test Body', $email->body);
        $this->assertContains('file.pdf', $email->attachments);
        $this->assertEquals('value', $email->headers['X-Custom']);
    }

    public function test_email_to_array_conversion()
    {
        $builder = new WelcomeEmailBuilder();
        $email = $builder
            ->setRecipient('test@example.com')
            ->setSender('sender@example.com')
            ->build();
        
        $array = $email->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('to', $array);
        $this->assertArrayHasKey('from', $array);
        $this->assertArrayHasKey('subject', $array);
        $this->assertArrayHasKey('body', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('created_at', $array);
    }

    public function test_email_render_method()
    {
        $builder = new WelcomeEmailBuilder();
        $email = $builder
            ->setRecipient('test@example.com')
            ->setSender('sender@example.com')
            ->setSubject('Test Subject')
            ->setBody('<h2>Test</h2><p>Content</p>')
            ->build();
        
        $html = $email->render();
        
        $this->assertStringContains('test@example.com', $html);
        $this->assertStringContains('sender@example.com', $html);
        $this->assertStringContains('Test Subject', $html);
        $this->assertStringContains('<h2>Test</h2>', $html);
    }
}
