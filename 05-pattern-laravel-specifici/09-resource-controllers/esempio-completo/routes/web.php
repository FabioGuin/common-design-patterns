<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/resource-controllers');
});

// Main demo page
Route::get('/resource-controllers', function () {
    $posts = \App\Models\Post::with(['category', 'user'])->latest()->take(5)->get();
    $categories = \App\Models\Category::active()->get();
    $comments = \App\Models\Comment::with(['post', 'user'])->latest()->take(5)->get();
    
    return view('resource-controllers.demo', compact('posts', 'categories', 'comments'));
})->name('resource-controllers.demo');

// Resource routes for Posts
Route::resource('posts', PostController::class);
Route::get('/posts/{post}/archive', [PostController::class, 'archive'])->name('posts.archive');
Route::get('/posts/{post}/restore', [PostController::class, 'restore'])->name('posts.restore');
Route::get('/posts/search', [PostController::class, 'search'])->name('posts.search');

// Resource routes for Comments
Route::resource('comments', CommentController::class);
Route::get('/posts/{post}/comments', [CommentController::class, 'forPost'])->name('posts.comments');
Route::get('/comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
Route::get('/comments/{comment}/reject', [CommentController::class, 'reject'])->name('comments.reject');

// Resource routes for Categories
Route::resource('categories', CategoryController::class);
Route::get('/categories/{category}/posts', [CategoryController::class, 'posts'])->name('categories.posts');
Route::get('/categories/{category}/archive', [CategoryController::class, 'archive'])->name('categories.archive');
Route::get('/categories/{category}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
Route::get('/categories/search', [CategoryController::class, 'search'])->name('categories.search');

// API routes
Route::prefix('api')->group(function () {
    Route::get('/posts', [PostController::class, 'apiIndex'])->name('api.posts.index');
    Route::get('/posts/{post}', [PostController::class, 'apiShow'])->name('api.posts.show');
    
    Route::get('/comments', [CommentController::class, 'apiIndex'])->name('api.comments.index');
    Route::get('/comments/{comment}', [CommentController::class, 'apiShow'])->name('api.comments.show');
    
    Route::get('/categories', [CategoryController::class, 'apiIndex'])->name('api.categories.index');
    Route::get('/categories/{category}', [CategoryController::class, 'apiShow'])->name('api.categories.show');
});
