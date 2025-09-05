<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ReportDocument;
use App\Models\ContractDocument;
use App\Models\InvoiceDocument;

class DocumentPrototypeTest extends TestCase
{
    public function test_report_document_can_be_cloned()
    {
        $original = new ReportDocument();
        $cloned = $original->clone();
        
        $this->assertInstanceOf(ReportDocument::class, $cloned);
        $this->assertNotEquals($original->id, $cloned->id);
        $this->assertEquals($original->title, $cloned->title);
        $this->assertEquals($original->content, $cloned->content);
        $this->assertEquals($original->type, $cloned->type);
        $this->assertEquals($original->metadata, $cloned->metadata);
    }

    public function test_contract_document_can_be_cloned()
    {
        $original = new ContractDocument();
        $cloned = $original->clone();
        
        $this->assertInstanceOf(ContractDocument::class, $cloned);
        $this->assertNotEquals($original->id, $cloned->id);
        $this->assertEquals($original->title, $cloned->title);
        $this->assertEquals($original->content, $cloned->content);
        $this->assertEquals($original->type, $cloned->type);
        $this->assertEquals($original->metadata, $cloned->metadata);
    }

    public function test_invoice_document_can_be_cloned()
    {
        $original = new InvoiceDocument();
        $cloned = $original->clone();
        
        $this->assertInstanceOf(InvoiceDocument::class, $cloned);
        $this->assertNotEquals($original->id, $cloned->id);
        $this->assertEquals($original->title, $cloned->title);
        $this->assertEquals($original->content, $cloned->content);
        $this->assertEquals($original->type, $cloned->type);
        $this->assertEquals($original->metadata, $cloned->metadata);
    }

    public function test_cloned_documents_have_unique_ids()
    {
        $original = new ReportDocument();
        $cloned1 = $original->clone();
        $cloned2 = $original->clone();
        
        $this->assertNotEquals($original->id, $cloned1->id);
        $this->assertNotEquals($original->id, $cloned2->id);
        $this->assertNotEquals($cloned1->id, $cloned2->id);
    }

    public function test_document_to_array_conversion()
    {
        $document = new ReportDocument();
        $array = $document->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('content', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('metadata', $array);
        $this->assertArrayHasKey('created_at', $array);
    }

    public function test_document_metadata_structure()
    {
        $report = new ReportDocument();
        $contract = new ContractDocument();
        $invoice = new InvoiceDocument();
        
        $this->assertArrayHasKey('sections', $report->metadata);
        $this->assertArrayHasKey('clauses', $contract->metadata);
        $this->assertArrayHasKey('fields', $invoice->metadata);
    }

    public function test_document_implements_prototype_interface()
    {
        $document = new ReportDocument();
        $this->assertInstanceOf(\App\Models\DocumentPrototypeInterface::class, $document);
    }
}
