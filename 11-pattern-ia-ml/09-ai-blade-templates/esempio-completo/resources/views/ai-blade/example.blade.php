<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Blade Templates - Esempio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">
                AI Blade Templates - Esempio
            </h1>
            
            <!-- Test Base -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Test Pattern Completo</h2>
                <p class="text-gray-600 mb-4">
                    Testa tutte le funzionalità dell'AI Blade Templates: generazione contenuti, traduzione, personalizzazione, SEO, raccomandazioni e recensioni.
                </p>
                
                <button id="fullTestBtn" class="bg-blue-500 text-white px-6 py-3 rounded hover:bg-blue-600 mb-4">
                    Esegui Test Completo
                </button>
                
                <div id="fullTestResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                    <h3 class="font-semibold mb-2">Risultato Test Completo:</h3>
                    <pre id="fullTestResultContent" class="text-sm overflow-x-auto"></pre>
                </div>
            </div>

            <!-- Template AI Demo -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Template Prodotto -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Template Prodotto AI</h2>
                    <p class="text-gray-600 mb-4">
                        Template prodotto con contenuti generati automaticamente dall'AI.
                    </p>
                    
                    <div class="mb-4">
                        <select id="productSelect" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleziona un prodotto</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button id="renderProductBtn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 mb-4">
                        Renderizza Template
                    </button>
                    
                    <div id="productResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Template Renderizzato:</h3>
                        <div id="productResultContent" class="text-sm"></div>
                    </div>
                </div>

                <!-- Traduzione Template -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Traduzione Template</h2>
                    <p class="text-gray-600 mb-4">
                        Traduci automaticamente contenuti di template in diverse lingue.
                    </p>
                    
                    <div class="mb-4">
                        <textarea id="translateContent" placeholder="Inserisci contenuto da tradurre" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 h-20"></textarea>
                        <select id="translateLanguage" class="w-full mt-2 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="en">Inglese</option>
                            <option value="es">Spagnolo</option>
                            <option value="fr">Francese</option>
                            <option value="de">Tedesco</option>
                        </select>
                    </div>
                    
                    <button id="translateBtn" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 mb-4">
                        Traduci
                    </button>
                    
                    <div id="translateResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Traduzione:</h3>
                        <div id="translateResultContent" class="text-sm"></div>
                    </div>
                </div>

                <!-- Personalizzazione Template -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Personalizzazione Template</h2>
                    <p class="text-gray-600 mb-4">
                        Personalizza contenuti di template basandoti su preferenze utente.
                    </p>
                    
                    <div class="mb-4">
                        <textarea id="personalizeContent" placeholder="Inserisci contenuto da personalizzare" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 h-20"></textarea>
                        <input type="text" id="userName" placeholder="Nome utente" 
                               class="w-full mt-2 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="text" id="userPreferences" placeholder="Preferenze (es: modern, classic)" 
                               class="w-full mt-2 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <button id="personalizeBtn" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 mb-4">
                        Personalizza
                    </button>
                    
                    <div id="personalizeResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Contenuto Personalizzato:</h3>
                        <div id="personalizeResultContent" class="text-sm"></div>
                    </div>
                </div>

                <!-- SEO Template -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">SEO Template</h2>
                    <p class="text-gray-600 mb-4">
                        Genera meta tag SEO ottimizzati automaticamente.
                    </p>
                    
                    <div class="mb-4">
                        <textarea id="seoContent" placeholder="Inserisci contenuto per SEO" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 h-20"></textarea>
                    </div>
                    
                    <button id="seoBtn" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 mb-4">
                        Genera SEO
                    </button>
                    
                    <div id="seoResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Meta Tag SEO:</h3>
                        <div id="seoResultContent" class="text-sm"></div>
                    </div>
                </div>

                <!-- Raccomandazioni Template -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Raccomandazioni Template</h2>
                    <p class="text-gray-600 mb-4">
                        Genera raccomandazioni AI per prodotti correlati.
                    </p>
                    
                    <div class="mb-4">
                        <select id="recommendationProductSelect" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleziona un prodotto</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button id="recommendationsBtn" class="bg-pink-500 text-white px-4 py-2 rounded hover:bg-pink-600 mb-4">
                        Genera Raccomandazioni
                    </button>
                    
                    <div id="recommendationsResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Raccomandazioni:</h3>
                        <div id="recommendationsResultContent" class="text-sm"></div>
                    </div>
                </div>

                <!-- Recensioni Template -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-4">Recensioni Template</h2>
                    <p class="text-gray-600 mb-4">
                        Genera recensioni AI realistiche per prodotti.
                    </p>
                    
                    <div class="mb-4">
                        <select id="reviewsProductSelect" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleziona un prodotto</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button id="reviewsBtn" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 mb-4">
                        Genera Recensioni
                    </button>
                    
                    <div id="reviewsResult" class="mt-4 p-4 bg-gray-50 rounded hidden">
                        <h3 class="font-semibold mb-2">Recensioni:</h3>
                        <div id="reviewsResultContent" class="text-sm"></div>
                    </div>
                </div>
            </div>

            <!-- Template Demo -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Demo Template AI</h2>
                <p class="text-gray-600 mb-4">
                    Esempio di template Blade con direttive AI integrate.
                </p>
                
                <div class="bg-gray-50 p-4 rounded">
                    <h3 class="font-semibold mb-2">Codice Template:</h3>
                    <pre class="text-sm overflow-x-auto"><code>&lt;!-- Template Prodotto con AI --&gt;
