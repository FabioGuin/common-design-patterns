<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class CartPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/cart';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
                ->assertVisible('@cart-items')
                ->assertVisible('@cart-summary');
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@cart-items' => '.cart-items',
            '@cart-item' => '.cart-item',
            '@product-name' => '.product-name',
            '@product-price' => '.product-price',
            '@quantity-input' => 'input[name="quantity"]',
            '@remove-button' => 'button[data-action="remove"]',
            '@update-button' => 'button[data-action="update"]',
            '@cart-summary' => '.cart-summary',
            '@subtotal' => '.subtotal',
            '@tax' => '.tax',
            '@shipping' => '.shipping',
            '@total' => '.total',
            '@checkout-button' => 'button[data-action="checkout"]',
            '@continue-shopping' => 'a[href*="products"]',
            '@empty-cart-message' => '.empty-cart',
            '@coupon-input' => 'input[name="coupon"]',
            '@apply-coupon-button' => 'button[data-action="apply-coupon"]',
            '@coupon-message' => '.coupon-message',
        ];
    }

    /**
     * Get cart items count
     */
    public function getItemsCount(Browser $browser): int
    {
        $cartItems = $browser->elements('@cart-item');
        return count($cartItems);
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(Browser $browser): bool
    {
        return $this->getItemsCount($browser) === 0;
    }

    /**
     * Get cart total
     */
    public function getTotal(Browser $browser): ?string
    {
        $totalElement = $browser->element('@total');
        return $totalElement ? $totalElement->getText() : null;
    }

    /**
     * Get subtotal
     */
    public function getSubtotal(Browser $browser): ?string
    {
        $subtotalElement = $browser->element('@subtotal');
        return $subtotalElement ? $subtotalElement->getText() : null;
    }

    /**
     * Update item quantity
     */
    public function updateQuantity(Browser $browser, int $itemIndex, int $quantity)
    {
        $browser->within('.cart-item:nth-child(' . ($itemIndex + 1) . ')', function ($browser) use ($quantity) {
            $browser->type('@quantity-input', (string) $quantity)
                    ->click('@update-button');
        });
        
        return $this;
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Browser $browser, int $itemIndex)
    {
        $browser->within('.cart-item:nth-child(' . ($itemIndex + 1) . ')', function ($browser) {
            $browser->click('@remove-button');
        });
        
        return $this;
    }

    /**
     * Clear entire cart
     */
    public function clearCart(Browser $browser)
    {
        $itemsCount = $this->getItemsCount($browser);
        
        for ($i = 0; $i < $itemsCount; $i++) {
            $this->removeItem($browser, 0); // Always remove first item
        }
        
        return $this;
    }

    /**
     * Apply coupon code
     */
    public function applyCoupon(Browser $browser, string $couponCode)
    {
        $browser->type('@coupon-input', $couponCode)
                ->click('@apply-coupon-button');
        
        return $this;
    }

    /**
     * Get coupon message
     */
    public function getCouponMessage(Browser $browser): ?string
    {
        $messageElement = $browser->element('@coupon-message');
        return $messageElement ? $messageElement->getText() : null;
    }

    /**
     * Go to checkout
     */
    public function goToCheckout(Browser $browser)
    {
        $browser->click('@checkout-button');
        return new CheckoutPage();
    }

    /**
     * Continue shopping
     */
    public function continueShopping(Browser $browser)
    {
        $browser->click('@continue-shopping');
        return new ProductPage();
    }

    /**
     * Get item details by index
     */
    public function getItemDetails(Browser $browser, int $itemIndex): array
    {
        $itemElement = $browser->element('.cart-item:nth-child(' . ($itemIndex + 1) . ')');
        
        if (!$itemElement) {
            return [];
        }
        
        $nameElement = $itemElement->findElement(WebDriverBy::cssSelector('.product-name'));
        $priceElement = $itemElement->findElement(WebDriverBy::cssSelector('.product-price'));
        $quantityElement = $itemElement->findElement(WebDriverBy::cssSelector('input[name="quantity"]'));
        
        return [
            'name' => $nameElement ? $nameElement->getText() : null,
            'price' => $priceElement ? $priceElement->getText() : null,
            'quantity' => $quantityElement ? (int) $quantityElement->getAttribute('value') : 0,
        ];
    }

    /**
     * Check if checkout button is enabled
     */
    public function isCheckoutEnabled(Browser $browser): bool
    {
        $checkoutButton = $browser->element('@checkout-button');
        return $checkoutButton && !$checkoutButton->getAttribute('disabled');
    }

    /**
     * Wait for cart to update
     */
    public function waitForCartUpdate(Browser $browser)
    {
        $browser->waitUntilMissing('.loading-spinner');
        return $this;
    }

    /**
     * Get cart summary details
     */
    public function getCartSummary(Browser $browser): array
    {
        return [
            'subtotal' => $this->getSubtotal($browser),
            'tax' => $this->getElementText($browser, '@tax'),
            'shipping' => $this->getElementText($browser, '@shipping'),
            'total' => $this->getTotal($browser),
        ];
    }

    /**
     * Helper method to get element text
     */
    private function getElementText(Browser $browser, string $selector): ?string
    {
        $element = $browser->element($selector);
        return $element ? $element->getText() : null;
    }
}
