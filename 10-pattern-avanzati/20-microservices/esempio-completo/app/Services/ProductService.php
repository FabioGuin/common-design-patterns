<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    protected $serviceId = 'product-service';
    protected $version = '1.0.0';

    /**
     * Crea un nuovo prodotto
     */
    public function createProduct(array $productData): array
    {
        try {
            // Valida i dati del prodotto
            $this->validateProductData($productData);

            // Crea il prodotto
            $product = new Product([
                'name' => $productData['name'],
                'description' => $productData['description'] ?? '',
                'price' => $productData['price'],
                'sku' => $productData['sku'] ?? $this->generateSku(),
                'category' => $productData['category'] ?? 'general',
                'stock_quantity' => $productData['stock_quantity'] ?? 0,
                'status' => 'active'
            ]);

            $product->save();

            // Cache del prodotto
            Cache::put("product:{$product->id}", $product, 3600);

            Log::info("Product Service: Prodotto creato", [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'service' => $this->serviceId
            ]);

            return [
                'success' => true,
                'data' => $product->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Product Service: Errore nella creazione prodotto", [
                'error' => $e->getMessage(),
                'product_data' => $productData,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene un prodotto per ID
     */
    public function getProduct(string $productId): array
    {
        try {
            // Prova prima la cache
            $cachedProduct = Cache::get("product:{$productId}");
            if ($cachedProduct) {
                return [
                    'success' => true,
                    'data' => $cachedProduct->toArray(),
                    'service' => $this->serviceId,
                    'cached' => true
                ];
            }

            // Recupera dal database
            $product = Product::find($productId);
            if (!$product) {
                return [
                    'success' => false,
                    'error' => 'Prodotto non trovato',
                    'service' => $this->serviceId
                ];
            }

            // Cache del prodotto
            Cache::put("product:{$productId}", $product, 3600);

            return [
                'success' => true,
                'data' => $product->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Product Service: Errore nel recupero prodotto", [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene un prodotto per SKU
     */
    public function getProductBySku(string $sku): array
    {
        try {
            $product = Product::where('sku', $sku)->first();
            if (!$product) {
                return [
                    'success' => false,
                    'error' => 'Prodotto non trovato',
                    'service' => $this->serviceId
                ];
            }

            return [
                'success' => true,
                'data' => $product->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Product Service: Errore nel recupero prodotto per SKU", [
                'error' => $e->getMessage(),
                'sku' => $sku,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Lista tutti i prodotti
     */
    public function listProducts(int $limit = 100, int $offset = 0, array $filters = []): array
    {
        try {
            $query = Product::query();

            // Applica filtri
            if (isset($filters['category'])) {
                $query->where('category', $filters['category']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['min_price'])) {
                $query->where('price', '>=', $filters['min_price']);
            }

            if (isset($filters['max_price'])) {
                $query->where('price', '<=', $filters['max_price']);
            }

            if (isset($filters['search'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('description', 'like', '%' . $filters['search'] . '%');
                });
            }

            $products = $query->limit($limit)->offset($offset)->get();
            $productsArray = $products->map(function($product) {
                return $product->toArray();
            })->toArray();

            return [
                'success' => true,
                'data' => $productsArray,
                'count' => count($productsArray),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Product Service: Errore nel recupero lista prodotti", [
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Aggiorna un prodotto
     */
    public function updateProduct(string $productId, array $updateData): array
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                return [
                    'success' => false,
                    'error' => 'Prodotto non trovato',
                    'service' => $this->serviceId
                ];
            }

            // Aggiorna i campi
            foreach ($updateData as $field => $value) {
                if (in_array($field, ['name', 'description', 'price', 'category', 'stock_quantity', 'status'])) {
                    $product->$field = $value;
                }
            }

            $product->save();

            // Aggiorna la cache
            Cache::put("product:{$productId}", $product, 3600);

            Log::info("Product Service: Prodotto aggiornato", [
                'product_id' => $productId,
                'updated_fields' => array_keys($updateData),
                'service' => $this->serviceId
            ]);

            return [
                'success' => true,
                'data' => $product->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Product Service: Errore nell'aggiornamento prodotto", [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Aggiorna lo stock di un prodotto
     */
    public function updateStock(string $productId, int $quantity): array
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                return [
                    'success' => false,
                    'error' => 'Prodotto non trovato',
                    'service' => $this->serviceId
                ];
            }

            $product->stock_quantity = $quantity;
            $product->save();

            // Aggiorna la cache
            Cache::put("product:{$productId}", $product, 3600);

            Log::info("Product Service: Stock aggiornato", [
                'product_id' => $productId,
                'new_quantity' => $quantity,
                'service' => $this->serviceId
            ]);

            return [
                'success' => true,
                'data' => $product->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Product Service: Errore nell'aggiornamento stock", [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Verifica la disponibilità di un prodotto
     */
    public function checkAvailability(string $productId, int $quantity): array
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                return [
                    'success' => false,
                    'error' => 'Prodotto non trovato',
                    'service' => $this->serviceId
                ];
            }

            $available = $product->stock_quantity >= $quantity;

            return [
                'success' => true,
                'data' => [
                    'product_id' => $productId,
                    'requested_quantity' => $quantity,
                    'available_quantity' => $product->stock_quantity,
                    'available' => $available
                ],
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Product Service: Errore nella verifica disponibilità", [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Riserva stock per un ordine
     */
    public function reserveStock(string $productId, int $quantity): array
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                return [
                    'success' => false,
                    'error' => 'Prodotto non trovato',
                    'service' => $this->serviceId
                ];
            }

            if ($product->stock_quantity < $quantity) {
                return [
                    'success' => false,
                    'error' => 'Stock insufficiente',
                    'service' => $this->serviceId
                ];
            }

            $product->stock_quantity -= $quantity;
            $product->save();

            // Aggiorna la cache
            Cache::put("product:{$productId}", $product, 3600);

            Log::info("Product Service: Stock riservato", [
                'product_id' => $productId,
                'reserved_quantity' => $quantity,
                'remaining_stock' => $product->stock_quantity,
                'service' => $this->serviceId
            ]);

            return [
                'success' => true,
                'data' => $product->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Product Service: Errore nella riserva stock", [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Rilascia stock riservato
     */
    public function releaseStock(string $productId, int $quantity): array
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                return [
                    'success' => false,
                    'error' => 'Prodotto non trovato',
                    'service' => $this->serviceId
                ];
            }

            $product->stock_quantity += $quantity;
            $product->save();

            // Aggiorna la cache
            Cache::put("product:{$productId}", $product, 3600);

            Log::info("Product Service: Stock rilasciato", [
                'product_id' => $productId,
                'released_quantity' => $quantity,
                'new_stock' => $product->stock_quantity,
                'service' => $this->serviceId
            ]);

            return [
                'success' => true,
                'data' => $product->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Product Service: Errore nel rilascio stock", [
                'error' => $e->getMessage(),
                'product_id' => $productId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene statistiche dei prodotti
     */
    public function getProductStats(): array
    {
        try {
            $totalProducts = Product::count();
            $activeProducts = Product::where('status', 'active')->count();
            $outOfStock = Product::where('stock_quantity', 0)->count();
            $lowStock = Product::where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10)->count();

            return [
                'success' => true,
                'data' => [
                    'total_products' => $totalProducts,
                    'active_products' => $activeProducts,
                    'out_of_stock' => $outOfStock,
                    'low_stock' => $lowStock
                ],
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("Product Service: Errore nel recupero statistiche", [
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Health check del servizio
     */
    public function healthCheck(): array
    {
        try {
            // Verifica connessione database
            Product::count();

            return [
                'success' => true,
                'status' => 'healthy',
                'service' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'service' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Valida i dati del prodotto
     */
    private function validateProductData(array $productData): void
    {
        $required = ['name', 'price'];
        
        foreach ($required as $field) {
            if (!isset($productData[$field]) || empty($productData[$field])) {
                throw new \InvalidArgumentException("Campo obbligatorio mancante: {$field}");
            }
        }

        // Valida prezzo
        if (!is_numeric($productData['price']) || $productData['price'] < 0) {
            throw new \InvalidArgumentException("Prezzo non valido");
        }

        // Valida stock
        if (isset($productData['stock_quantity']) && (!is_numeric($productData['stock_quantity']) || $productData['stock_quantity'] < 0)) {
            throw new \InvalidArgumentException("Quantità stock non valida");
        }
    }

    /**
     * Genera un SKU univoco
     */
    private function generateSku(): string
    {
        return 'SKU_' . strtoupper(uniqid());
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->serviceId;
    }

    /**
     * Ottiene la versione del servizio
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
