@extends('layouts.app')

@section('title', 'Policy Demo - Sistema di Autorizzazione')

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
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Utenti</h3>
            <p class="text-3xl font-bold text-green-600">{{ $users->count() }}</p>
            <a href="{{ route('users.index') }}" class="text-green-500 hover:text-green-700 text-sm">Vedi tutti gli utenti</a>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Commenti</h3>
            <p class="text-3xl font-bold text-purple-600">{{ $comments->count() }}</p>
            <a href="{{ route('comments.index') }}" class="text-purple-500 hover:text-purple-700 text-sm">Vedi tutti i commenti</a>
        </div>
    </div>

    <!-- Authorization Examples -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Esempi di Autorizzazione</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 mb-2">Post Management</h3>
                <p class="text-sm text-gray-600 mb-3">Gestione post con autorizzazione basata su ruoli e proprietà</p>
                <div class="space-y-2">
                    @can('viewAny', App\Models\Post::class)
                        <a href="{{ route('posts.index') }}" 
                           class="block w-full text-center px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                            Visualizza Post
                        </a>
                    @else
                        <span class="block w-full text-center px-3 py-2 bg-gray-300 text-gray-500 text-sm rounded">
                            Accesso Negato
                        </span>
                    @endcan
                    
                    @can('create', App\Models\Post::class)
                        <a href="{{ route('posts.create') }}" 
                           class="block w-full text-center px-3 py-2 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                            Crea Post
                        </a>
                    @else
                        <span class="block w-full text-center px-3 py-2 bg-gray-300 text-gray-500 text-sm rounded">
                            Accesso Negato
                        </span>
                    @endcan
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 mb-2">User Management</h3>
                <p class="text-sm text-gray-600 mb-3">Gestione utenti con autorizzazione basata su ruoli</p>
                <div class="space-y-2">
                    @can('viewAny', App\Models\User::class)
                        <a href="{{ route('users.index') }}" 
                           class="block w-full text-center px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                            Visualizza Utenti
                        </a>
                    @else
                        <span class="block w-full text-center px-3 py-2 bg-gray-300 text-gray-500 text-sm rounded">
                            Accesso Negato
                        </span>
                    @endcan
                    
                    @can('create', App\Models\User::class)
                        <a href="{{ route('users.create') }}" 
                           class="block w-full text-center px-3 py-2 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                            Crea Utente
                        </a>
                    @else
                        <span class="block w-full text-center px-3 py-2 bg-gray-300 text-gray-500 text-sm rounded">
                            Accesso Negato
                        </span>
                    @endcan
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 mb-2">Comment Management</h3>
                <p class="text-sm text-gray-600 mb-3">Gestione commenti con autorizzazione granulare</p>
                <div class="space-y-2">
                    @can('viewAny', App\Models\Comment::class)
                        <a href="{{ route('comments.index') }}" 
                           class="block w-full text-center px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                            Visualizza Commenti
                        </a>
                    @else
                        <span class="block w-full text-center px-3 py-2 bg-gray-300 text-gray-500 text-sm rounded">
                            Accesso Negato
                        </span>
                    @endcan
                    
                    @can('create', App\Models\Comment::class)
                        <a href="{{ route('comments.create') }}" 
                           class="block w-full text-center px-3 py-2 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                            Crea Commento
                        </a>
                    @else
                        <span class="block w-full text-center px-3 py-2 bg-gray-300 text-gray-500 text-sm rounded">
                            Accesso Negato
                        </span>
                    @endcan
                </div>
            </div>
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
                                @can('view', $post)
                                    <a href="{{ route('posts.show', $post) }}" class="hover:text-blue-600">
                                        {{ $post->title }}
                                    </a>
                                @else
                                    <span class="text-gray-500">{{ $post->title }} (Accesso Negato)</span>
                                @endcan
                            </h3>
                            <p class="text-gray-600 text-sm mt-1">{{ $post->excerpt }}</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                <span>Autore: {{ $post->user->name ?? 'Sconosciuto' }}</span>
                                <span>Data: {{ $post->created_at->format('d/m/Y') }}</span>
                                <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $post->status_display }}</span>
                            </div>
                        </div>
                        <div class="flex space-x-2 ml-4">
                            @can('view', $post)
                                <a href="{{ route('posts.show', $post) }}" 
                                   class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                                    Visualizza
                                </a>
                            @endcan
                            
                            @can('update', $post)
                                <a href="{{ route('posts.edit', $post) }}" 
                                   class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                                    Modifica
                                </a>
                            @endcan
                            
                            @can('delete', $post)
                                <form action="{{ route('posts.destroy', $post) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600"
                                            onclick="return confirm('Sei sicuro di voler eliminare questo post?')">
                                        Elimina
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Nessun post disponibile.</p>
            @endforelse
        </div>
    </div>

    <!-- Recent Users -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Utenti Recenti</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($users as $user)
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900">
                        @can('view', $user)
                            <a href="{{ route('users.show', $user) }}" class="hover:text-blue-600">
                                {{ $user->name }}
                            </a>
                        @else
                            <span class="text-gray-500">{{ $user->name }} (Accesso Negato)</span>
                        @endcan
                    </h3>
                    <p class="text-gray-600 text-sm mt-1">{{ $user->email }}</p>
                    <div class="flex items-center justify-between mt-3">
                        <span class="text-sm text-gray-500">{{ $user->posts_count }} post</span>
                        <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $user->role_display_name }}</span>
                    </div>
                    <div class="flex space-x-2 mt-3">
                        @can('view', $user)
                            <a href="{{ route('users.show', $user) }}" 
                               class="text-blue-500 hover:text-blue-700 text-sm">
                                Visualizza
                            </a>
                        @endcan
                        
                        @can('update', $user)
                            <a href="{{ route('users.edit', $user) }}" 
                               class="text-green-500 hover:text-green-700 text-sm">
                                Modifica
                            </a>
                        @endcan
                    </div>
                </div>
            @empty
                <p class="text-gray-500 col-span-full">Nessun utente disponibile.</p>
            @endforelse
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
                            <p class="text-gray-900">
                                @can('view', $comment)
                                    {{ $comment->content_excerpt }}
                                @else
                                    <span class="text-gray-500">Contenuto non accessibile</span>
                                @endcan
                            </p>
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
                            @can('view', $comment)
                                <a href="{{ route('comments.show', $comment) }}" 
                                   class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                                    Visualizza
                                </a>
                            @endcan
                            
                            @can('approve', $comment)
                                <a href="{{ route('comments.approve', $comment) }}" 
                                   class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                                    Approva
                                </a>
                            @endcan
                            
                            @can('delete', $comment)
                                <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600"
                                            onclick="return confirm('Sei sicuro di voler eliminare questo commento?')">
                                        Elimina
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Nessun commento disponibile.</p>
            @endforelse
        </div>
    </div>

    <!-- Policy Information -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Informazioni sulle Policy</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-medium text-gray-900 mb-2">PostPolicy</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• view: Tutti possono vedere post pubblicati</li>
                    <li>• create: Solo utenti autenticati</li>
                    <li>• update: Autore, admin o moderatori</li>
                    <li>• delete: Solo admin o autore (se non pubblicato)</li>
                    <li>• publish: Autore, admin o moderatori</li>
                </ul>
            </div>
            
            <div>
                <h3 class="font-medium text-gray-900 mb-2">CommentPolicy</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• view: Commenti approvati o propri</li>
                    <li>• create: Solo utenti autenticati</li>
                    <li>• update: Autore (se non approvato) o admin</li>
                    <li>• delete: Autore, admin o moderatori</li>
                    <li>• approve: Solo admin o moderatori</li>
                </ul>
            </div>
            
            <div>
                <h3 class="font-medium text-gray-900 mb-2">UserPolicy</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• view: Proprio profilo o admin/moderatori</li>
                    <li>• create: Solo admin</li>
                    <li>• update: Proprio profilo o admin</li>
                    <li>• delete: Solo admin (non se stessi)</li>
                    <li>• changeRole: Solo admin (non se stessi)</li>
                </ul>
            </div>
            
            <div>
                <h3 class="font-medium text-gray-900 mb-2">Ruoli Utente</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• <strong>Admin</strong>: Accesso completo</li>
                    <li>• <strong>Moderator</strong>: Gestione contenuti</li>
                    <li>• <strong>User</strong>: Operazioni base</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
