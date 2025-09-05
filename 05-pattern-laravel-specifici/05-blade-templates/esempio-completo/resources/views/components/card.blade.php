<div class="card {{ $class ?? '' }}" {{ $attributes }}>
    @if(isset($header))
        <div class="card-header {{ $headerClass ?? '' }}">
            {{ $header }}
        </div>
    @endif
    
    <div class="card-body {{ $bodyClass ?? '' }}">
        {{ $slot }}
    </div>
    
    @if(isset($footer))
        <div class="card-footer {{ $footerClass ?? '' }}">
            {{ $footer }}
        </div>
    @endif
</div>
