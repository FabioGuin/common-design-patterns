<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Eloquent Enhancement - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-6xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                AI Eloquent Enhancement - Esempio
            </h1>
            
            <!-- Test Base -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Test Pattern Completo</h2>
                <p class="text-gray-600 mb-4">
                    Testa tutte le funzionalità dell'AI Eloquent Enhancement: ricerca semantica, generazione tag, traduzione, correlati, classificazione e sentiment analysis.
                </p>
                
                <button id="fullTestBtn" class="bg-blue-500 text-white px-6 py-3 rounded hover:bg-blue-600 mb-4">
                    Esegui Test Completo
                </button>
                
                <div id="fullTestResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                    <h3 class="font-semibold mb-2">Risultato Test Completo:</h3>
                    <pre id="fullTestResultContent" class="text-sm overflow-x-auto"></pre>
                </div>
            </div>

            <!-- Funzionalità AI -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Ricerca Semantica -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Ricerca Semantica</h2>
                    <p class="text-gray-600 mb-4">
                        Cerca articoli usando query semantiche che capiscono il significato.
                    </p>
                    
                    <div class="mb-4">
                        <input type="text" id="searchQuery" placeholder="Es: ricetta pane" 
                               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <button id="searchBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 mb-4">
                        Cerca
                    </button>
                    
                    <div id="searchResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Risultati:</h3>
                        <div id="searchResultContent" class="text-sm"></div>
                    </div>
                </div>

                <!-- Generazione Tag -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Generazione Tag AI</h2>
                    <p class="text-gray-600 mb-4">
                        Genera automaticamente tag per un articolo usando l'AI.
                    </p>
                    
                    <div class="mb-4">
                        <select id="articleSelect" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleziona un articolo</option>
                            @foreach($articles as $article)
                                <option value="{{ $article->id }}">{{ $article->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button id="generateTagsBtn" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 mb-4">
                        Genera Tag
                    </button>
                    
                    <div id="tagsResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Tag Generati:</h3>
                        <div id="tagsResultContent" class="text-sm"></div>
                    </div>
                </div>

                <!-- Traduzione -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Traduzione AI</h2>
                    <p class="text-gray-600 mb-4">
                        Traduci un articolo in una lingua specifica usando l'AI.
                    </p>
                    
                    <div class="mb-4">
                        <select id="translateArticleSelect" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 mb-2">
                            <option value="">Seleziona un articolo</option>
                            @foreach($articles as $article)
                                <option value="{{ $article->id }}">{{ $article->title }}</option>
                            @endforeach
                        </select>
                        <select id="languageSelect" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="en">Inglese</option>
                            <option value="es">Spagnolo</option>
                            <option value="fr">Francese</option>
                            <option value="de">Tedesco</option>
                        </select>
                    </div>
                    
                    <button id="translateBtn" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 mb-4">
                        Traduci
                    </button>
                    
                    <div id="translateResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Traduzione:</h3>
                        <div id="translateResultContent" class="text-sm"></div>
                    </div>
                </div>

                <!-- Articoli Correlati -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Articoli Correlati</h2>
                    <p class="text-gray-600 mb-4">
                        Trova articoli correlati basati su similarità semantica.
                    </p>
                    
                    <div class="mb-4">
                        <select id="correlatedArticleSelect" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleziona un articolo</option>
                            @foreach($articles as $article)
                                <option value="{{ $article->id }}">{{ $article->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button id="correlatedBtn" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 mb-4">
                        Trova Correlati
                    </button>
                    
                    <div id="correlatedResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Articoli Correlati:</h3>
                        <div id="correlatedResultContent" class="text-sm"></div>
                    </div>
                </div>

                <!-- Classificazione -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Classificazione AI</h2>
                    <p class="text-gray-600 mb-4">
                        Classifica automaticamente un articolo in categorie.
                    </p>
                    
                    <div class="mb-4">
                        <select id="classifyArticleSelect" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleziona un articolo</option>
                            @foreach($articles as $article)
                                <option value="{{ $article->id }}">{{ $article->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button id="classifyBtn" class="bg-pink-500 text-white px-4 py-2 rounded hover:bg-pink-600 mb-4">
                        Classifica
                    </button>
                    
                    <div id="classifyResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Classificazione:</h3>
                        <div id="classifyResultContent" class="text-sm"></div>
                    </div>
                </div>

                <!-- Sentiment Analysis -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Analisi Sentiment</h2>
                    <p class="text-gray-600 mb-4">
                        Analizza il sentiment di un articolo usando l'AI.
                    </p>
                    
                    <div class="mb-4">
                        <select id="sentimentArticleSelect" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleziona un articolo</option>
                            @foreach($articles as $article)
                                <option value="{{ $article->id }}">{{ $article->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button id="sentimentBtn" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 mb-4">
                        Analizza Sentiment
                    </button>
                    
                    <div id="sentimentResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Sentiment:</h3>
                        <div id="sentimentResultContent" class="text-sm"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test Completo
        document.getElementById('fullTestBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/ai-eloquent/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                document.getElementById('fullTestResultContent').textContent = JSON.stringify(data, null, 2);
                document.getElementById('fullTestResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante il test: ' + error.message);
            }
        });

        // Ricerca Semantica
        document.getElementById('searchBtn').addEventListener('click', async function() {
            const query = document.getElementById('searchQuery').value;
            if (!query) {
                alert('Inserisci una query di ricerca');
                return;
            }

            try {
                const response = await fetch('/api/ai-eloquent/search', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ query: query })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    let html = `<p><strong>Query:</strong> ${data.query}</p>`;
                    html += `<p><strong>Risultati trovati:</strong> ${data.count}</p>`;
                    
                    if (data.data.length > 0) {
                        html += '<ul class="mt-2">';
                        data.data.forEach(article => {
                            html += `<li class="mb-2"><strong>${article.title}</strong><br><small>${article.content.substring(0, 100)}...</small></li>`;
                        });
                        html += '</ul>';
                    }
                    
                    document.getElementById('searchResultContent').innerHTML = html;
                } else {
                    document.getElementById('searchResultContent').innerHTML = `<p class="text-red-600">Errore: ${data.message}</p>`;
                }
                
                document.getElementById('searchResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante la ricerca: ' + error.message);
            }
        });

        // Generazione Tag
        document.getElementById('generateTagsBtn').addEventListener('click', async function() {
            const articleId = document.getElementById('articleSelect').value;
            if (!articleId) {
                alert('Seleziona un articolo');
                return;
            }

            try {
                const response = await fetch('/api/ai-eloquent/generate-tags', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ article_id: articleId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    let html = `<p><strong>Articolo:</strong> ${data.data.title}</p>`;
                    html += `<p><strong>Tag generati:</strong> ${data.data.tags.join(', ')}</p>`;
                    
                    document.getElementById('tagsResultContent').innerHTML = html;
                } else {
                    document.getElementById('tagsResultContent').innerHTML = `<p class="text-red-600">Errore: ${data.message}</p>`;
                }
                
                document.getElementById('tagsResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante la generazione dei tag: ' + error.message);
            }
        });

        // Traduzione
        document.getElementById('translateBtn').addEventListener('click', async function() {
            const articleId = document.getElementById('translateArticleSelect').value;
            const language = document.getElementById('languageSelect').value;
            
            if (!articleId) {
                alert('Seleziona un articolo');
                return;
            }

            try {
                const response = await fetch('/api/ai-eloquent/translate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ 
                        article_id: articleId,
                        language: language 
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    let html = `<p><strong>Articolo originale:</strong> ${data.data.original_title}</p>`;
                    html += `<p><strong>Lingua:</strong> ${data.data.language}</p>`;
                    html += `<p><strong>Titolo tradotto:</strong> ${data.data.translation.title}</p>`;
                    html += `<p><strong>Contenuto tradotto:</strong> ${data.data.translation.content.substring(0, 200)}...</p>`;
                    
                    document.getElementById('translateResultContent').innerHTML = html;
                } else {
                    document.getElementById('translateResultContent').innerHTML = `<p class="text-red-600">Errore: ${data.message}</p>`;
                }
                
                document.getElementById('translateResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante la traduzione: ' + error.message);
            }
        });

        // Articoli Correlati
        document.getElementById('correlatedBtn').addEventListener('click', async function() {
            const articleId = document.getElementById('correlatedArticleSelect').value;
            if (!articleId) {
                alert('Seleziona un articolo');
                return;
            }

            try {
                const response = await fetch('/api/ai-eloquent/related', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ article_id: articleId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    let html = `<p><strong>Articolo:</strong> ${data.data.title}</p>`;
                    html += `<p><strong>Articoli correlati trovati:</strong> ${data.data.count}</p>`;
                    
                    if (data.data.related_articles.length > 0) {
                        html += '<ul class="mt-2">';
                        data.data.related_articles.forEach(article => {
                            html += `<li class="mb-2"><strong>${article.title}</strong><br><small>${article.content.substring(0, 100)}...</small></li>`;
                        });
                        html += '</ul>';
                    }
                    
                    document.getElementById('correlatedResultContent').innerHTML = html;
                } else {
                    document.getElementById('correlatedResultContent').innerHTML = `<p class="text-red-600">Errore: ${data.message}</p>`;
                }
                
                document.getElementById('correlatedResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante la ricerca di articoli correlati: ' + error.message);
            }
        });

        // Classificazione
        document.getElementById('classifyBtn').addEventListener('click', async function() {
            const articleId = document.getElementById('classifyArticleSelect').value;
            if (!articleId) {
                alert('Seleziona un articolo');
                return;
            }

            try {
                const response = await fetch('/api/ai-eloquent/classify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ article_id: articleId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    let html = `<p><strong>Articolo:</strong> ${data.data.title}</p>`;
                    html += `<p><strong>Categoria:</strong> ${data.data.category}</p>`;
                    
                    document.getElementById('classifyResultContent').innerHTML = html;
                } else {
                    document.getElementById('classifyResultContent').innerHTML = `<p class="text-red-600">Errore: ${data.message}</p>`;
                }
                
                document.getElementById('classifyResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante la classificazione: ' + error.message);
            }
        });

        // Sentiment Analysis
        document.getElementById('sentimentBtn').addEventListener('click', async function() {
            const articleId = document.getElementById('sentimentArticleSelect').value;
            if (!articleId) {
                alert('Seleziona un articolo');
                return;
            }

            try {
                const response = await fetch('/api/ai-eloquent/sentiment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ article_id: articleId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    let html = `<p><strong>Articolo:</strong> ${data.data.title}</p>`;
                    html += `<p><strong>Sentiment:</strong> ${data.data.sentiment}</p>`;
                    html += `<p><strong>Confidenza:</strong> ${(data.data.confidence * 100).toFixed(1)}%</p>`;
                    
                    document.getElementById('sentimentResultContent').innerHTML = html;
                } else {
                    document.getElementById('sentimentResultContent').innerHTML = `<p class="text-red-600">Errore: ${data.message}</p>`;
                }
                
                document.getElementById('sentimentResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante l\'analisi del sentiment: ' + error.message);
            }
        });
    </script>
</body>
</html>
