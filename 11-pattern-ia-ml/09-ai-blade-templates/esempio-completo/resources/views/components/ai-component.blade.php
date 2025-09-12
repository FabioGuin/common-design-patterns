@props(['type', 'data', 'options' => []])

<div class="ai-component" data-type="{{ $type }}">
    @switch($type)
        @case('content')
            <div class="ai-content">
                {{ $this->generateContent() }}
            </div>
            @break
            
        @case('translate')
            <div class="ai-translate">
                {{ $this->translateContent($options['language'] ?? 'en') }}
            </div>
            @break
            
        @case('personalize')
            <div class="ai-personalize">
                {{ $this->personalizeContent($options['user'] ?? null) }}
            </div>
            @break
            
        @case('seo')
            <div class="ai-seo">
                {!! $this->generateSeo() !!}
            </div>
            @break
            
        @case('image')
            <div class="ai-image">
                {{ $this->optimizeImage() }}
            </div>
            @break
            
        @case('recommendations')
            <div class="ai-recommendations">
                {{ $this->generateRecommendations() }}
            </div>
            @break
            
        @case('reviews')
            <div class="ai-reviews">
                {{ $this->generateReviews() }}
            </div>
            @break
            
        @case('meta')
            <div class="ai-meta">
                {!! $this->generateMeta() !!}
            </div>
            @break
            
        @default
            <div class="ai-fallback">
                {{ $this->fallbackContent(function() { return $this->generateContent(); }, 'Contenuto non disponibile') }}
            </div>
    @endswitch
</div>
