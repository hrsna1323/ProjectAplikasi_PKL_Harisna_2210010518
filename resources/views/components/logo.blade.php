@props([
    'size' => '40',
    'clickable' => false,
    'href' => null,
    'class' => '',
])

@php
    // Validate and sanitize size parameter
    $size = is_numeric($size) && $size > 0 ? $size : '40';
    
    // Determine wrapper element based on clickable prop
    $isClickable = $clickable && $href;
    $wrapperTag = $isClickable ? 'a' : 'div';
    
    // Build wrapper classes
    $wrapperClasses = $class;
    if ($isClickable) {
        $wrapperClasses .= ' inline-block transition-opacity hover:opacity-80 cursor-pointer';
    } else {
        $wrapperClasses .= ' inline-block';
    }
@endphp

@if($isClickable)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => trim($wrapperClasses)]) }} aria-label="Logo aplikasi - kembali ke dashboard">
        <svg 
            viewBox="0 0 100 100" 
            width="{{ $size }}" 
            height="{{ $size }}"
            role="img"
            aria-hidden="true"
            class="inline-block"
        >
            <!-- Top-left box -->
            <rect x="10" y="10" width="35" height="35" rx="8" fill="currentColor"/>
            
            <!-- Top-right box -->
            <rect x="55" y="10" width="35" height="35" rx="8" fill="currentColor"/>
            
            <!-- Bottom-left box -->
            <rect x="10" y="55" width="35" height="35" rx="8" fill="currentColor"/>
            
            <!-- Bottom-right box -->
            <rect x="55" y="55" width="35" height="35" rx="8" fill="currentColor"/>
        </svg>
    </a>
@else
    <div {{ $attributes->merge(['class' => trim($wrapperClasses)]) }}>
        <svg 
            viewBox="0 0 100 100" 
            width="{{ $size }}" 
            height="{{ $size }}"
            role="img"
            aria-label="Logo aplikasi"
            class="inline-block"
        >
            <!-- Top-left box -->
            <rect x="10" y="10" width="35" height="35" rx="8" fill="currentColor"/>
            
            <!-- Top-right box -->
            <rect x="55" y="10" width="35" height="35" rx="8" fill="currentColor"/>
            
            <!-- Bottom-left box -->
            <rect x="10" y="55" width="35" height="35" rx="8" fill="currentColor"/>
            
            <!-- Bottom-right box -->
            <rect x="55" y="55" width="35" height="35" rx="8" fill="currentColor"/>
        </svg>
    </div>
@endif
