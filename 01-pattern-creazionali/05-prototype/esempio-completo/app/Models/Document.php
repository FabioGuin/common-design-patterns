<?php

namespace App\Models;

interface DocumentPrototypeInterface
{
    public function clone(): Document;
}

class Document implements DocumentPrototypeInterface
{
    public string $title;
    public string $content;
    public string $type;
    public array $metadata;
    public string $id;

    public function __construct(string $title = '', string $content = '', string $type = 'generic')
    {
        $this->title = $title;
        $this->content = $content;
        $this->type = $type;
        $this->metadata = [];
        $this->id = uniqid('doc_', true);
    }

    public function clone(): Document
    {
        $cloned = new Document($this->title, $this->content, $this->type);
        $cloned->metadata = $this->metadata;
        $cloned->id = uniqid('doc_', true);
        return $cloned;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'metadata' => $this->metadata,
            'created_at' => now()->toDateTimeString()
        ];
    }
}

class ReportDocument extends Document
{
    public function __construct()
    {
        parent::__construct('Report Template', 'This is a report template...', 'report');
        $this->metadata = ['sections' => ['introduction', 'analysis', 'conclusion']];
    }

    public function clone(): Document
    {
        $cloned = new ReportDocument();
        $cloned->id = uniqid('doc_', true);
        return $cloned;
    }
}

class ContractDocument extends Document
{
    public function __construct()
    {
        parent::__construct('Contract Template', 'This is a contract template...', 'contract');
        $this->metadata = ['clauses' => ['terms', 'conditions', 'signatures']];
    }

    public function clone(): Document
    {
        $cloned = new ContractDocument();
        $cloned->id = uniqid('doc_', true);
        return $cloned;
    }
}

class InvoiceDocument extends Document
{
    public function __construct()
    {
        parent::__construct('Invoice Template', 'This is an invoice template...', 'invoice');
        $this->metadata = ['fields' => ['amount', 'date', 'recipient']];
    }

    public function clone(): Document
    {
        $cloned = new InvoiceDocument();
        $cloned->id = uniqid('doc_', true);
        return $cloned;
    }
}
