<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Prompt\PromptTemplateService;

class PromptController extends Controller
{
    private PromptTemplateService $promptService;

    public function __construct(PromptTemplateService $promptService)
    {
        $this->promptService = $promptService;
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Prompt Engineering Pattern Demo',
            'data' => [
                'pattern_description' => 'Prompt Engineering ottimizza i prompt per migliori risultati AI',
                'available_types' => $this->promptService->getAvailableTypes()
            ]
        ]);
    }

    public function test()
    {
        $testCases = [
            ['type' => 'chat', 'variables' => ['question' => 'Come funziona Laravel?']],
            ['type' => 'code', 'variables' => ['language' => 'PHP', 'description' => 'una funzione per validare email']],
            ['type' => 'translation', 'variables' => ['from_language' => 'italiano', 'to_language' => 'inglese', 'text' => 'Ciao mondo']]
        ];

        $results = [];
        foreach ($testCases as $test) {
            $result = $this->promptService->generatePrompt($test['type'], $test['variables']);
            $results[] = [
                'test_case' => $test,
                'result' => $result
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Prompt Engineering Test Completed',
            'data' => [
                'test_results' => $results,
                'available_types' => $this->promptService->getAvailableTypes()
            ]
        ]);
    }

    public function generatePrompt(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'variables' => 'nullable|array'
        ]);

        $result = $this->promptService->generatePrompt(
            $request->input('type'),
            $request->input('variables', [])
        );

        $validation = $this->promptService->validatePrompt($result['prompt']);
        $optimization = $this->promptService->optimizePrompt($result['prompt']);

        return response()->json([
            'success' => true,
            'message' => 'Prompt generated successfully',
            'data' => [
                'generation' => $result,
                'validation' => $validation,
                'optimization' => $optimization
            ]
        ]);
    }

    public function show()
    {
        return view('prompt-engineering.example');
    }
}
