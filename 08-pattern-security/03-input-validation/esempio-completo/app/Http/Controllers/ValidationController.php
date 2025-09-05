<?php

namespace App\Http\Controllers;

use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ValidationController extends Controller
{
    private ValidationService $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Validate user input
     */
    public function validateUser(Request $request): JsonResponse
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'age' => 'required|integer|min:18|max:120',
                'phone' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/',
                'website' => 'nullable|url',
                'bio' => 'nullable|string|max:1000'
            ];

            $validatedData = $this->validationService->validate($request->all(), $rules);

            return response()->json([
                'success' => true,
                'message' => 'Validation passed',
                'data' => $validatedData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Validate product input
     */
    public function validateProduct(Request $request): JsonResponse
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'price' => 'required|numeric|min:0.01',
                'category_id' => 'required|integer|exists:categories,id',
                'sku' => 'required|string|unique:products,sku',
                'stock' => 'required|integer|min:0',
                'images' => 'nullable|array|max:5',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50'
            ];

            $validatedData = $this->validationService->validate($request->all(), $rules);

            return response()->json([
                'success' => true,
                'message' => 'Validation passed',
                'data' => $validatedData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Validate order input
     */
    public function validateOrder(Request $request): JsonResponse
    {
        try {
            $rules = [
                'customer_id' => 'required|integer|exists:users,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1|max:100',
                'items.*.price' => 'required|numeric|min:0.01',
                'shipping_address' => 'required|array',
                'shipping_address.street' => 'required|string|max:255',
                'shipping_address.city' => 'required|string|max:100',
                'shipping_address.state' => 'required|string|max:100',
                'shipping_address.zip' => 'required|string|max:20',
                'shipping_address.country' => 'required|string|max:100',
                'payment_method' => 'required|string|in:credit_card,paypal,bank_transfer',
                'notes' => 'nullable|string|max:500'
            ];

            $validatedData = $this->validationService->validate($request->all(), $rules);

            return response()->json([
                'success' => true,
                'message' => 'Validation passed',
                'data' => $validatedData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Sanitize input
     */
    public function sanitizeInput(Request $request): JsonResponse
    {
        try {
            $input = $request->all();
            $sanitizedData = $this->validationService->sanitize($input);

            return response()->json([
                'success' => true,
                'message' => 'Input sanitized successfully',
                'original' => $input,
                'sanitized' => $sanitizedData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sanitization failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate with custom rules
     */
    public function validateWithCustomRules(Request $request): JsonResponse
    {
        try {
            $rules = [
                'email' => 'required|email|unique:users,email',
                'password' => ['required', 'string', 'min:8', new \App\Rules\StrongPassword()],
                'phone' => ['required', new \App\Rules\ValidPhoneNumber()],
                'credit_card' => ['required', new \App\Rules\ValidCreditCard()],
                'date_of_birth' => 'required|date|before:today|after:1900-01-01'
            ];

            $validatedData = $this->validationService->validate($request->all(), $rules);

            return response()->json([
                'success' => true,
                'message' => 'Custom validation passed',
                'data' => $validatedData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Custom validation failed',
                'errors' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get validation statistics
     */
    public function getValidationStats(): JsonResponse
    {
        try {
            $stats = $this->validationService->getValidationStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get validation statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test validation rules
     */
    public function testValidationRules(Request $request): JsonResponse
    {
        try {
            $testData = $request->input('test_data', []);
            $rules = $request->input('rules', []);
            
            $result = $this->validationService->testRules($testData, $rules);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
