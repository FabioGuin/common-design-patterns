<?php

namespace Tests\Feature;

use Tests\TestCase;

class AIRateLimitTest extends TestCase
{
    public function test_ai_rate_limit_returns_successful_response()
    {
        $response = $this->get('/api/ai-rate-limit/');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'AI Rate Limiting Pattern Demo'
        ]);
    }

    public function test_ai_rate_limit_test_endpoint()
    {
        $response = $this->get('/api/ai-rate-limit/test');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'AI Rate Limiting Test Completed'
        ]);
    }

    public function test_ai_rate_limit_query_endpoint()
    {
        $response = $this->post('/api/ai-rate-limit/query', [
            'query' => 'Test query for rate limiting'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Query processed with rate limiting'
        ]);
    }

    public function test_ai_rate_limit_query_with_validation()
    {
        $response = $this->post('/api/ai-rate-limit/query', [
            'query' => ''
        ]);
        
        $response->assertStatus(422);
    }

    public function test_ai_rate_limit_show_page()
    {
        $response = $this->get('/ai-rate-limit');
        
        $response->assertStatus(200);
        $response->assertViewIs('ai-rate-limit.example');
    }

    public function test_ai_rate_limit_test_page()
    {
        $response = $this->get('/ai-rate-limit/test');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'AI Rate Limiting Test Completed'
        ]);
    }
}
