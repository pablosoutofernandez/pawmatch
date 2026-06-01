@props(['size' => 'sm'])

@php
    // Tamaños: 'sm' (junto a nombres), 'md' (cabeceras de perfil)
    $classes = $size === 'md'
        ? 'text-xs px-3 py-1 gap-1.5'
        : 'text-[11px] px-2 py-0.5 gap-1';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center $classes font-bold rounded-full
        bg-gradient-to-r from-brand-400 to-brand-600 text-white shadow-soft ring-1 ring-white/40 align-middle"]) }}
      title="Usuario Premium">
    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M12 2l2.39 4.84 5.34.78-3.86 3.76.91 5.32L12 14.98l-4.78 2.52.91-5.32L4.27 8.62l5.34-.78L12 2z"/>
    </svg>
    Premium
</span>
