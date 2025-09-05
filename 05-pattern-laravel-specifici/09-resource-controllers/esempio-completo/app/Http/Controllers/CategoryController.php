<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $categories = $this->categoryService->getCategories($request->all());
            
            Log::info('Categories list retrieved', [
                'count' => $categories->count(),
                'filters' => $request->all()
            ]);

            return view('resource-controllers.categories.index', compact('categories'));
        } catch (\Exception $e) {
            Log::error('Error retrieving categories list', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);

            return redirect()->back()->with('error', 'Errore nel recupero delle categorie.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('resource-controllers.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        try {
            $categoryData = $request->validated();
            $category = $this->categoryService->createCategory($categoryData);

            Log::info('Category created successfully', [
                'category_id' => $category->id,
                'name' => $category->name
            ]);

            return redirect()->route('categories.show', $category)
                ->with('success', 'Categoria creata con successo!');
        } catch (\Exception $e) {
            Log::error('Error creating category', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Errore nella creazione della categoria: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        try {
            $category->load(['posts']);
            
            Log::info('Category retrieved', [
                'category_id' => $category->id,
                'name' => $category->name
            ]);

            return view('resource-controllers.categories.show', compact('category'));
        } catch (\Exception $e) {
            Log::error('Error retrieving category', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('categories.index')
                ->with('error', 'Errore nel recupero della categoria.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('resource-controllers.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        try {
            $categoryData = $request->validated();
            $this->categoryService->updateCategory($category, $categoryData);

            Log::info('Category updated successfully', [
                'category_id' => $category->id,
                'name' => $category->name
            ]);

            return redirect()->route('categories.show', $category)
                ->with('success', 'Categoria aggiornata con successo!');
        } catch (\Exception $e) {
            Log::error('Error updating category', [
                'category_id' => $category->id,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Errore nell\'aggiornamento della categoria: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            $this->categoryService->deleteCategory($category);

            Log::info('Category deleted successfully', [
                'category_id' => $category->id,
                'name' => $category->name
            ]);

            return redirect()->route('categories.index')
                ->with('success', 'Categoria eliminata con successo!');
        } catch (\Exception $e) {
            Log::error('Error deleting category', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Errore nell\'eliminazione della categoria: ' . $e->getMessage());
        }
    }

    /**
     * Get posts for a specific category
     */
    public function posts(Category $category)
    {
        try {
            $posts = $this->categoryService->getPostsForCategory($category);
            
            Log::info('Posts for category retrieved', [
                'category_id' => $category->id,
                'count' => $posts->count()
            ]);

            return view('resource-controllers.categories.posts', compact('category', 'posts'));
        } catch (\Exception $e) {
            Log::error('Error retrieving posts for category', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('categories.show', $category)
                ->with('error', 'Errore nel recupero dei post per la categoria.');
        }
    }

    /**
     * Search categories
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q');
            $categories = $this->categoryService->searchCategories($query);

            Log::info('Categories searched', [
                'query' => $query,
                'count' => $categories->count()
            ]);

            return view('resource-controllers.categories.index', compact('categories', 'query'));
        } catch (\Exception $e) {
            Log::error('Error searching categories', [
                'query' => $request->get('q'),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('categories.index')
                ->with('error', 'Errore nella ricerca delle categorie.');
        }
    }

    /**
     * Archive category
     */
    public function archive(Category $category)
    {
        try {
            $this->categoryService->archiveCategory($category);

            Log::info('Category archived', [
                'category_id' => $category->id
            ]);

            return redirect()->route('categories.index')
                ->with('success', 'Categoria archiviata con successo!');
        } catch (\Exception $e) {
            Log::error('Error archiving category', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Errore nell\'archiviazione della categoria: ' . $e->getMessage());
        }
    }

    /**
     * Restore archived category
     */
    public function restore(Category $category)
    {
        try {
            $this->categoryService->restoreCategory($category);

            Log::info('Category restored', [
                'category_id' => $category->id
            ]);

            return redirect()->route('categories.index')
                ->with('success', 'Categoria ripristinata con successo!');
        } catch (\Exception $e) {
            Log::error('Error restoring category', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Errore nel ripristino della categoria: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint for categories
     */
    public function apiIndex(Request $request)
    {
        try {
            $categories = $this->categoryService->getCategories($request->all());
            
            return CategoryResource::collection($categories);
        } catch (\Exception $e) {
            Log::error('Error in API categories index', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Errore nel recupero delle categorie'
            ], 500);
        }
    }

    /**
     * API endpoint for single category
     */
    public function apiShow(Category $category)
    {
        try {
            $category->load(['posts']);
            
            return new CategoryResource($category);
        } catch (\Exception $e) {
            Log::error('Error in API category show', [
                'category_id' => $category->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Errore nel recupero della categoria'
            ], 500);
        }
    }
}
