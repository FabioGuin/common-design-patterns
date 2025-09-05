<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class ProductPage extends Page
{
    protected $productId;

    public function __construct($productId = null)
    {
        $this->productId = $productId;
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return $this->productId ? "/products/{$this->productId}" : '/products';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url());
        
        if ($this->productId) {
            $browser->assertVisible('@product-title')
                    ->assertVisible('@product-price')
                    ->assertVisible('@add-to-cart-button');
        } else {
            $browser->assertVisible('@products-grid')
                    ->assertVisible('@product-filters');
        }
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@product-title' => '.product-title',
            '@product-price' => '.product-price',
            '@product-description' => '.product-description',
            '@product-image' => '.product-image img',
            '@add-to-cart-button' => 'button[data-action="add-to-cart"]',
            '@quantity-input' => 'input[name="quantity"]',
            '@wishlist-button' => 'button[data-action="wishlist"]',
            '@share-button' => 'button[data-action="share"]',
            '@products-grid' => '.products-grid',
            '@product-card' => '.product-card',
            '@product-filters' => '.product-filters',
            '@sort-select' => 'select[name="sort"]',
            '@category-filter' => 'select[name="category"]',
            '@price-range' => 'input[name="price_range"]',
            '@search-input' => 'input[name="search"]',
            '@pagination' => '.pagination',
            '@loading-spinner' => '.loading-spinner',
        ];
    }

    /**
     * Add product to cart
     */
    public function addToCart(Browser $browser, int $quantity = 1)
    {
        if ($quantity > 1) {
            $browser->type('@quantity-input', (string) $quantity);
        }
        
        $browser->click('@add-to-cart-button');
        return $this;
    }

    /**
     * Add product to wishlist
     */
    public function addToWishlist(Browser $browser)
    {
        $browser->click('@wishlist-button');
        return $this;
    }

    /**
     * Share product
     */
    public function shareProduct(Browser $browser)
    {
        $browser->click('@share-button');
        return $this;
    }

    /**
     * Get product title
     */
    public function getProductTitle(Browser $browser): ?string
    {
        $titleElement = $browser->element('@product-title');
        return $titleElement ? $titleElement->getText() : null;
    }

    /**
     * Get product price
     */
    public function getProductPrice(Browser $browser): ?string
    {
        $priceElement = $browser->element('@product-price');
        return $priceElement ? $priceElement->getText() : null;
    }

    /**
     * Filter products by category
     */
    public function filterByCategory(Browser $browser, string $category)
    {
        $browser->select('@category-filter', $category);
        return $this;
    }

    /**
     * Sort products
     */
    public function sortProducts(Browser $browser, string $sortBy)
    {
        $browser->select('@sort-select', $sortBy);
        return $this;
    }

    /**
     * Search products
     */
    public function searchProducts(Browser $browser, string $query)
    {
        $browser->type('@search-input', $query)
                ->keys('@search-input', '{enter}');
        return $this;
    }

    /**
     * Get products count on page
     */
    public function getProductsCount(Browser $browser): int
    {
        $productCards = $browser->elements('@product-card');
        return count($productCards);
    }

    /**
     * Wait for products to load
     */
    public function waitForProductsToLoad(Browser $browser)
    {
        $browser->waitUntilMissing('@loading-spinner');
        return $this;
    }

    /**
     * Go to next page
     */
    public function goToNextPage(Browser $browser)
    {
        $browser->click('.pagination .next');
        return $this;
    }

    /**
     * Go to previous page
     */
    public function goToPreviousPage(Browser $browser)
    {
        $browser->click('.pagination .prev');
        return $this;
    }

    /**
     * Go to specific page
     */
    public function goToPage(Browser $browser, int $page)
    {
        $browser->click(".pagination a[data-page='{$page}']");
        return $this;
    }

    /**
     * Check if product is in stock
     */
    public function isProductInStock(Browser $browser): bool
    {
        $addToCartButton = $browser->element('@add-to-cart-button');
        return $addToCartButton && !$addToCartButton->getAttribute('disabled');
    }

    /**
     * Get product availability status
     */
    public function getProductAvailability(Browser $browser): string
    {
        if ($this->isProductInStock($browser)) {
            return 'in_stock';
        }
        
        $stockElement = $browser->element('.stock-status');
        return $stockElement ? $stockElement->getText() : 'unknown';
    }
}
