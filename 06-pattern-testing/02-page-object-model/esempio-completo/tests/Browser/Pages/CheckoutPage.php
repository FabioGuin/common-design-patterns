<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class CheckoutPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/checkout';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
                ->assertVisible('@checkout-form')
                ->assertVisible('@order-summary');
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@checkout-form' => '.checkout-form',
            '@shipping-section' => '.shipping-section',
            '@billing-section' => '.billing-section',
            '@payment-section' => '.payment-section',
            '@order-summary' => '.order-summary',
            '@first-name' => 'input[name="first_name"]',
            '@last-name' => 'input[name="last_name"]',
            '@email' => 'input[name="email"]',
            '@phone' => 'input[name="phone"]',
            '@address' => 'input[name="address"]',
            '@city' => 'input[name="city"]',
            '@postal-code' => 'input[name="postal_code"]',
            '@country' => 'select[name="country"]',
            '@same-as-shipping' => 'input[name="same_as_shipping"]',
            '@payment-method' => 'input[name="payment_method"]',
            '@card-number' => 'input[name="card_number"]',
            '@expiry-month' => 'select[name="expiry_month"]',
            '@expiry-year' => 'select[name="expiry_year"]',
            '@cvv' => 'input[name="cvv"]',
            '@cardholder-name' => 'input[name="cardholder_name"]',
            '@terms-checkbox' => 'input[name="terms"]',
            '@newsletter-checkbox' => 'input[name="newsletter"]',
            '@place-order-button' => 'button[type="submit"]',
            '@back-to-cart' => 'a[href*="cart"]',
            '@order-total' => '.order-total',
            '@shipping-cost' => '.shipping-cost',
            '@tax-amount' => '.tax-amount',
            '@subtotal' => '.subtotal',
            '@validation-errors' => '.validation-errors',
            '@success-message' => '.success-message',
        ];
    }

    /**
     * Fill shipping information
     */
    public function fillShippingInfo(Browser $browser, array $data)
    {
        $browser->type('@first-name', $data['first_name'] ?? '')
                ->type('@last-name', $data['last_name'] ?? '')
                ->type('@email', $data['email'] ?? '')
                ->type('@phone', $data['phone'] ?? '')
                ->type('@address', $data['address'] ?? '')
                ->type('@city', $data['city'] ?? '')
                ->type('@postal-code', $data['postal_code'] ?? '')
                ->select('@country', $data['country'] ?? 'IT');
        
        return $this;
    }

    /**
     * Fill billing information
     */
    public function fillBillingInfo(Browser $browser, array $data)
    {
        if ($data['same_as_shipping'] ?? false) {
            $browser->check('@same-as-shipping');
        } else {
            $browser->uncheck('@same-as-shipping')
                    ->type('@first-name', $data['first_name'] ?? '')
                    ->type('@last-name', $data['last_name'] ?? '')
                    ->type('@address', $data['address'] ?? '')
                    ->type('@city', $data['city'] ?? '')
                    ->type('@postal-code', $data['postal_code'] ?? '')
                    ->select('@country', $data['country'] ?? 'IT');
        }
        
        return $this;
    }

    /**
     * Fill payment information
     */
    public function fillPaymentInfo(Browser $browser, array $data)
    {
        $browser->radio('@payment-method', $data['payment_method'] ?? 'credit_card');
        
        if (($data['payment_method'] ?? '') === 'credit_card') {
            $browser->type('@card-number', $data['card_number'] ?? '')
                    ->select('@expiry-month', $data['expiry_month'] ?? '12')
                    ->select('@expiry-year', $data['expiry_year'] ?? '2025')
                    ->type('@cvv', $data['cvv'] ?? '')
                    ->type('@cardholder-name', $data['cardholder_name'] ?? '');
        }
        
        return $this;
    }

    /**
     * Accept terms and conditions
     */
    public function acceptTerms(Browser $browser, bool $newsletter = false)
    {
        $browser->check('@terms-checkbox');
        
        if ($newsletter) {
            $browser->check('@newsletter-checkbox');
        } else {
            $browser->uncheck('@newsletter-checkbox');
        }
        
        return $this;
    }

    /**
     * Place order
     */
    public function placeOrder(Browser $browser)
    {
        $browser->click('@place-order-button');
        return $this;
    }

    /**
     * Complete checkout process
     */
    public function completeCheckout(Browser $browser, array $shippingData, array $billingData = null, array $paymentData = null)
    {
        $this->fillShippingInfo($browser, $shippingData);
        
        if ($billingData) {
            $this->fillBillingInfo($browser, $billingData);
        } else {
            $browser->check('@same-as-shipping');
        }
        
        if ($paymentData) {
            $this->fillPaymentInfo($browser, $paymentData);
        }
        
        $this->acceptTerms($browser);
        $this->placeOrder($browser);
        
        return $this;
    }

    /**
     * Go back to cart
     */
    public function goBackToCart(Browser $browser)
    {
        $browser->click('@back-to-cart');
        return new CartPage();
    }

    /**
     * Get order total
     */
    public function getOrderTotal(Browser $browser): ?string
    {
        $totalElement = $browser->element('@order-total');
        return $totalElement ? $totalElement->getText() : null;
    }

    /**
     * Get shipping cost
     */
    public function getShippingCost(Browser $browser): ?string
    {
        $shippingElement = $browser->element('@shipping-cost');
        return $shippingElement ? $shippingElement->getText() : null;
    }

    /**
     * Get tax amount
     */
    public function getTaxAmount(Browser $browser): ?string
    {
        $taxElement = $browser->element('@tax-amount');
        return $taxElement ? $taxElement->getText() : null;
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
     * Check if there are validation errors
     */
    public function hasValidationErrors(Browser $browser): bool
    {
        return $browser->element('@validation-errors') !== null;
    }

    /**
     * Get validation errors
     */
    public function getValidationErrors(Browser $browser): array
    {
        $errorElement = $browser->element('@validation-errors');
        if (!$errorElement) {
            return [];
        }
        
        $errorItems = $errorElement->findElements(WebDriverBy::cssSelector('.error-item'));
        $errors = [];
        
        foreach ($errorItems as $item) {
            $errors[] = $item->getText();
        }
        
        return $errors;
    }

    /**
     * Check if order was placed successfully
     */
    public function isOrderSuccessful(Browser $browser): bool
    {
        return $browser->element('@success-message') !== null;
    }

    /**
     * Get success message
     */
    public function getSuccessMessage(Browser $browser): ?string
    {
        $messageElement = $browser->element('@success-message');
        return $messageElement ? $messageElement->getText() : null;
    }

    /**
     * Wait for order processing
     */
    public function waitForOrderProcessing(Browser $browser)
    {
        $browser->waitUntilMissing('.loading-spinner');
        return $this;
    }

    /**
     * Get order summary details
     */
    public function getOrderSummary(Browser $browser): array
    {
        return [
            'subtotal' => $this->getSubtotal($browser),
            'shipping' => $this->getShippingCost($browser),
            'tax' => $this->getTaxAmount($browser),
            'total' => $this->getOrderTotal($browser),
        ];
    }
}
