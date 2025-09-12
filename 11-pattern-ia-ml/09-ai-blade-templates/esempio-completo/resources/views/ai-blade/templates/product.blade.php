<div class="product-template bg-white rounded-lg shadow-lg overflow-hidden">
    <!-- Header con AI SEO -->
    @aiSeo($product->getContentForAI())
        <title>{{ $aiContent['name'] ?? $product->name }}</title>
        <meta name="description" content="{{ $aiContent['description'] ?? $product->description }}">
    @endai

    <div class="grid md:grid-cols-2 gap-6 p-6">
        <!-- Immagine Prodotto -->
        <div class="product-image">
            <img src="{{ $product->image_url }}" 
                 alt="{{ $aiContent['name'] ?? $product->name }}"
                 class="w-full h-64 object-cover rounded-lg">
            
            <!-- AI Image Optimization -->
            @aiImage($product->getContentForAI())
                <div class="image-optimization mt-2 text-sm text-gray-600">
                    {{ $optimizedImage }}
                </div>
            @endai
        </div>

        <!-- Informazioni Prodotto -->
        <div class="product-info">
            <!-- Nome Prodotto AI -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                @aiContent('title', $product->getContentForAI())
                    {{ $product->name }}
                @endai
            </h1>

            <!-- Prezzo -->
            <div class="price text-3xl font-bold text-green-600 mb-4">
                €{{ number_format($product->price, 2) }}
            </div>

            <!-- Rating -->
            <div class="rating flex items-center mb-4">
                <div class="stars text-yellow-400">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $product->rating)
                            ★
                        @else
                            ☆
                        @endif
                    @endfor
                </div>
                <span class="ml-2 text-gray-600">({{ $product->reviews_count }} recensioni)</span>
            </div>

            <!-- Descrizione AI -->
            <div class="description mb-6">
                <h3 class="text-lg font-semibold mb-2">Descrizione</h3>
                <p class="text-gray-700">
                    @aiContent('description', $product->getContentForAI())
                        {{ $product->description }}
                    @endai
                </p>
            </div>

            <!-- Caratteristiche AI -->
            <div class="features mb-6">
                <h3 class="text-lg font-semibold mb-2">Caratteristiche Principali</h3>
                <ul class="list-disc list-inside text-gray-700">
                    @aiContent('features', $product->getContentForAI())
                        @foreach($product->features as $feature)
                            <li>{{ $feature }}</li>
                        @endforeach
                    @endai
                </ul>
            </div>

            <!-- Benefici AI -->
            <div class="benefits mb-6">
                <h3 class="text-lg font-semibold mb-2">Benefici</h3>
                <ul class="list-disc list-inside text-gray-700">
                    @aiContent('benefits', $product->getContentForAI())
                        @foreach($product->benefits as $benefit)
                            <li>{{ $benefit }}</li>
                        @endforeach
                    @endai
                </ul>
            </div>

            <!-- Pulsanti Azione -->
            <div class="actions flex gap-4">
                <button class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    Aggiungi al Carrello
                </button>
                <button class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-300 transition">
                    Aggiungi ai Preferiti
                </button>
            </div>
        </div>
    </div>

    <!-- Sezione Raccomandazioni AI -->
    <div class="recommendations bg-gray-50 p-6">
        <h3 class="text-xl font-semibold mb-4">Prodotti Correlati</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @aiRecommendations($product->getContentForAI())
                @foreach($recommendations as $recommendation)
                    <div class="recommendation-item bg-white p-4 rounded-lg shadow">
                        <h4 class="font-semibold">{{ $recommendation }}</h4>
                        <p class="text-sm text-gray-600">Prodotto correlato</p>
                    </div>
                @endforeach
            @endai
        </div>
    </div>

    <!-- Sezione Recensioni AI -->
    <div class="reviews p-6">
        <h3 class="text-xl font-semibold mb-4">Recensioni</h3>
        <div class="space-y-4">
            @aiReviews($product->getContentForAI())
                @foreach($reviews as $review)
                    <div class="review bg-white p-4 rounded-lg shadow">
                        <div class="flex items-center mb-2">
                            <div class="stars text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review['rating'])
                                        ★
                                    @else
                                        ☆
                                    @endif
                                @endfor
                            </div>
                            <span class="ml-2 font-semibold">{{ $review['author'] }}</span>
                        </div>
                        <p class="text-gray-700">{{ $review['content'] }}</p>
                    </div>
                @endforeach
            @endai
        </div>
    </div>
</div>
