@extends('layouts.app')

@section('title', 'Resource Controllers Demo - Sistema Blog')

@section('content')
<div class="space-y-8">
    <!-- Dashboard Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Post Totali</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $posts->count() }}</p>
            <a href="{{ route('posts.index') }}" class="text-blue-500 hover:text-blue-700 text-sm">Vedi tutti i post</a>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Categorie</h3>
            <p class="text-3xl font-bold text-green-600">{{ $categories->count() }}</p>
            <a href="{{ route('categories.index') }}" class="text-green-500 hover:text-green-700 text-sm">Vedi tutte le categorie</a>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Commenti</h3>
            <p class="text-3xl font-bold text-purple-600">{{ $comments->count() }}</p>
            <a href="{{ route('comments.index') }}" class="text-purple-500 hover:text-purple-700 text-sm">Vedi tutti i commenti</a>
        </div>
    </div>

    <!-- Recent Posts -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Post Recenti</h2>
        <div class="space-y-4">
            @forelse($posts as $post)
                <div class="border-b border-gray-200 pb-4 last:border-b-0">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900">
                                <a href="{{ route('posts.show', $post) }}" class="hover:text-blue-600">
                                    {{ $post->title }}
                                </a>
                            </h3>
                            <p class="text-gray-600 text-sm mt-1">{{ $post->excerpt }}</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                <span>Categoria: {{ $post->category->name ?? 'Nessuna' }}</span>
                                <span>Autore: {{ $post->user->name ?? 'Sconosciuto' }}</span>
                                <span>Data: {{ $post->created_at->format('d/m/Y') }}</span>
                                <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $post->status_display }}</span>
                            </div>
                        </div>
                        <div class="flex space-x-2 ml-4">
                            <a href="{{ route('posts.show', $post) }}" 
                               class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                                Visualizza
                            </a>
                            <a href="{{ route('posts.edit', $post) }}" 
                               class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                                Modifica
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Nessun post disponibile.</p>
            @endforelse
        </div>
        <div class="mt-4">
            <a href="{{ route('posts.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Vedi tutti i post
            </a>
        </div>
    </div>

    <!-- Categories -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Categorie</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($categories as $category)
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900">{{ $category->name }}</h3>
                    <p class="text-gray-600 text-sm mt-1">{{ $category->description ?: 'Nessuna descrizione' }}</p>
                    <div class="flex items-center justify-between mt-3">
                        <span class="text-sm text-gray-500">{{ $category->posts_count }} post</span>
                        <div class="flex space-x-2">
                            <a href="{{ route('categories.show', $category) }}" 
                               class="text-blue-500 hover:text-blue-700 text-sm">
                                Visualizza
                            </a>
                            <a href="{{ route('categories.edit', $category) }}" 
                               class="text-green-500 hover:text-green-700 text-sm">
                                Modifica
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 col-span-full">Nessuna categoria disponibile.</p>
            @endforelse
        </div>
        <div class="mt-4">
            <a href="{{ route('categories.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                Vedi tutte le categorie
            </a>
        </div>
    </div>

    <!-- Recent Comments -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Commenti Recenti</h2>
        <div class="space-y-4">
            @forelse($comments as $comment)
                <div class="border-b border-gray-200 pb-4 last:border-b-0">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="text-gray-900">{{ $comment->content_excerpt }}</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                <span>Post: {{ $comment->post->title }}</span>
                                <span>Autore: {{ $comment->user->name ?? 'Anonimo' }}</span>
                                <span>Data: {{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                <span class="px-2 py-1 {{ $comment->approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} rounded text-xs">
                                    {{ $comment->status_display }}
                                </span>
                            </div>
                        </div>
                        <div class="flex space-x-2 ml-4">
                            <a href="{{ route('comments.show', $comment) }}" 
                               class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                                Visualizza
                            </a>
                            @if($comment->canBeApproved())
                                <a href="{{ route('comments.approve', $comment) }}" 
                                   class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                                    Approva
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Nessun commento disponibile.</p>
            @endforelse
        </div>
        <div class="mt-4">
            <a href="{{ route('comments.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
                Vedi tutti i commenti
            </a>
        </div>
    </div>

    <!-- CRUD Operations Demo -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Operazioni CRUD Demo</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="text-center">
                <h3 class="font-medium text-gray-900 mb-2">Create</h3>
                <p class="text-sm text-gray-600 mb-3">Crea nuove risorse</p>
                <a href="{{ route('posts.create') }}" 
                   class="inline-block px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    Nuovo Post
                </a>
            </div>
            
            <div class="text-center">
                <h3 class="font-medium text-gray-900 mb-2">Read</h3>
                <p class="text-sm text-gray-600 mb-3">Visualizza risorse</p>
                <a href="{{ route('posts.index') }}" 
                   class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Lista Post
                </a>
            </div>
            
            <div class="text-center">
                <h3 class="font-medium text-gray-900 mb-2">Update</h3>
                <p class="text-sm text-gray-600 mb-3">Modifica risorse</p>
                <a href="{{ route('posts.index') }}" 
                   class="inline-block px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    Modifica Post
                </a>
            </div>
            
            <div class="text-center">
                <h3 class="font-medium text-gray-900 mb-2">Delete</h3>
                <p class="text-sm text-gray-600 mb-3">Elimina risorse</p>
                <a href="{{ route('posts.index') }}" 
                   class="inline-block px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Elimina Post
                </a>
            </div>
        </div>
    </div>

    <!-- API Demo -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">API Demo</h2>
        <p class="text-gray-600 mb-4">Testa le API REST per le risorse:</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center">
                <h3 class="font-medium text-gray-900 mb-2">Posts API</h3>
                <a href="{{ route('api.posts.index') }}" 
                   class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    GET /api/posts
                </a>
            </div>
            
            <div class="text-center">
                <h3 class="font-medium text-gray-900 mb-2">Comments API</h3>
                <a href="{{ route('api.comments.index') }}" 
                   class="inline-block px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
                    GET /api/comments
                </a>
            </div>
            
            <div class="text-center">
                <h3 class="font-medium text-gray-900 mb-2">Categories API</h3>
                <a href="{{ route('api.categories.index') }}" 
                   class="inline-block px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    GET /api/categories
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
