<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class LoginPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/login';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
                ->assertSee('Login')
                ->assertVisible('@email-input')
                ->assertVisible('@password-input')
                ->assertVisible('@login-button');
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@email-input' => 'input[name="email"]',
            '@password-input' => 'input[name="password"]',
            '@login-button' => 'button[type="submit"]',
            '@remember-checkbox' => 'input[name="remember"]',
            '@forgot-password-link' => 'a[href*="password/reset"]',
            '@register-link' => 'a[href*="register"]',
            '@error-message' => '.alert-danger',
            '@success-message' => '.alert-success',
        ];
    }

    /**
     * Fill login form with credentials
     */
    public function fillCredentials(Browser $browser, string $email, string $password, bool $remember = false)
    {
        $browser->type('@email-input', $email)
                ->type('@password-input', $password);
        
        if ($remember) {
            $browser->check('@remember-checkbox');
        }
        
        return $this;
    }

    /**
     * Submit login form
     */
    public function submit(Browser $browser)
    {
        $browser->click('@login-button');
        return $this;
    }

    /**
     * Login with credentials
     */
    public function login(Browser $browser, string $email, string $password, bool $remember = false)
    {
        return $this->fillCredentials($browser, $email, $password, $remember)
                    ->submit($browser);
    }

    /**
     * Check if login was successful
     */
    public function isLoginSuccessful(Browser $browser): bool
    {
        return $browser->assertPathIs('/') && 
               $browser->element('@user-menu') !== null;
    }

    /**
     * Check if there are validation errors
     */
    public function hasValidationErrors(Browser $browser): bool
    {
        return $browser->element('@error-message') !== null;
    }

    /**
     * Get error message text
     */
    public function getErrorMessage(Browser $browser): ?string
    {
        $errorElement = $browser->element('@error-message');
        return $errorElement ? $errorElement->getText() : null;
    }

    /**
     * Go to forgot password page
     */
    public function goToForgotPassword(Browser $browser)
    {
        $browser->click('@forgot-password-link');
        return new ForgotPasswordPage();
    }

    /**
     * Go to registration page
     */
    public function goToRegistration(Browser $browser)
    {
        $browser->click('@register-link');
        return new RegisterPage();
    }

    /**
     * Clear form fields
     */
    public function clearForm(Browser $browser)
    {
        $browser->clear('@email-input')
                ->clear('@password-input')
                ->uncheck('@remember-checkbox');
        
        return $this;
    }
}
