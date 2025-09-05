<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AIModel;

class AIModelController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'AI Model Abstraction Pattern Demo',
            'data' => [
                'pattern_description' => 'AI Model Abstraction astrae le differenze tra modelli AI',
                'available_models' => $this->getAvailableModels()
            ]
        ]);
    }

    public function test()
    {
        $models = $this->getAvailableModels();
        $testResults = [];

        foreach ($models as $model) {
            $testResults[] = [
                'model' => $model,
                'prediction' => $this->simulatePrediction($model),
                'performance' => $this->simulatePerformance($model)
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'AI Model Abstraction Test Completed',
            'data' => [
                'test_results' => $testResults,
                'available_models' => $models
            ]
        ]);
    }

    public function predict(Request $request)
    {
        $request->validate([
            'input' => 'required|string',
            'model_type' => 'nullable|string'
        ]);

        $input = $request->input('input');
        $modelType = $request->input('model_type', 'auto');
        
        $selectedModel = $this->selectBestModel($modelType);
        $prediction = $this->simulatePrediction($selectedModel, $input);

        return response()->json([
            'success' => true,
            'message' => 'Prediction completed successfully',
            'data' => [
                'input' => $input,
                'selected_model' => $selectedModel,
                'prediction' => $prediction,
                'confidence' => $this->simulateConfidence($selectedModel)
            ]
        ]);
    }

    public function show()
    {
        return view('ai-model-abstraction.example');
    }

    private function getAvailableModels(): array
    {
        return [
            [
                'name' => 'GPT-4',
                'type' => 'text',
                'provider' => 'OpenAI',
                'performance_score' => 9.5,
                'cost_per_token' => 0.0001,
                'max_tokens' => 8192,
                'is_available' => true
            ],
            [
                'name' => 'Claude-3',
                'type' => 'text',
                'provider' => 'Anthropic',
                'performance_score' => 9.2,
                'cost_per_token' => 0.00015,
                'max_tokens' => 100000,
                'is_available' => true
            ],
            [
                'name' => 'Gemini-Pro',
                'type' => 'text',
                'provider' => 'Google',
                'performance_score' => 8.8,
                'cost_per_token' => 0.00008,
                'max_tokens' => 30720,
                'is_available' => true
            ]
        ];
    }

    private function selectBestModel(string $type): array
    {
        $models = $this->getAvailableModels();
        
        if ($type === 'auto') {
            // Seleziona il modello con il miglior score di performance
            return collect($models)->sortByDesc('performance_score')->first();
        }
        
        return collect($models)->firstWhere('type', $type) ?? $models[0];
    }

    private function simulatePrediction(array $model, string $input = 'test input'): array
    {
        return [
            'result' => "Predizione da {$model['name']}: " . substr($input, 0, 50) . "...",
            'tokens_used' => strlen($input) + 50,
            'response_time' => rand(100, 500),
            'cost' => (strlen($input) + 50) * $model['cost_per_token']
        ];
    }

    private function simulatePerformance(array $model): array
    {
        return [
            'accuracy' => $model['performance_score'] / 10,
            'speed' => rand(80, 100),
            'reliability' => rand(85, 100)
        ];
    }

    private function simulateConfidence(array $model): float
    {
        return ($model['performance_score'] / 10) + (rand(0, 20) / 100);
    }
}
