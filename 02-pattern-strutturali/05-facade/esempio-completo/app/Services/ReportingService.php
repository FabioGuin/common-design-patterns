<?php

namespace App\Services;

class ReportingService
{
    private array $reports = [];

    /**
     * Genera un report delle vendite
     */
    public function generateSalesReport(array $filters = []): array
    {
        \Log::info('Generating sales report', $filters);

        $reportId = 'RPT_' . uniqid();
        $startDate = $filters['start_date'] ?? now()->subDays(30)->toISOString();
        $endDate = $filters['end_date'] ?? now()->toISOString();

        // Simula la generazione del report
        $report = [
            'id' => $reportId,
            'type' => 'sales_report',
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'metrics' => [
                'total_orders' => rand(50, 200),
                'total_revenue' => rand(5000, 25000),
                'average_order_value' => rand(80, 150),
                'top_products' => $this->getTopProducts(),
            ],
            'generated_at' => now()->toISOString(),
        ];

        $this->reports[$reportId] = $report;

        return [
            'success' => true,
            'report_id' => $reportId,
            'message' => 'Sales report generated successfully',
            'report' => $report,
        ];
    }

    /**
     * Genera un report dell'inventario
     */
    public function generateInventoryReport(): array
    {
        \Log::info('Generating inventory report');

        $reportId = 'RPT_' . uniqid();

        $report = [
            'id' => $reportId,
            'type' => 'inventory_report',
            'metrics' => [
                'total_products' => rand(20, 100),
                'low_stock_items' => rand(5, 15),
                'out_of_stock_items' => rand(0, 5),
                'total_value' => rand(10000, 50000),
            ],
            'generated_at' => now()->toISOString(),
        ];

        $this->reports[$reportId] = $report;

        return [
            'success' => true,
            'report_id' => $reportId,
            'message' => 'Inventory report generated successfully',
            'report' => $report,
        ];
    }

    /**
     * Genera un report dei pagamenti
     */
    public function generatePaymentReport(array $filters = []): array
    {
        \Log::info('Generating payment report', $filters);

        $reportId = 'RPT_' . uniqid();

        $report = [
            'id' => $reportId,
            'type' => 'payment_report',
            'metrics' => [
                'total_payments' => rand(100, 500),
                'successful_payments' => rand(95, 100),
                'failed_payments' => rand(0, 5),
                'total_amount' => rand(15000, 75000),
                'refund_rate' => rand(2, 8) / 100,
            ],
            'generated_at' => now()->toISOString(),
        ];

        $this->reports[$reportId] = $report;

        return [
            'success' => true,
            'report_id' => $reportId,
            'message' => 'Payment report generated successfully',
            'report' => $report,
        ];
    }

    /**
     * Genera un report delle spedizioni
     */
    public function generateShippingReport(): array
    {
        \Log::info('Generating shipping report');

        $reportId = 'RPT_' . uniqid();

        $report = [
            'id' => $reportId,
            'type' => 'shipping_report',
            'metrics' => [
                'total_shipments' => rand(80, 300),
                'delivered' => rand(75, 95),
                'in_transit' => rand(5, 20),
                'delayed' => rand(0, 10),
                'average_delivery_time' => rand(2, 5),
            ],
            'generated_at' => now()->toISOString(),
        ];

        $this->reports[$reportId] = $report;

        return [
            'success' => true,
            'report_id' => $reportId,
            'message' => 'Shipping report generated successfully',
            'report' => $report,
        ];
    }

    /**
     * Ottiene un report specifico
     */
    public function getReport(string $reportId): ?array
    {
        return $this->reports[$reportId] ?? null;
    }

    /**
     * Ottiene tutti i report
     */
    public function getAllReports(): array
    {
        return $this->reports;
    }

    /**
     * Ottiene i report per tipo
     */
    public function getReportsByType(string $type): array
    {
        return array_filter($this->reports, function ($report) use ($type) {
            return $report['type'] === $type;
        });
    }

    /**
     * Ottiene le statistiche dei report
     */
    public function getReportStats(): array
    {
        $total = count($this->reports);
        $byType = [];

        foreach ($this->reports as $report) {
            $type = $report['type'];
            $byType[$type] = ($byType[$type] ?? 0) + 1;
        }

        return [
            'total_reports' => $total,
            'by_type' => $byType,
            'last_report' => $total > 0 ? max(array_column($this->reports, 'generated_at')) : null,
        ];
    }

    /**
     * Ottiene i prodotti top
     */
    private function getTopProducts(): array
    {
        $products = [
            ['name' => 'Laptop Gaming', 'sales' => rand(20, 50)],
            ['name' => 'Smartphone', 'sales' => rand(30, 80)],
            ['name' => 'Cuffie Wireless', 'sales' => rand(40, 100)],
            ['name' => 'Tastiera Meccanica', 'sales' => rand(15, 40)],
            ['name' => 'Monitor 4K', 'sales' => rand(10, 30)],
        ];

        usort($products, function ($a, $b) {
            return $b['sales'] - $a['sales'];
        });

        return array_slice($products, 0, 3);
    }
}
