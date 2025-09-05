<?php

namespace App\Services;

use App\QueryModels\ProductView;
use Illuminate\Database\Eloquent\Collection;

class ProductQueryService
{
    public function searchProducts(array $filters = []): Collection
    {
        $query = ProductView::query();

        // Filtro per disponibilità
        if (isset($filters['available']) && $filters['available']) {
            $query->available();
        }

        // Filtro per categoria
        if (isset($filters['category']) && $filters['category']) {
            $query->byCategory($filters['category']);
        }

        // Filtro per range di prezzo
        if (isset($filters['min_price']) && isset($filters['max_price'])) {
            $query->priceRange($filters['min_price'], $filters['max_price']);
        }

        // Ricerca testuale
        if (isset($filters['search']) && $filters['search']) {
            $query->search($filters['search']);
        }

        // Ordinamento
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $query->orderBy($sortBy, $sortDirection);

        // Paginazione
        $limit = $filters['limit'] ?? 20;
        $offset = $filters['offset'] ?? 0;
        
        return $query->limit($limit)->offset($offset)->get();
    }

    public function getProductById(int $id): ?ProductView
    {
        return ProductView::find($id);
    }

    public function getProductsByCategory(string $category): Collection
    {
        return ProductView::byCategory($category)->available()->get();
    }

    public function getProductStats(): array
    {
        return [
            'total_products' => ProductView::count(),
            'available_products' => ProductView::available()->count(),
            'categories' => ProductView::distinct('category')->pluck('category'),
            'price_range' => [
                'min' => ProductView::min('price'),
                'max' => ProductView::max('price'),
            ],
        ];
    }
}
