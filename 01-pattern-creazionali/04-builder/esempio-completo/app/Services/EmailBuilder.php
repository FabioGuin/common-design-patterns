<?php

namespace App\Services;

use App\Models\Email;

interface EmailBuilderInterface
{
    public function setRecipient(string $email): self;
    public function setSender(string $email): self;
    public function setSubject(string $subject): self;
    public function setBody(string $body): self;
    public function addAttachment(string $file): self;
    public function addHeader(string $key, string $value): self;
    public function build(): Email;
}

class WelcomeEmailBuilder implements EmailBuilderInterface
{
    private Email $email;

    public function __construct()
    {
        $this->email = new Email();
        $this->email->type = 'welcome';
    }

    public function setRecipient(string $email): self
    {
        $this->email->to = $email;
        return $this;
    }

    public function setSender(string $email): self
    {
        $this->email->from = $email;
        return $this;
    }

    public function setSubject(string $subject): self
    {
        $this->email->subject = $subject ?: 'Benvenuto!';
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->email->body = $body ?: '<h2>Benvenuto nella nostra piattaforma!</h2><p>Grazie per esserti registrato.</p>';
        $this->email->isHtml = true;
        return $this;
    }

    public function addAttachment(string $file): self
    {
        $this->email->attachments[] = $file;
        return $this;
    }

    public function addHeader(string $key, string $value): self
    {
        $this->email->headers[$key] = $value;
        return $this;
    }

    public function build(): Email
    {
        $this->email->addHeader('X-Email-Type', 'welcome');
        return $this->email;
    }
}

class NewsletterEmailBuilder implements EmailBuilderInterface
{
    private Email $email;

    public function __construct()
    {
        $this->email = new Email();
        $this->email->type = 'newsletter';
    }

    public function setRecipient(string $email): self
    {
        $this->email->to = $email;
        return $this;
    }

    public function setSender(string $email): self
    {
        $this->email->from = $email;
        return $this;
    }

    public function setSubject(string $subject): self
    {
        $this->email->subject = $subject ?: 'Newsletter Settimanale';
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->email->body = $body ?: '<h2>Newsletter</h2><p>Ecco le ultime novità...</p>';
        $this->email->isHtml = true;
        return $this;
    }

    public function addAttachment(string $file): self
    {
        $this->email->attachments[] = $file;
        return $this;
    }

    public function addHeader(string $key, string $value): self
    {
        $this->email->headers[$key] = $value;
        return $this;
    }

    public function build(): Email
    {
        $this->email->addHeader('X-Email-Type', 'newsletter');
        $this->email->addHeader('List-Unsubscribe', '<mailto:unsubscribe@example.com>');
        return $this->email;
    }
}

class NotificationEmailBuilder implements EmailBuilderInterface
{
    private Email $email;

    public function __construct()
    {
        $this->email = new Email();
        $this->email->type = 'notification';
    }

    public function setRecipient(string $email): self
    {
        $this->email->to = $email;
        return $this;
    }

    public function setSender(string $email): self
    {
        $this->email->from = $email;
        return $this;
    }

    public function setSubject(string $subject): self
    {
        $this->email->subject = $subject ?: 'Notifica Importante';
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->email->body = $body ?: 'Hai ricevuto una notifica importante.';
        $this->email->isHtml = false;
        return $this;
    }

    public function addAttachment(string $file): self
    {
        $this->email->attachments[] = $file;
        return $this;
    }

    public function addHeader(string $key, string $value): self
    {
        $this->email->headers[$key] = $value;
        return $this;
    }

    public function build(): Email
    {
        $this->email->addHeader('X-Email-Type', 'notification');
        $this->email->addHeader('X-Priority', 'high');
        return $this->email;
    }
}

class EmailDirector
{
    public function buildWelcomeEmail(EmailBuilderInterface $builder, string $recipient, string $sender): Email
    {
        return $builder
            ->setRecipient($recipient)
            ->setSender($sender)
            ->setSubject('Benvenuto!')
            ->setBody('<h2>Benvenuto nella nostra piattaforma!</h2><p>Grazie per esserti registrato.</p>')
            ->addHeader('X-Priority', 'normal')
            ->build();
    }

    public function buildNewsletterEmail(EmailBuilderInterface $builder, string $recipient, string $sender, string $content): Email
    {
        return $builder
            ->setRecipient($recipient)
            ->setSender($sender)
            ->setSubject('Newsletter Settimanale')
            ->setBody($content)
            ->addHeader('X-Priority', 'low')
            ->build();
    }

    public function buildNotificationEmail(EmailBuilderInterface $builder, string $recipient, string $sender, string $message): Email
    {
        return $builder
            ->setRecipient($recipient)
            ->setSender($sender)
            ->setSubject('Notifica Importante')
            ->setBody($message)
            ->addHeader('X-Priority', 'high')
            ->build();
    }
}
