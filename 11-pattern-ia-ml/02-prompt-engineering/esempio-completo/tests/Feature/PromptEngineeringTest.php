<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Prompt\PromptTemplateService;

class PromptEngineeringTest extends TestCase
{
    public function test_prompt_engineering_returns_successful_response()
    {
        $response = $this->get('/api/prompt-engineering/');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Prompt Engineering Pattern Demo'
        ]);
    }

    public function test_prompt_engineering_test_endpoint()
    {
        $response = $this->get('/api/prompt-engineering/test');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Prompt Engineering Test Completed'
        ]);
    }

    public function test_prompt_engineering_generate_endpoint()
    {
        $response = $this->post('/api/prompt-engineering/generate', [
            'type' => 'chat',
            'variables' => ['question' => 'Come funziona Laravel?']
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Prompt generated successfully'
        ]);
    }

    public function test_prompt_engineering_generate_with_validation()
    {
        $response = $this->post('/api/prompt-engineering/generate', [
            'type' => '',
            'variables' => []
        ]);
        
        $response->assertStatus(422);
    }

    public function test_prompt_engineering_show_page()
    {
        $response = $this->get('/prompt-engineering');
        
        $response->assertStatus(200);
        $response->assertViewIs('prompt-engineering.example');
    }

    public function test_prompt_engineering_test_page()
    {
        $response = $this->get('/prompt-engineering/test');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Prompt Engineering Test Completed'
        ]);
    }

    public function test_prompt_template_service_generate_prompt()
    {
        $service = new PromptTemplateService();
        $result = $service->generatePrompt('chat', ['question' => 'Test question']);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('prompt', $result);
        $this->assertEquals('chat', $result['type']);
    }

    public function test_prompt_template_service_validate_prompt()
    {
        $service = new PromptTemplateService();
        $result = $service->validatePrompt('This is a valid prompt with enough words');
        
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function test_prompt_template_service_optimize_prompt()
    {
        $service = new PromptTemplateService();
        $result = $service->optimizePrompt('  This   is   a   prompt   with   extra   spaces  ');
        
        $this->assertArrayHasKey('original', $result);
        $this->assertArrayHasKey('optimized', $result);
        $this->assertNotEquals($result['original'], $result['optimized']);
    }

    public function test_prompt_template_service_get_available_types()
    {
        $service = new PromptTemplateService();
        $types = $service->getAvailableTypes();
        
        $this->assertIsArray($types);
        $this->assertArrayHasKey('chat', $types);
        $this->assertArrayHasKey('code', $types);
        $this->assertArrayHasKey('translation', $types);
    }
}
