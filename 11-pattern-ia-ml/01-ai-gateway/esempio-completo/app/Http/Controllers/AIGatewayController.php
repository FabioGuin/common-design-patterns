<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AI\AIGatewayService;

class AIGatewayController extends Controller
{
    private AIGatewayService $aiGateway;

    public function __construct(AIGatewayService $aiGateway)
    {
        $this->aiGateway = $aiGateway;
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'AI Gateway Pattern Demo',
            'data' => [
                'pattern_description' => 'AI Gateway astrae le differenze tra provider AI',
                'available_providers' => $this->aiGateway->getAvailableProviders(),
                'provider_stats' => $this->aiGateway->getProviderStats()
            ]
        ]);
    }

    public function test()
    {
        $testPrompts = [
            'Ciao',
            'Come stai?',
            'Raccontami una barzelletta'
        ];

        $results = [];
        foreach ($testPrompts as $prompt) {
            $result = $this->aiGateway->chat($prompt);
            $results[] = [
                'prompt' => $prompt,
                'result' => $result
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'AI Gateway Test Completed',
            'data' => [
                'test_results' => $results,
                'available_providers' => $this->aiGateway->getAvailableProviders()
            ]
        ]);
    }

    public function chat(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'provider' => 'nullable|string|in:openai,claude,gemini'
        ]);

        $result = $this->aiGateway->chat(
            $request->input('prompt'),
            $request->input('provider')
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success'] ? 'Chat completed successfully' : 'Chat failed',
            'data' => $result
        ]);
    }

    public function show()
    {
        return view('ai-gateway.example');
    }
}
