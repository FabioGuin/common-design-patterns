<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('event-system.demo');
});

// Public routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    
    // Order routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/payment', [OrderController::class, 'processPayment']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
});

// Demo routes for testing events
Route::get('/demo/register', function () {
    $user = User::create([
        'name' => 'Demo User',
        'email' => 'demo@example.com',
        'password' => bcrypt('password123'),
        'phone' => '+393401234567',
        'newsletter' => true,
        'terms_accepted' => true,
        'address' => [
            'street' => 'Via Demo 123',
            'city' => 'Milano',
            'postal_code' => '20100',
            'country' => 'IT'
        ]
    ]);
    
    return response()->json([
        'message' => 'Demo user created and events fired',
        'user_id' => $user->id
    ]);
});

Route::get('/demo/login/{userId}', function ($userId) {
    $user = User::find($userId);
    
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    
    $user->handleLogin('127.0.0.1', 'Demo Browser');
    
    return response()->json([
        'message' => 'Demo login completed and events fired',
        'user_id' => $user->id
    ]);
});

Route::get('/demo/order/{userId}', function ($userId) {
    $user = User::find($userId);
    
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    
    $order = Order::create([
        'user_id' => $user->id,
        'total_amount' => 99.99,
        'status' => Order::STATUS_PENDING,
        'payment_method' => Order::PAYMENT_METHOD_CREDIT_CARD,
        'shipping_address' => [
            'street' => 'Via Demo 123',
            'city' => 'Milano',
            'postal_code' => '20100',
            'country' => 'IT'
        ],
        'billing_address' => [
            'street' => 'Via Demo 123',
            'city' => 'Milano',
            'postal_code' => '20100',
            'country' => 'IT'
        ],
        'notes' => 'Demo order'
    ]);
    
    return response()->json([
        'message' => 'Demo order created and events fired',
        'order_id' => $order->id
    ]);
});

Route::get('/demo/payment/{orderId}', function ($orderId) {
    $order = Order::find($orderId);
    
    if (!$order) {
        return response()->json(['error' => 'Order not found'], 404);
    }
    
    $order->markAsPaid('credit_card', 'txn_demo_' . uniqid());
    
    return response()->json([
        'message' => 'Demo payment processed and events fired',
        'order_id' => $order->id
    ]);
});

Route::get('/demo/events', function () {
    $events = [
        'User Events' => [
            'UserRegistered' => 'Fired when a user registers',
            'UserLoggedIn' => 'Fired when a user logs in'
        ],
        'Order Events' => [
            'OrderCreated' => 'Fired when an order is created',
            'OrderPaid' => 'Fired when an order is paid'
        ],
        'Listeners' => [
            'SendWelcomeEmail' => 'Sends welcome email to new users',
            'LogUserActivity' => 'Logs user activities to database',
            'SendOrderConfirmation' => 'Sends order confirmation emails'
        ]
    ];
    
    return response()->json([
        'message' => 'Event System Demo',
        'events' => $events,
        'demo_endpoints' => [
            'GET /demo/register' => 'Create demo user and fire UserRegistered event',
            'GET /demo/login/{userId}' => 'Simulate login and fire UserLoggedIn event',
            'GET /demo/order/{userId}' => 'Create demo order and fire OrderCreated event',
            'GET /demo/payment/{orderId}' => 'Process payment and fire OrderPaid event'
        ]
    ]);
});
