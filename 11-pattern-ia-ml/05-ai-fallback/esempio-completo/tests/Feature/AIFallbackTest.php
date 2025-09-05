<?php

namespace Tests\Feature;

use Tests\TestCase;

class AIFallbackTest extends TestCase
{
    public function test_ai_fallback_returns_successful_response()
    {
        $response = $this->get('/api/ai-fallback/');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'AI Fallback Pattern Demo'
        ]);
    }

    public function test_ai_fallback_test_endpoint()
    {
        $response = $this->get('/api/ai-fallback/test');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'AI Fallback Test Completed'
        ]);
    }

    public function test_ai_fallback_query_endpoint()
    {
        $response = $this->post('/api/ai-fallback/query', [
            'query' => 'Test query for fallback'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Query processed with fallback'
        ]);
    }

    public function test_ai_fallback_query_with_validation()
    {
        $response = $this->post('/api/ai-fallback/query', [
            'query' => ''
        ]);
        
        $response->assertStatus(422);
    }

    public function test_ai_fallback_show_page()
    {
        $response = $this->get('/ai-fallback');
        
        $response->assertStatus(200);
        $response->assertViewIs('ai-fallback.example');
    }

    public function test_ai_fallback_test_page()
    {
        $response = $this->get('/ai-fallback/test');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'AI Fallback Test Completed'
        ]);
    }
}
