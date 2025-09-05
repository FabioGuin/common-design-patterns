<?php

namespace App\Services\Templates;

class PDFReportGenerator extends ReportGenerator
{
    protected function formatData(array $data): array
    {
        // Formatta i dati per PDF
        return array_map(function($item) {
            return [
                'title' => strtoupper($item['title']),
                'content' => wordwrap($item['content'], 80)
            ];
        }, $data);
    }
    
    protected function generateHeader(): string
    {
        return "=== PDF REPORT ===\n" . date('Y-m-d H:i:s');
    }
    
    protected function generateBody(array $data): string
    {
        $body = "";
        foreach ($data as $item) {
            $body .= "Title: {$item['title']}\n";
            $body .= "Content: {$item['content']}\n\n";
        }
        return $body;
    }
    
    protected function generateFooter(): string
    {
        return "=== END OF PDF REPORT ===";
    }
}
