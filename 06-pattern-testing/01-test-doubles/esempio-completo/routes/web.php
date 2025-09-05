<?php

use App\Http\Controllers\OrderController;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\NotificationService;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Route;

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
    return view('test-doubles.demo');
});

Route::prefix('api')->group(function () {
    // Route per la gestione degli ordini
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders/{id}/payment', [OrderController::class, 'processPayment']);
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    });
});

// Route per la demo dei Test Doubles
Route::get('/demo', function () {
    return view('test-doubles.demo');
});

// Route per testare i servizi con Test Doubles
Route::get('/test-payment', function () {
    $paymentService = new PaymentService();
    $order = new Order([
        'id' => 1,
        'total_amount' => 99.99,
        'status' => Order::STATUS_PENDING
    ]);
    
    $result = $paymentService->processPayment($order, [
        'method' => 'credit_card',
        'card_token' => 'tok_test123'
    ]);
    
    return response()->json([
        'payment_result' => $result,
        'order_status' => $order->status
    ]);
});

Route::get('/test-notification', function () {
    $notificationService = new NotificationService();
    $user = new User([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+393401234567'
    ]);
    
    $emailResult = $notificationService->sendEmail($user, 'Test Subject', 'Test message');
    $smsResult = $notificationService->sendSms($user, 'Test SMS');
    
    return response()->json([
        'email_sent' => $emailResult,
        'sms_sent' => $smsResult
    ]);
});

Route::get('/test-order-service', function () {
    $orderRepository = new OrderRepository();
    $paymentService = new PaymentService();
    $notificationService = new NotificationService();
    
    $orderService = new OrderService($orderRepository, $paymentService, $notificationService);
    
    $user = new User([
        'id' => 1,
        'name' => 'Test User',
        'email' => 'test@example.com'
    ]);
    
    $orderData = [
        'total_amount' => 149.99,
        'payment_method' => Order::PAYMENT_METHOD_CREDIT_CARD,
        'shipping_address' => [
            'street' => 'Via Test 123',
            'city' => 'Milano',
            'postal_code' => '20100',
            'country' => 'Italia'
        ],
        'billing_address' => [
            'street' => 'Via Test 123',
            'city' => 'Milano',
            'postal_code' => '20100',
            'country' => 'Italia'
        ],
        'notes' => 'Test order from demo'
    ];
    
    try {
        $order = $orderService->createOrder($user, $orderData);
        
        return response()->json([
            'order_created' => true,
            'order_id' => $order->id,
            'order_status' => $order->status,
            'total_amount' => $order->total_amount
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'order_created' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});
