<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AIModel;

class AIModelAbstractionTest extends TestCase
{
    public function test_ai_model_abstraction_returns_successful_response()
    {
        $response = $this->get('/api/ai-model-abstraction/');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'AI Model Abstraction Pattern Demo'
        ]);
    }

    public function test_ai_model_abstraction_test_endpoint()
    {
        $response = $this->get('/api/ai-model-abstraction/test');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'AI Model Abstraction Test Completed'
        ]);
    }

    public function test_ai_model_abstraction_predict_endpoint()
    {
        $response = $this->post('/api/ai-model-abstraction/predict', [
            'input' => 'Test input for prediction',
            'model_type' => 'auto'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Prediction completed successfully'
        ]);
    }

    public function test_ai_model_abstraction_predict_with_validation()
    {
        $response = $this->post('/api/ai-model-abstraction/predict', [
            'input' => '',
            'model_type' => 'auto'
        ]);
        
        $response->assertStatus(422);
    }

    public function test_ai_model_abstraction_show_page()
    {
        $response = $this->get('/ai-model-abstraction');
        
        $response->assertStatus(200);
        $response->assertViewIs('ai-model-abstraction.example');
    }

    public function test_ai_model_abstraction_test_page()
    {
        $response = $this->get('/ai-model-abstraction/test');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'AI Model Abstraction Test Completed'
        ]);
    }
}
