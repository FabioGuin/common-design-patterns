<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AI\AIGatewayService;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Providers\ClaudeProvider;
use App\Services\AI\Providers\GeminiProvider;

class AIGatewayTest extends TestCase
{
    public function test_ai_gateway_returns_successful_response()
    {
        $gateway = new AIGatewayService();
        $result = $gateway->chat('Ciao');
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('response', $result);
        $this->assertArrayHasKey('provider', $result);
        $this->assertArrayHasKey('tokens_used', $result);
        $this->assertArrayHasKey('cost', $result);
    }

    public function test_ai_gateway_fallback_works()
    {
        $gateway = new AIGatewayService();
        $result = $gateway->chat('Test message');
        
        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['response']);
    }

    public function test_ai_gateway_returns_available_providers()
    {
        $gateway = new AIGatewayService();
        $providers = $gateway->getAvailableProviders();
        
        $this->assertIsArray($providers);
        $this->assertArrayHasKey('openai', $providers);
        $this->assertArrayHasKey('claude', $providers);
        $this->assertArrayHasKey('gemini', $providers);
    }

    public function test_ai_gateway_returns_provider_stats()
    {
        $gateway = new AIGatewayService();
        $stats = $gateway->getProviderStats();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('openai', $stats);
        $this->assertArrayHasKey('claude', $stats);
        $this->assertArrayHasKey('gemini', $stats);
        
        foreach ($stats as $provider) {
            $this->assertArrayHasKey('name', $provider);
            $this->assertArrayHasKey('available', $provider);
            $this->assertArrayHasKey('cost_per_token', $provider);
        }
    }

    public function test_openai_provider_works()
    {
        $provider = new OpenAIProvider();
        $result = $provider->chat('Ciao');
        
        $this->assertTrue($result['success']);
        $this->assertEquals('openai', $result['provider']);
        $this->assertNotEmpty($result['response']);
    }

    public function test_claude_provider_works()
    {
        $provider = new ClaudeProvider();
        $result = $provider->chat('Ciao');
        
        $this->assertTrue($result['success']);
        $this->assertEquals('claude', $result['provider']);
        $this->assertNotEmpty($result['response']);
    }

    public function test_gemini_provider_works()
    {
        $provider = new GeminiProvider();
        $result = $provider->chat('Ciao');
        
        $this->assertTrue($result['success']);
        $this->assertEquals('gemini', $result['provider']);
        $this->assertNotEmpty($result['response']);
    }

    public function test_providers_implement_interface()
    {
        $openai = new OpenAIProvider();
        $claude = new ClaudeProvider();
        $gemini = new GeminiProvider();
        
        $this->assertInstanceOf(\App\Services\AI\Providers\AIProviderInterface::class, $openai);
        $this->assertInstanceOf(\App\Services\AI\Providers\AIProviderInterface::class, $claude);
        $this->assertInstanceOf(\App\Services\AI\Providers\AIProviderInterface::class, $gemini);
    }

    public function test_providers_have_correct_names()
    {
        $openai = new OpenAIProvider();
        $claude = new ClaudeProvider();
        $gemini = new GeminiProvider();
        
        $this->assertEquals('OpenAI', $openai->getName());
        $this->assertEquals('Claude', $claude->getName());
        $this->assertEquals('Gemini', $gemini->getName());
    }

    public function test_providers_have_cost_per_token()
    {
        $openai = new OpenAIProvider();
        $claude = new ClaudeProvider();
        $gemini = new GeminiProvider();
        
        $this->assertIsFloat($openai->getCostPerToken());
        $this->assertIsFloat($claude->getCostPerToken());
        $this->assertIsFloat($gemini->getCostPerToken());
        
        $this->assertGreaterThan(0, $openai->getCostPerToken());
        $this->assertGreaterThan(0, $claude->getCostPerToken());
        $this->assertGreaterThan(0, $gemini->getCostPerToken());
    }
}
