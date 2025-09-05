<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/policy');
});

// Main demo page
Route::get('/policy', function () {
    $posts = \App\Models\Post::with(['user', 'category'])->latest()->take(5)->get();
    $users = \App\Models\User::withCount(['posts', 'comments'])->take(5)->get();
    $comments = \App\Models\Comment::with(['post', 'user'])->latest()->take(5)->get();
    
    return view('policy.demo', compact('posts', 'users', 'comments'));
})->name('policy.demo');

// Resource routes for Posts with authorization
Route::resource('posts', PostController::class);
Route::get('/posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
Route::get('/posts/{post}/archive', [PostController::class, 'archive'])->name('posts.archive');
Route::get('/posts/{post}/comments', [PostController::class, 'comments'])->name('posts.comments');
Route::get('/posts/{post}/moderate', [PostController::class, 'moderate'])->name('posts.moderate');

// Resource routes for Comments with authorization
Route::resource('comments', CommentController::class);
Route::get('/comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
Route::get('/comments/{comment}/reject', [CommentController::class, 'reject'])->name('comments.reject');
Route::get('/comments/{comment}/reply', [CommentController::class, 'reply'])->name('comments.reply');
Route::get('/comments/{comment}/flag', [CommentController::class, 'flag'])->name('comments.flag');

// Resource routes for Users with authorization
Route::resource('users', UserController::class);
Route::get('/users/{user}/posts', [UserController::class, 'posts'])->name('users.posts');
Route::get('/users/{user}/comments', [UserController::class, 'comments'])->name('users.comments');
Route::get('/users/{user}/ban', [UserController::class, 'ban'])->name('users.ban');
Route::get('/users/{user}/unban', [UserController::class, 'unban'])->name('users.unban');
Route::get('/users/{user}/change-role', [UserController::class, 'changeRole'])->name('users.change-role');
Route::post('/users/{user}/change-role', [UserController::class, 'updateRole'])->name('users.update-role');

// API routes with authorization
Route::prefix('api')->group(function () {
    Route::get('/posts', [PostController::class, 'apiIndex'])->name('api.posts.index');
    Route::get('/posts/{post}', [PostController::class, 'apiShow'])->name('api.posts.show');
    
    Route::get('/comments', [CommentController::class, 'apiIndex'])->name('api.comments.index');
    Route::get('/comments/{comment}', [CommentController::class, 'apiShow'])->name('api.comments.show');
    
    Route::get('/users', [UserController::class, 'apiIndex'])->name('api.users.index');
    Route::get('/users/{user}', [UserController::class, 'apiShow'])->name('api.users.show');
});
