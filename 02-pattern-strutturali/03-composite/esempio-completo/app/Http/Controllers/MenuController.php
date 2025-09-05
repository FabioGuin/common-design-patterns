<?php

namespace App\Http\Controllers;

use App\Services\MenuComponentInterface;
use App\Services\MenuItem;
use App\Services\MenuCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    private MenuCategory $menu;

    public function __construct()
    {
        $this->menu = $this->createSampleMenu();
    }

    /**
     * Mostra la pagina principale del menu
     */
    public function index()
    {
        return view('menu.index', [
            'menu' => $this->menu,
            'menuData' => $this->menu->toArray(),
        ]);
    }

    /**
     * Aggiunge un elemento al menu
     */
    public function addItem(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'type' => 'required|string|in:item,category',
            'parent_name' => 'nullable|string|max:100',
        ]);

        try {
            $parent = $this->findParent($request->parent_name);
            
            if ($request->type === 'item') {
                $component = new MenuItem(
                    $request->name,
                    $request->price,
                    $request->description ?? ''
                );
            } else {
                $component = new MenuCategory(
                    $request->name,
                    $request->description ?? ''
                );
            }

            $parent->add($component);

            return response()->json([
                'success' => true,
                'message' => 'Elemento aggiunto con successo',
                'element' => $component->toArray(),
                'parent' => $parent->getName(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rimuove un elemento dal menu
     */
    public function removeItem(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'parent_name' => 'nullable|string|max:100',
        ]);

        try {
            $parent = $this->findParent($request->parent_name);
            $element = $parent->findByName($request->name);

            if (!$element) {
                throw new \Exception('Elemento non trovato');
            }

            $parent->remove($element);

            return response()->json([
                'success' => true,
                'message' => 'Elemento rimosso con successo',
                'element_name' => $element->getName(),
                'parent' => $parent->getName(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cerca un elemento nel menu
     */
    public function searchItem(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        try {
            $element = $this->menu->findByName($request->name);

            if (!$element) {
                return response()->json([
                    'success' => false,
                    'message' => 'Elemento non trovato',
                ]);
            }

            return response()->json([
                'success' => true,
                'element' => $element->toArray(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ottiene le statistiche del menu
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_items' => $this->menu->getTotalCount(),
                'total_price' => $this->menu->getTotalPrice(),
                'categories_count' => $this->countCategories($this->menu),
                'items_count' => $this->countItems($this->menu),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crea un menu di esempio
     */
    private function createSampleMenu(): MenuCategory
    {
        $menu = new MenuCategory('Menu Principale', 'Il nostro menu completo');

        // Antipasti
        $antipasti = new MenuCategory('Antipasti', 'Iniziamo con gusto');
        $antipasti->add(new MenuItem('Bruschetta al Pomodoro', 6.50, 'Pane tostato con pomodoro fresco e basilico'));
        $antipasti->add(new MenuItem('Antipasto della Casa', 12.00, 'Selezione di salumi e formaggi locali'));
        $menu->add($antipasti);

        // Primi Piatti
        $primi = new MenuCategory('Primi Piatti', 'Pasta e risotti della tradizione');
        $primi->add(new MenuItem('Spaghetti Carbonara', 11.00, 'Pasta con uova, pancetta e pecorino'));
        $primi->add(new MenuItem('Risotto ai Porcini', 13.50, 'Risotto cremoso con funghi porcini'));
        
        // Sottocategoria Pasta
        $pasta = new MenuCategory('Pasta Speciale', 'Le nostre specialità');
        $pasta->add(new MenuItem('Linguine alle Vongole', 14.00, 'Pasta con vongole veraci e prezzemolo'));
        $pasta->add(new MenuItem('Penne all\'Arrabbiata', 10.50, 'Pasta piccante con pomodoro e peperoncino'));
        $primi->add($pasta);
        
        $menu->add($primi);

        // Secondi Piatti
        $secondi = new MenuCategory('Secondi Piatti', 'Carni e pesci di qualità');
        $secondi->add(new MenuItem('Bistecca alla Fiorentina', 25.00, 'Tagliata di manzo alla griglia'));
        $secondi->add(new MenuItem('Branzino al Sale', 18.00, 'Pesce intero cotto nel sale grosso'));
        $menu->add($secondi);

        // Dolci
        $dolci = new MenuCategory('Dolci', 'Per concludere in dolcezza');
        $dolci->add(new MenuItem('Tiramisù', 6.00, 'Il classico dolce al caffè'));
        $dolci->add(new MenuItem('Panna Cotta ai Frutti di Bosco', 5.50, 'Crema vaniglia con salsa ai frutti di bosco'));
        $menu->add($dolci);

        return $menu;
    }

    /**
     * Trova il parent per aggiungere un elemento
     */
    private function findParent(?string $parentName): MenuComponentInterface
    {
        if ($parentName === null) {
            return $this->menu;
        }

        $parent = $this->menu->findByName($parentName);
        if (!$parent || !$parent->isCategory()) {
            throw new \Exception('Categoria parent non trovata o non valida');
        }

        return $parent;
    }

    /**
     * Conta le categorie nel menu
     */
    private function countCategories(MenuComponentInterface $component): int
    {
        $count = $component->isCategory() ? 1 : 0;
        
        foreach ($component->getChildren() as $child) {
            $count += $this->countCategories($child);
        }
        
        return $count;
    }

    /**
     * Conta le voci del menu
     */
    private function countItems(MenuComponentInterface $component): int
    {
        $count = $component instanceof MenuItem ? 1 : 0;
        
        foreach ($component->getChildren() as $child) {
            $count += $this->countItems($child);
        }
        
        return $count;
    }
}