@aiContent('description', $product)
    {{ $product->description }}
@endai

@aiTranslate('reviews', 'en')
    @foreach($reviews as $review)
        &lt;div class="review"&gt;{{ $review->content }}&lt;/div&gt;
    @endforeach
@endai

@aiPersonalize('recommendations', $user)
    &lt;div class="recommendations"&gt;
        @foreach($recommendations as $item)
            &lt;div class="item"&gt;{{ $item->name }}&lt;/div&gt;
        @endforeach
    &lt;/div&gt;
@endai

@aiSeo($page)
    &lt;title&gt;{{ $page->title }}&lt;/title&gt;
    &lt;meta name="description" content="{{ $page->description }}"&gt;
@endai</code></pre>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Test Completo
        document.getElementById('fullTestBtn').addEventListener('click', async function() {
            try {
                const response = await fetch('/api/ai-blade/test', {
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

        // Render Template Prodotto
        document.getElementById('renderProductBtn').addEventListener('click', async function() {
            const productId = document.getElementById('productSelect').value;
            if (!productId) {
                alert('Seleziona un prodotto');
                return;
            }

            try {
                const response = await fetch('/api/ai-blade/render', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ 
                        template: 'product',
                        data: { product_id: productId }
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('productResultContent').innerHTML = data.data.rendered_content;
                } else {
                    document.getElementById('productResultContent').innerHTML = `<p class="text-red-600">Errore: ${data.message}</p>`;
                }
                
                document.getElementById('productResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante il rendering: ' + error.message);
            }
        });

        // Traduzione
        document.getElementById('translateBtn').addEventListener('click', async function() {
            const content = document.getElementById('translateContent').value;
            const language = document.getElementById('translateLanguage').value;
            
            if (!content) {
                alert('Inserisci contenuto da tradurre');
                return;
            }

            try {
                const response = await fetch('/api/ai-blade/translate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ 
                        content: content,
                        language: language
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    let html = `<p><strong>Contenuto originale:</strong> ${data.data.original_content}</p>`;
                    html += `<p><strong>Traduzione (${data.data.language}):</strong> ${data.data.translated_content}</p>`;
                    
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

        // Personalizzazione
        document.getElementById('personalizeBtn').addEventListener('click', async function() {
            const content = document.getElementById('personalizeContent').value;
            const userName = document.getElementById('userName').value;
            const userPreferences = document.getElementById('userPreferences').value;
            
            if (!content) {
                alert('Inserisci contenuto da personalizzare');
                return;
            }

            try {
                const response = await fetch('/api/ai-blade/personalize', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ 
                        content: content,
                        user: {
                            name: userName,
                            preferences: userPreferences.split(',').map(p => p.trim())
                        }
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    let html = `<p><strong>Contenuto originale:</strong> ${data.data.original_content}</p>`;
                    html += `<p><strong>Contenuto personalizzato:</strong> ${data.data.personalized_content}</p>`;
                    
                    document.getElementById('personalizeResultContent').innerHTML = html;
                } else {
                    document.getElementById('personalizeResultContent').innerHTML = `<p class="text-red-600">Errore: ${data.message}</p>`;
                }
                
                document.getElementById('personalizeResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante la personalizzazione: ' + error.message);
            }
        });

        // SEO
        document.getElementById('seoBtn').addEventListener('click', async function() {
            const content = document.getElementById('seoContent').value;
            
            if (!content) {
                alert('Inserisci contenuto per SEO');
                return;
            }

            try {
                const response = await fetch('/api/ai-blade/render', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ 
                        template: 'seo',
                        data: { content: content }
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('seoResultContent').innerHTML = data.data.rendered_content;
                } else {
                    document.getElementById('seoResultContent').innerHTML = `<p class="text-red-600">Errore: ${data.message}</p>`;
                }
                
                document.getElementById('seoResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante la generazione SEO: ' + error.message);
            }
        });

        // Raccomandazioni
        document.getElementById('recommendationsBtn').addEventListener('click', async function() {
            const productId = document.getElementById('recommendationProductSelect').value;
            if (!productId) {
                alert('Seleziona un prodotto');
                return;
            }

            try {
                const response = await fetch('/api/ai-blade/render', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ 
                        template: 'recommendations',
                        data: { product_id: productId }
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('recommendationsResultContent').innerHTML = data.data.rendered_content;
                } else {
                    document.getElementById('recommendationsResultContent').innerHTML = `<p class="text-red-600">Errore: ${data.message}</p>`;
                }
                
                document.getElementById('recommendationsResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante la generazione delle raccomandazioni: ' + error.message);
            }
        });

        // Recensioni
        document.getElementById('reviewsBtn').addEventListener('click', async function() {
            const productId = document.getElementById('reviewsProductSelect').value;
            if (!productId) {
                alert('Seleziona un prodotto');
                return;
            }

            try {
                const response = await fetch('/api/ai-blade/render', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ 
                        template: 'reviews',
                        data: { product_id: productId }
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('reviewsResultContent').innerHTML = data.data.rendered_content;
                } else {
                    document.getElementById('reviewsResultContent').innerHTML = `<p class="text-red-600">Errore: ${data.message}</p>`;
                }
                
                document.getElementById('reviewsResult').classList.remove('hidden');
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore durante la generazione delle recensioni: ' + error.message);
            }
        });
    </script>
</body>
</html>
