<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\ProductPage;
use Tests\Browser\Pages\CartPage;
use Tests\Browser\Pages\CheckoutPage;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class EcommerceTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_complete_full_purchase_flow()
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 10
        ]);

        $this->browse(function (Browser $browser) use ($user, $product) {
            // Login
            $loginPage = new LoginPage();
            $browser->visit($loginPage)
                    ->login($user->email, 'password123')
                    ->assertTrue($loginPage->isLoginSuccessful($browser));

            // Browse products
            $productPage = new ProductPage();
            $browser->visit($productPage)
                    ->waitForProductsToLoad()
                    ->assertTrue($productPage->getProductsCount($browser) > 0);

            // View specific product
            $productDetailPage = new ProductPage($product->id);
            $browser->visit($productDetailPage)
                    ->assertSee('Test Product')
                    ->assertSee('€99.99');

            // Add to cart
            $productDetailPage->addToCart($browser, 2);
            $browser->assertSee('Product added to cart');

            // Go to cart
            $cartPage = new CartPage();
            $browser->visit($cartPage)
                    ->assertTrue($cartPage->getItemsCount($browser) > 0)
                    ->assertSee('Test Product');

            // Update quantity
            $cartPage->updateQuantity($browser, 0, 3);
            $browser->assertSee('Cart updated');

            // Go to checkout
            $checkoutPage = new CheckoutPage();
            $browser->visit($checkoutPage)
                    ->assertSee('Checkout');

            // Fill checkout form
            $shippingData = [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'phone' => '+393401234567',
                'address' => 'Via Roma 123',
                'city' => 'Milano',
                'postal_code' => '20100',
                'country' => 'IT'
            ];

            $paymentData = [
                'payment_method' => 'credit_card',
                'card_number' => '4111111111111111',
                'expiry_month' => '12',
                'expiry_year' => '2025',
                'cvv' => '123',
                'cardholder_name' => 'John Doe'
            ];

            $checkoutPage->completeCheckout($browser, $shippingData, null, $paymentData);

            // Verify order success
            $browser->waitForOrderProcessing()
                    ->assertTrue($checkoutPage->isOrderSuccessful($browser))
                    ->assertSee('Order placed successfully');
        });
    }

    /** @test */
    public function user_can_manage_cart_items()
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create();

        $this->browse(function (Browser $browser) use ($user, $products) {
            // Login
            $loginPage = new LoginPage();
            $browser->visit($loginPage)
                    ->login($user->email, 'password')
                    ->assertTrue($loginPage->isLoginSuccessful($browser));

            // Add multiple products to cart
            foreach ($products as $index => $product) {
                $productPage = new ProductPage($product->id);
                $browser->visit($productPage)
                        ->addToCart($browser, $index + 1);
            }

            // Go to cart
            $cartPage = new CartPage();
            $browser->visit($cartPage)
                    ->assertTrue($cartPage->getItemsCount($browser) === 3);

            // Update quantities
            $cartPage->updateQuantity($browser, 0, 5)
                    ->updateQuantity($browser, 1, 2);

            // Remove one item
            $cartPage->removeItem($browser, 2);

            // Verify final state
            $browser->assertTrue($cartPage->getItemsCount($browser) === 2);
        });
    }

    /** @test */
    public function user_can_search_and_filter_products()
    {
        $user = User::factory()->create();
        
        Product::factory()->create(['name' => 'Laptop Gaming', 'category' => 'Electronics', 'price' => 999.99]);
        Product::factory()->create(['name' => 'Smartphone', 'category' => 'Electronics', 'price' => 599.99]);
        Product::factory()->create(['name' => 'T-Shirt', 'category' => 'Clothing', 'price' => 29.99]);

        $this->browse(function (Browser $browser) use ($user) {
            // Login
            $loginPage = new LoginPage();
            $browser->visit($loginPage)
                    ->login($user->email, 'password')
                    ->assertTrue($loginPage->isLoginSuccessful($browser));

            // Search products
            $productPage = new ProductPage();
            $browser->visit($productPage)
                    ->searchProducts('Laptop')
                    ->waitForProductsToLoad()
                    ->assertSee('Laptop Gaming')
                    ->assertDontSee('Smartphone');

            // Filter by category
            $browser->filterByCategory('Electronics')
                    ->waitForProductsToLoad()
                    ->assertSee('Laptop Gaming')
                    ->assertSee('Smartphone')
                    ->assertDontSee('T-Shirt');

            // Sort by price
            $browser->sortProducts('price_asc')
                    ->waitForProductsToLoad();
        });
    }

    /** @test */
    public function user_can_apply_coupon_code()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100.00]);

        $this->browse(function (Browser $browser) use ($user, $product) {
            // Login
            $loginPage = new LoginPage();
            $browser->visit($loginPage)
                    ->login($user->email, 'password')
                    ->assertTrue($loginPage->isLoginSuccessful($browser));

            // Add product to cart
            $productPage = new ProductPage($product->id);
            $browser->visit($productPage)
                    ->addToCart($browser);

            // Go to cart
            $cartPage = new CartPage();
            $browser->visit($cartPage)
                    ->assertTrue($cartPage->getItemsCount($browser) > 0);

            // Apply valid coupon
            $cartPage->applyCoupon($browser, 'SAVE10');
            $browser->assertSee('Coupon applied successfully');

            // Apply invalid coupon
            $cartPage->applyCoupon($browser, 'INVALID');
            $browser->assertSee('Invalid coupon code');
        });
    }

    /** @test */
    public function user_can_manage_wishlist()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $product) {
            // Login
            $loginPage = new LoginPage();
            $browser->visit($loginPage)
                    ->login($user->email, 'password')
                    ->assertTrue($loginPage->isLoginSuccessful($browser));

            // Add to wishlist
            $productPage = new ProductPage($product->id);
            $browser->visit($productPage)
                    ->addToWishlist($browser)
                    ->assertSee('Added to wishlist');

            // Remove from wishlist
            $browser->addToWishlist($browser)
                    ->assertSee('Removed from wishlist');
        });
    }

    /** @test */
    public function checkout_validation_works_correctly()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $product) {
            // Login
            $loginPage = new LoginPage();
            $browser->visit($loginPage)
                    ->login($user->email, 'password')
                    ->assertTrue($loginPage->isLoginSuccessful($browser));

            // Add product to cart
            $productPage = new ProductPage($product->id);
            $browser->visit($productPage)
                    ->addToCart($browser);

            // Go to checkout
            $checkoutPage = new CheckoutPage();
            $browser->visit($checkoutPage);

            // Try to place order without filling required fields
            $checkoutPage->placeOrder($browser);

            // Verify validation errors
            $browser->assertTrue($checkoutPage->hasValidationErrors($browser));
            $errors = $checkoutPage->getValidationErrors($browser);
            $this->assertNotEmpty($errors);
        });
    }

    /** @test */
    public function user_can_navigate_between_pages()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            // Login
            $loginPage = new LoginPage();
            $browser->visit($loginPage)
                    ->login($user->email, 'password')
                    ->assertTrue($loginPage->isLoginSuccessful($browser));

            // Navigate to products
            $productPage = new ProductPage();
            $browser->visit($productPage)
                    ->assertPathIs('/products');

            // Navigate to cart
            $cartPage = new CartPage();
            $browser->visit($cartPage)
                    ->assertPathIs('/cart');

            // Navigate to checkout
            $checkoutPage = new CheckoutPage();
            $browser->visit($checkoutPage)
                    ->assertPathIs('/checkout');

            // Go back to cart
            $checkoutPage->goBackToCart($browser)
                    ->assertPathIs('/cart');

            // Continue shopping
            $cartPage->continueShopping($browser)
                    ->assertPathIs('/products');
        });
    }
}
