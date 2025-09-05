<?php

namespace App\Services\Templates;

abstract class ReportGenerator
{
    // Template method - definisce la struttura
    final public function generateReport(array $data): string
    {
        $this->validateData($data);
        $formattedData = $this->formatData($data);
        $header = $this->generateHeader();
        $body = $this->generateBody($formattedData);
        $footer = $this->generateFooter();
        
        return $this->combineSections($header, $body, $footer);
    }
    
    // Metodi astratti - devono essere implementati dalle sottoclassi
    abstract protected function formatData(array $data): array;
    abstract protected function generateHeader(): string;
    abstract protected function generateBody(array $data): string;
    abstract protected function generateFooter(): string;
    
    // Metodi concreti - implementazione comune
    protected function validateData(array $data): void
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Data cannot be empty');
        }
    }
    
    protected function combineSections(string $header, string $body, string $footer): string
    {
        return $header . "\n" . $body . "\n" . $footer;
    }
}
