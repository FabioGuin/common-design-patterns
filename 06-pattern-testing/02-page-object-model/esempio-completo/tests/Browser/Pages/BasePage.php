<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Page;

abstract class BasePage extends Page
{
    /**
     * Get the global element shortcuts for the site.
     */
    public static function siteElements(): array
    {
        return [
            '@navigation' => 'nav',
            '@user-menu' => '.user-menu',
            '@cart-icon' => '.cart-icon',
            '@search-box' => 'input[name="search"]',
            '@footer' => 'footer',
        ];
    }

    /**
     * Wait for the page to load completely
     */
    public function waitForPageLoad($browser)
    {
        $browser->waitFor('body');
        return $this;
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn($browser)
    {
        return $browser->element('@user-menu') !== null;
    }

    /**
     * Get cart items count
     */
    public function getCartItemsCount($browser)
    {
        $cartIcon = $browser->element('@cart-icon');
        if ($cartIcon) {
            $badge = $cartIcon->findElement(WebDriverBy::className('badge'));
            return $badge ? (int) $badge->getText() : 0;
        }
        return 0;
    }

    /**
     * Search for products
     */
    public function search($browser, $query)
    {
        $browser->type('@search-box', $query)
                ->keys('@search-box', '{enter}');
        
        return $this;
    }

    /**
     * Navigate to cart
     */
    public function goToCart($browser)
    {
        $browser->click('@cart-icon');
        return new CartPage();
    }

    /**
     * Logout user
     */
    public function logout($browser)
    {
        if ($this->isLoggedIn($browser)) {
            $browser->click('@user-menu')
                    ->clickLink('Logout');
        }
        return new LoginPage();
    }
}
