<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ApiController;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Homepage
Route::get('/', function () {
    return redirect()->route('blog.index');
});

// Blog routes
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/post/{post}', [BlogController::class, 'show'])->name('show');
    
    // Protected routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/create', [BlogController::class, 'create'])->name('create');
        Route::post('/store', [BlogController::class, 'store'])->name('store');
        Route::get('/edit/{post}', [BlogController::class, 'edit'])->name('edit');
        Route::put('/update/{post}', [BlogController::class, 'update'])->name('update');
        Route::delete('/delete/{post}', [BlogController::class, 'destroy'])->name('destroy');
    });
});

// User routes
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::get('/{user}', [UserController::class, 'show'])->name('show');
});

// API routes
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/posts', [BlogController::class, 'apiPosts'])->name('posts');
    Route::get('/posts/{post}', [BlogController::class, 'apiPost'])->name('post');
    
    // Protected API routes
    Route::middleware(['auth'])->group(function () {
        Route::post('/posts', [BlogController::class, 'apiCreatePost'])->name('posts.create');
        Route::put('/posts/{post}', [BlogController::class, 'apiUpdatePost'])->name('posts.update');
        Route::delete('/posts/{post}', [BlogController::class, 'apiDeletePost'])->name('posts.delete');
    });
});

// Demo routes
Route::prefix('eloquent-demo')->name('eloquent.demo.')->group(function () {
    Route::get('/', function () {
        return view('eloquent-demo');
    })->name('index');
    
    Route::get('/test', function () {
        return view('eloquent-test');
    })->name('test');
    
    // Test basic queries
    Route::get('/basic-queries', function () {
        $users = User::with('posts')->limit(5)->get();
        $posts = Post::with(['user', 'category', 'tags'])->limit(5)->get();
        $categories = Category::withCount('posts')->get();
        $tags = Tag::withCount('posts')->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Basic queries executed successfully',
            'data' => [
                'users' => $users,
                'posts' => $posts,
                'categories' => $categories,
                'tags' => $tags,
            ],
        ]);
    })->name('basic-queries');
    
    // Test relationships
    Route::get('/relationships', function () {
        $user = User::with(['posts', 'comments', 'profile'])->first();
        $post = Post::with(['user', 'category', 'tags', 'comments.user'])->first();
        
        return response()->json([
            'success' => true,
            'message' => 'Relationships loaded successfully',
            'data' => [
                'user_with_relations' => $user,
                'post_with_relations' => $post,
            ],
        ]);
    })->name('relationships');
    
    // Test scopes
    Route::get('/scopes', function () {
        $activeUsers = User::active()->get();
        $publishedPosts = Post::published()->get();
        $draftPosts = Post::draft()->get();
        $popularPosts = Post::popular()->limit(5)->get();
        $recentPosts = Post::recent()->limit(5)->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Scopes executed successfully',
            'data' => [
                'active_users' => $activeUsers,
                'published_posts' => $publishedPosts,
                'draft_posts' => $draftPosts,
                'popular_posts' => $popularPosts,
                'recent_posts' => $recentPosts,
            ],
        ]);
    })->name('scopes');
    
    // Test complex queries
    Route::get('/complex-queries', function () {
        $postsWithComments = Post::withCount('comments')
            ->having('comments_count', '>', 0)
            ->get();
            
        $usersWithPosts = User::whereHas('posts', function ($query) {
            $query->where('status', 'published');
        })->withCount('posts')->get();
        
        $postsByCategory = Post::with('category')
            ->selectRaw('category_id, COUNT(*) as post_count')
            ->groupBy('category_id')
            ->get();
        
        $tagUsage = Tag::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Complex queries executed successfully',
            'data' => [
                'posts_with_comments' => $postsWithComments,
                'users_with_posts' => $usersWithPosts,
                'posts_by_category' => $postsByCategory,
                'tag_usage' => $tagUsage,
            ],
        ]);
    })->name('complex-queries');
    
    // Test performance
    Route::get('/performance', function () {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        // Test N+1 problem (bad)
        $posts = Post::all();
        foreach ($posts as $post) {
            $post->user->name; // This will cause N+1 queries
        }
        
        $n1Time = microtime(true) - $startTime;
        $n1Memory = memory_get_usage() - $startMemory;
        
        // Reset
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        // Test eager loading (good)
        $posts = Post::with('user')->get();
        foreach ($posts as $post) {
            $post->user->name; // This will not cause N+1 queries
        }
        
        $eagerTime = microtime(true) - $startTime;
        $eagerMemory = memory_get_usage() - $startMemory;
        
        return response()->json([
            'success' => true,
            'message' => 'Performance test completed',
            'data' => [
                'n1_queries' => [
                    'time_ms' => round($n1Time * 1000, 2),
                    'memory_mb' => round($n1Memory / 1024 / 1024, 2),
                ],
                'eager_loading' => [
                    'time_ms' => round($eagerTime * 1000, 2),
                    'memory_mb' => round($eagerMemory / 1024 / 1024, 2),
                ],
                'improvement' => [
                    'time_improvement' => round((($n1Time - $eagerTime) / $n1Time) * 100, 2) . '%',
                    'memory_improvement' => round((($n1Memory - $eagerMemory) / $n1Memory) * 100, 2) . '%',
                ],
            ],
        ]);
    })->name('performance');
    
    // Test CRUD operations
    Route::get('/crud-test', function () {
        try {
            // Create
            $user = User::create([
                'name' => 'Test User ' . rand(1000, 9999),
                'email' => 'test' . rand(1000, 9999) . '@example.com',
                'password' => bcrypt('password'),
            ]);
            
            $post = Post::create([
                'title' => 'Test Post ' . rand(1000, 9999),
                'content' => 'This is a test post content.',
                'user_id' => $user->id,
                'status' => 'published',
                'published_at' => now(),
            ]);
            
            // Read
            $foundUser = User::find($user->id);
            $foundPost = Post::find($post->id);
            
            // Update
            $user->update(['name' => 'Updated Test User']);
            $post->update(['title' => 'Updated Test Post']);
            
            // Delete
            $post->delete();
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'CRUD operations completed successfully',
                'data' => [
                    'created_user_id' => $user->id,
                    'created_post_id' => $post->id,
                    'found_user' => $foundUser,
                    'found_post' => $foundPost,
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'CRUD operations failed: ' . $e->getMessage(),
            ], 500);
        }
    })->name('crud-test');
});

