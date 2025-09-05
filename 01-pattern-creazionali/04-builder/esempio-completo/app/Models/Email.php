<?php

namespace App\Models;

class Email
{
    public string $to;
    public string $from;
    public string $subject;
    public string $body;
    public array $attachments;
    public array $headers;
    public string $type;
    public bool $isHtml;

    public function __construct()
    {
        $this->attachments = [];
        $this->headers = [];
        $this->isHtml = false;
    }

    public function toArray(): array
    {
        return [
            'to' => $this->to,
            'from' => $this->from,
            'subject' => $this->subject,
            'body' => $this->body,
            'attachments' => $this->attachments,
            'headers' => $this->headers,
            'type' => $this->type,
            'is_html' => $this->isHtml,
            'created_at' => now()->toDateTimeString()
        ];
    }

    public function render(): string
    {
        $html = "<div style='border: 1px solid #ddd; padding: 20px; margin: 10px;'>";
        $html .= "<h3>Email: {$this->type}</h3>";
        $html .= "<p><strong>To:</strong> {$this->to}</p>";
        $html .= "<p><strong>From:</strong> {$this->from}</p>";
        $html .= "<p><strong>Subject:</strong> {$this->subject}</p>";
        $html .= "<div style='border-top: 1px solid #eee; margin-top: 10px; padding-top: 10px;'>";
        $html .= $this->isHtml ? $this->body : nl2br(htmlspecialchars($this->body));
        $html .= "</div>";
        if (!empty($this->attachments)) {
            $html .= "<p><strong>Attachments:</strong> " . implode(', ', $this->attachments) . "</p>";
        }
        $html .= "</div>";
        return $html;
    }
}
