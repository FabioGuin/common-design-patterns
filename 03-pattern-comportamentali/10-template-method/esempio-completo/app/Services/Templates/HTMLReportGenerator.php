<?php

namespace App\Services\Templates;

class HTMLReportGenerator extends ReportGenerator
{
    protected function formatData(array $data): array
    {
        // Formatta i dati per HTML
        return array_map(function($item) {
            return [
                'title' => htmlspecialchars($item['title']),
                'content' => nl2br(htmlspecialchars($item['content']))
            ];
        }, $data);
    }
    
    protected function generateHeader(): string
    {
        return "<h1>HTML Report</h1><p>Generated on: " . date('Y-m-d H:i:s') . "</p>";
    }
    
    protected function generateBody(array $data): string
    {
        $body = "<div class='report-body'>";
        foreach ($data as $item) {
            $body .= "<div class='report-item'>";
            $body .= "<h2>{$item['title']}</h2>";
            $body .= "<p>{$item['content']}</p>";
            $body .= "</div>";
        }
        $body .= "</div>";
        return $body;
    }
    
    protected function generateFooter(): string
    {
        return "<footer><p>End of HTML Report</p></footer>";
    }
}