// Statistics routes
Route::get('/stats', function () {
    $stats = [
        'users' => [
            'total' => User::count(),
            'active' => User::active()->count(),
            'verified' => User::verified()->count(),
        ],
        'posts' => [
            'total' => Post::count(),
            'published' => Post::published()->count(),
            'draft' => Post::draft()->count(),
            'total_views' => Post::sum('views_count'),
            'total_likes' => Post::sum('likes_count'),
        ],
        'categories' => Category::count(),
        'tags' => Tag::count(),
    ];
    
    return response()->json([
        'success' => true,
        'message' => 'Statistics retrieved successfully',
        'data' => $stats,
    ]);
})->name('stats');

// Dashboard
Route::get('/dashboard', function () {
    $stats = [
        'users' => User::count(),
        'posts' => Post::count(),
        'published_posts' => Post::published()->count(),
        'categories' => Category::count(),
        'tags' => Tag::count(),
    ];
    
    $recentPosts = Post::with('user')->latest()->limit(5)->get();
    $popularPosts = Post::published()->popular()->limit(5)->get();
    $activeUsers = User::active()->withCount('posts')->orderBy('posts_count', 'desc')->limit(5)->get();
    
    return view('dashboard', compact('stats', 'recentPosts', 'popularPosts', 'activeUsers'));
})->name('dashboard');
