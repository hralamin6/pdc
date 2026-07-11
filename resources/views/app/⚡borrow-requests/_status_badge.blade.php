@props(['status'])

@php
    $color = match($status) {
        'pending' => 'badge-warning',
        'accepted' => 'badge-info',
        'rejected' => 'badge-error',
        'given' => 'badge-secondary',
        'active' => 'badge-primary',
        'returned' => 'badge-success',
        default => 'badge-ghost',
    };
@endphp

<span class="badge {{ $color }} border-none shadow-sm text-xs text-white">
    {{ str_replace('_', ' ', Str::title($status)) }}
</span>
