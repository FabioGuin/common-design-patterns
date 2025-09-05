<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\UIAbstractFactory;

class UIComponentController extends Controller
{
    /**
     * Endpoint principale per testare l'Abstract Factory
     */
    public function index(Request $request)
    {
        $supportedThemes = UIAbstractFactory::getSupportedThemes();
        
        return response()->json([
            'success' => true,
            'message' => 'Abstract Factory Pattern Demo',
            'data' => [
                'supported_themes' => $supportedThemes,
                'pattern_description' => 'Abstract Factory crea famiglie di oggetti correlati'
            ]
        ]);
    }

    /**
     * Endpoint di test per dimostrare la creazione di componenti
     */
    public function test()
    {
        $themes = UIAbstractFactory::getSupportedThemes();
        $components = [];
        
        foreach ($themes as $theme) {
            $factory = UIAbstractFactory::getFactory($theme);
            $themeComponents = [
                'theme' => $theme,
                'button' => $factory->createButton('Test Button')->toArray(),
                'card' => $factory->createCard('Test Card Content')->toArray(),
                'modal' => $factory->createModal('Test Modal Content')->toArray(),
            ];
            $components[] = $themeComponents;
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Abstract Factory Test Completed',
            'data' => [
                'themes_tested' => count($themes),
                'components' => $components,
                'pattern_benefits' => [
                    'Consistency' => 'Componenti coerenti per tema',
                    'Flexibility' => 'Facile cambio tema',
                    'Maintainability' => 'Codice più mantenibile',
                    'Extensibility' => 'Facile aggiungere nuovi temi'
                ]
            ]
        ]);
    }

    /**
     * Endpoint per creare componenti per un tema specifico
     */
    public function createComponents(Request $request)
    {
        $request->validate([
            'theme' => 'required|string|in:' . implode(',', UIAbstractFactory::getSupportedThemes()),
            'button_text' => 'required|string|max:255',
            'card_content' => 'required|string|max:500',
            'modal_content' => 'required|string|max:500'
        ]);

        try {
            $factory = UIAbstractFactory::getFactory($request->input('theme'));
            
            $components = [
                'theme' => $request->input('theme'),
                'button' => $factory->createButton($request->input('button_text'))->toArray(),
                'card' => $factory->createCard($request->input('card_content'))->toArray(),
                'modal' => $factory->createModal($request->input('modal_content'))->toArray(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Components created successfully',
                'data' => $components
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating components: ' . $e->getMessage(),
                'data' => null
            ], 400);
        }
    }

    /**
     * Endpoint per mostrare la vista di esempio
     */
    public function show()
    {
        $supportedThemes = UIAbstractFactory::getSupportedThemes();
        
        return view('abstract-factory.example', compact('supportedThemes'));
    }
}
