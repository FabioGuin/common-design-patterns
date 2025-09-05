<div class="card component-card mb-4">
    @if($post->featured_image)
        <img src="{{ $post->featured_image_url }}" class="card-img-top" alt="{{ $post->title }}" style="height: 200px; object-fit: cover;">
    @endif
    
    <div class="card-body">
        <h5 class="card-title">{{ $post->title }}</h5>
        <p class="card-text">{{ $post->excerpt }}</p>
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <x-user-avatar :user="$post->user" size="sm" class="me-2" />
                <div>
                    <small class="text-muted d-block">{{ $post->user->name }}</small>
                    <small class="text-muted">{{ $post->formatted_published_date }}</small>
                </div>
            </div>
            
            <div class="d-flex align-items-center">
                <span class="badge bg-{{ $post->status_badge }} me-2">{{ $post->status_text }}</span>
                <small class="text-muted">{{ $post->reading_time }}</small>
            </div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="me-3">
                    <i class="fas fa-eye me-1"></i> {{ $post->views_count }}
                </span>
                <span class="me-3">
                    <i class="fas fa-heart me-1"></i> {{ $post->likes_count }}
                </span>
                <span>
                    <i class="fas fa-comment me-1"></i> {{ $post->comments_count }}
                </span>
            </div>
            
            <a href="{{ route('blog.show', $post) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> Read More
            </a>
        </div>
        
        @if($post->tags->count() > 0)
            <div class="mt-3">
                @foreach($post->tags as $tag)
                    <span class="badge bg-secondary me-1">{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>
