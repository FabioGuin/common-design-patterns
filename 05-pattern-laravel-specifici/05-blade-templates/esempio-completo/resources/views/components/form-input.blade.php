<div class="form-group mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required ?? false)
            <span class="text-danger">*</span>
        @endif
    </label>
    
    <input 
        type="{{ $type ?? 'text' }}" 
        name="{{ $name }}" 
        id="{{ $name }}" 
        class="form-control @error($name) is-invalid @enderror"
        value="{{ old($name, $value ?? '') }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['placeholder' => $placeholder ?? '']) }}
        {{ $attributes->merge(['disabled' => $disabled ?? false]) }}
        {{ $attributes->merge(['readonly' => $readonly ?? false]) }}
    >
    
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    
    @if(isset($help))
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
