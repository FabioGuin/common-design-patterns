<?php

namespace App\Services;

use App\QueryModels\OrderView;
use Illuminate\Database\Eloquent\Collection;

class OrderQueryService
{
    public function getOrdersByUser(int $userId, array $filters = []): Collection
    {
        $query = OrderView::byUser($userId);

        // Filtro per status
        if (isset($filters['status']) && $filters['status']) {
            $query->byStatus($filters['status']);
        }

        // Filtro per range di date
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        // Ordinamento
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        // Paginazione
        $limit = $filters['limit'] ?? 20;
        $offset = $filters['offset'] ?? 0;
        
        return $query->limit($limit)->offset($offset)->get();
    }

    public function getOrderById(int $id): ?OrderView
    {
        return OrderView::find($id);
    }

    public function getOrderStats(int $userId = null): array
    {
        $query = OrderView::query();
        
        if ($userId) {
            $query->byUser($userId);
        }

        return [
            'total_orders' => $query->count(),
            'total_amount' => $query->sum('total_amount'),
            'average_order_value' => $query->avg('total_amount'),
            'orders_by_status' => $query->groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->pluck('count', 'status'),
        ];
    }

    public function getRecentOrders(int $limit = 10): Collection
    {
        return OrderView::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
