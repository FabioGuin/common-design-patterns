@php
    $sizeClass = match($size ?? 'md') {
        'xs' => 'avatar-xs',
        'sm' => 'avatar-sm', 
        'md' => 'avatar-md',
        'lg' => 'avatar-lg',
        'xl' => 'avatar-xl',
        default => 'avatar-md'
    };
    
    $initials = $user->initials ?? strtoupper(substr($user->name, 0, 2));
    $avatarUrl = $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF';
@endphp

<div class="avatar {{ $sizeClass }} {{ $class ?? '' }}" {{ $attributes }}>
    @if($user->avatar)
        <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="rounded-circle">
    @else
        <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center">
            {{ $initials }}
        </div>
    @endif
</div>

<style>
.avatar-xs { width: 24px; height: 24px; font-size: 10px; }
.avatar-sm { width: 32px; height: 32px; font-size: 12px; }
.avatar-md { width: 40px; height: 40px; font-size: 14px; }
.avatar-lg { width: 48px; height: 48px; font-size: 16px; }
.avatar-xl { width: 64px; height: 64px; font-size: 20px; }

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: bold;
}
</style>
