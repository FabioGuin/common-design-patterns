<?php

namespace App\Http\Controllers;

use App\Services\ExpressionParser;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpressionController extends Controller
{
    private ExpressionParser $parser;
    
    public function __construct()
    {
        $this->parser = new ExpressionParser();
    }
    
    /**
     * Mostra la pagina principale con esempi di utilizzo dell'Interpreter Pattern
     */
    public function index(): View
    {
        $examples = [
            'math_expressions' => $this->getMathExpressionsExample(),
            'query_language' => $this->getQueryLanguageExample(),
            'configuration' => $this->getConfigurationExample(),
            'validation' => $this->getValidationExample()
        ];
        
        return view('expressions.index', compact('examples'));
    }
    
    /**
     * Valuta un'espressione matematica
     */
    public function evaluateMath(Request $request)
    {
        try {
            $expression = $request->get('expression', '');
            $variables = $request->get('variables', []);
            
            $parsedExpression = $this->parser->parse($expression);
            $result = $parsedExpression->interpret($variables);
            
            return response()->json([
                'success' => true,
                'result' => $result,
                'expression' => $expression,
                'variables' => $variables
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Valuta un'espressione di query
     */
    public function evaluateQuery(Request $request)
    {
        try {
            $query = $request->get('query', '');
            $context = $request->get('context', []);
            
            // Simula parsing di query SQL-like
            $result = $this->parseQuery($query, $context);
            
            return response()->json([
                'success' => true,
                'result' => $result,
                'query' => $query,
                'context' => $context
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Valuta un'espressione di configurazione
     */
    public function evaluateConfig(Request $request)
    {
        try {
            $config = $request->get('config', '');
            $environment = $request->get('environment', []);
            
            $result = $this->parseConfig($config, $environment);
            
            return response()->json([
                'success' => true,
                'result' => $result,
                'config' => $config,
                'environment' => $environment
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Valida un'espressione
     */
    public function validateExpression(Request $request)
    {
        try {
            $expression = $request->get('expression', '');
            
            $parsedExpression = $this->parser->parse($expression);
            $isValid = $this->isValidExpression($parsedExpression);
            
            return response()->json([
                'success' => true,
                'valid' => $isValid,
                'expression' => $expression
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Simula parsing di query SQL-like
     */
    private function parseQuery(string $query, array $context): array
    {
        // Implementazione semplificata per demo
        $query = strtolower(trim($query));
        
        if (strpos($query, 'select') === 0) {
            return $this->parseSelectQuery($query, $context);
        }
        
        if (strpos($query, 'where') !== false) {
            return $this->parseWhereQuery($query, $context);
        }
        
        return ['message' => 'Query not supported'];
    }
    
    private function parseSelectQuery(string $query, array $context): array
    {
        return [
            'type' => 'select',
            'fields' => $this->extractFields($query),
            'table' => $this->extractTable($query),
            'results' => $context['data'] ?? []
        ];
    }
    
    private function parseWhereQuery(string $query, array $context): array
    {
        return [
            'type' => 'where',
            'condition' => $this->extractCondition($query),
            'filtered_results' => $this->applyFilter($context['data'] ?? [], $query)
        ];
    }
    
    private function extractFields(string $query): array
    {
        preg_match('/select\s+(.+?)\s+from/i', $query, $matches);
        return $matches[1] ? explode(',', $matches[1]) : ['*'];
    }
    
    private function extractTable(string $query): string
    {
        preg_match('/from\s+(\w+)/i', $query, $matches);
        return $matches[1] ?? 'unknown';
    }
    
    private function extractCondition(string $query): string
    {
        preg_match('/where\s+(.+)/i', $query, $matches);
        return $matches[1] ?? '';
    }
    
    private function applyFilter(array $data, string $query): array
    {
        // Implementazione semplificata
        return array_filter($data, function($item) use ($query) {
            return strpos(json_encode($item), 'test') !== false;
        });
    }
    
    /**
     * Simula parsing di configurazione
     */
    private function parseConfig(string $config, array $environment): array
    {
        $lines = explode("\n", $config);
        $result = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Sostituisci variabili d'ambiente
                $value = $this->replaceEnvironmentVariables($value, $environment);
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    private function replaceEnvironmentVariables(string $value, array $environment): string
    {
        return preg_replace_callback('/\$\{(\w+)\}/', function($matches) use ($environment) {
            return $environment[$matches[1]] ?? $matches[0];
        }, $value);
    }
    
    /**
     * Valida un'espressione
     */
    private function isValidExpression($expression): bool
    {
        // Implementazione semplificata
        return $expression !== null;
    }
    
    /**
     * Ottiene esempi per la pagina principale
     */
    private function getMathExpressionsExample(): array
    {
        return [
            'title' => 'Mathematical Expressions',
            'description' => 'Valutazione di espressioni matematiche con variabili',
            'features' => [
                'Operazioni aritmetiche',
                'Variabili dinamiche',
                'Parentesi e precedenza',
                'Validazione sintassi'
            ]
        ];
    }
    
    private function getQueryLanguageExample(): array
    {
        return [
            'title' => 'Query Language',
            'description' => 'Interprete per linguaggi di query personalizzati',
            'features' => [
                'SQL-like syntax',
                'Filtri dinamici',
                'Join e aggregazioni',
                'Ottimizzazione query'
            ]
        ];
    }
    
    private function getConfigurationExample(): array
    {
        return [
            'title' => 'Configuration Parser',
            'description' => 'Parser per file di configurazione con variabili',
            'features' => [
                'Variabili d\'ambiente',
                'Configurazioni condizionali',
                'Validazione parametri',
                'Hot reload'
            ]
        ];
    }
    
    private function getValidationExample(): array
    {
        return [
            'title' => 'Expression Validation',
            'description' => 'Validazione e controllo sintassi delle espressioni',
            'features' => [
                'Controllo sintassi',
                'Validazione tipi',
                'Error reporting',
                'Suggerimenti correzione'
            ]
        ];
    }
}
