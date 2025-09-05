<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;

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
    return view('home');
})->name('home');

// Blog routes (simulated)
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', function () {
        $posts = collect([
            (object)[
                'id' => 1,
                'title' => 'Introduzione a Blade Templates',
                'excerpt' => 'Blade è il motore di templating di Laravel che fornisce una sintassi elegante...',
                'user' => (object)['name' => 'John Doe', 'initials' => 'JD'],
                'created_at' => now()->subDays(1),
                'views_count' => 150,
                'likes_count' => 25,
                'comments_count' => 8,
                'status' => 'published',
                'featured_image' => null,
                'tags' => collect([
                    (object)['name' => 'Laravel'],
                    (object)['name' => 'Blade'],
                ]),
            ],
            (object)[
                'id' => 2,
                'title' => 'Componenti Riutilizzabili in Laravel',
                'excerpt' => 'I componenti sono un modo potente per creare elementi UI riutilizzabili...',
                'user' => (object)['name' => 'Jane Smith', 'initials' => 'JS'],
                'created_at' => now()->subDays(2),
                'views_count' => 200,
                'likes_count' => 35,
                'comments_count' => 12,
                'status' => 'published',
                'featured_image' => null,
                'tags' => collect([
                    (object)['name' => 'Components'],
                    (object)['name' => 'UI'],
                ]),
            ],
        ]);
        
        return view('blog.index', compact('posts'));
    })->name('index');
    
    Route::get('/{id}', function ($id) {
        $post = (object)[
            'id' => $id,
            'title' => 'Post ' . $id . ' - Blade Templates',
            'content' => 'Questo è il contenuto completo del post ' . $id . '. Blade templates sono molto utili...',
            'excerpt' => 'Questo è il contenuto completo del post ' . $id . '.',
            'user' => (object)['name' => 'John Doe', 'initials' => 'JD'],
            'created_at' => now()->subDays($id),
            'views_count' => 150 + $id * 10,
            'likes_count' => 25 + $id * 5,
            'comments_count' => 8 + $id * 2,
            'status' => 'published',
            'featured_image' => null,
            'tags' => collect([
                (object)['name' => 'Laravel'],
                (object)['name' => 'Blade'],
            ]),
        ];
        
        return view('blog.show', compact('post'));
    })->name('show');
});

// Users routes (simulated)
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', function () {
        $users = collect([
            (object)['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'initials' => 'JD'],
            (object)['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'initials' => 'JS'],
            (object)['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'initials' => 'BJ'],
        ]);
        
        return view('users.index', compact('users'));
    })->name('index');
    
    Route::get('/{id}', function ($id) {
        $user = (object)[
            'id' => $id,
            'name' => 'User ' . $id,
            'email' => 'user' . $id . '@example.com',
            'initials' => 'U' . $id,
        ];
        
        return view('users.show', compact('user'));
    })->name('show');
    
    Route::get('/profile', function () {
        return view('users.profile');
    })->name('profile');
});

// Blade demo routes
Route::prefix('blade-demo')->name('blade.')->group(function () {
    Route::get('/', function () {
        return view('blade-demo');
    })->name('demo');
    
    Route::get('/test', function () {
        return view('blade-test');
    })->name('test');
    
    // Test components
    Route::get('/components', function () {
        $components = [
            'form-input' => 'Form input component',
            'form-select' => 'Form select component',
            'card' => 'Card component',
            'modal' => 'Modal component',
            'post-card' => 'Post card component',
            'user-avatar' => 'User avatar component',
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Components loaded successfully',
            'data' => $components,
        ]);
    })->name('components');
    
    // Test directives
    Route::get('/directives', function () {
        $directives = [
            'role' => 'Role-based content display',
            'admin' => 'Admin-only content',
            'guest' => 'Guest-only content',
            'datetime' => 'Date formatting',
            'currency' => 'Currency formatting',
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Directives loaded successfully',
            'data' => $directives,
        ]);
    })->name('directives');
});

// Dashboard
Route::get('/dashboard', function () {
    $stats = [
        'total_posts' => 25,
        'total_users' => 150,
        'total_views' => 5000,
        'total_likes' => 800,
    ];
    
    return view('dashboard', compact('stats'));
})->name('dashboard');

// Auth routes (simulated)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/logout', function () {
    return redirect()->route('home');
})->name('logout');

// Register custom Blade directives
Blade::directive('role', function ($expression) {
    return "<?php if(auth()->check() && auth()->user()->hasRole($expression)): ?>";
});

Blade::directive('endrole', function () {
    return "<?php endif; ?>";
});

Blade::directive('admin', function () {
    return "<?php if(auth()->check() && auth()->user()->isAdmin()): ?>";
});

Blade::directive('endadmin', function () {
    return "<?php endif; ?>";
});

Blade::directive('guest', function () {
    return "<?php if(!auth()->check()): ?>";
});

Blade::directive('endguest', function () {
    return "<?php endif; ?>";
});

Blade::directive('datetime', function ($expression) {
    return "<?php echo ($expression)->format('M d, Y H:i'); ?>";
});

Blade::directive('currency', function ($expression) {
    return "<?php echo number_format($expression, 2) . ' €'; ?>";
});

// Register Blade if statements
Blade::if('admin', function () {
    return auth()->check() && auth()->user()->isAdmin();
});

Blade::if('guest', function () {
    return !auth()->check();
});
