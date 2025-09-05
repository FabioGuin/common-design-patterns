<?php

namespace Tests\Feature;

use Tests\TestCase;

class AIResponseCachingTest extends TestCase
{
    public function test_ai_response_caching_returns_successful_response()
    {
        $response = $this->get('/api/ai-response-caching/');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'AI Response Caching Pattern Demo'
        ]);
    }

    public function test_ai_response_caching_test_endpoint()
    {
        $response = $this->get('/api/ai-response-caching/test');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'AI Response Caching Test Completed'
        ]);
    }

    public function test_ai_response_caching_query_endpoint()
    {
        $response = $this->post('/api/ai-response-caching/query', [
            'query' => 'Test query for caching'
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Query processed successfully'
        ]);
    }

    public function test_ai_response_caching_query_with_validation()
    {
        $response = $this->post('/api/ai-response-caching/query', [
            'query' => ''
        ]);
        
        $response->assertStatus(422);
    }

    public function test_ai_response_caching_show_page()
    {
        $response = $this->get('/ai-response-caching');
        
        $response->assertStatus(200);
        $response->assertViewIs('ai-response-caching.example');
    }

    public function test_ai_response_caching_test_page()
    {
        $response = $this->get('/ai-response-caching/test');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'AI Response Caching Test Completed'
        ]);
    }
}
