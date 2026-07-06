@props([
    'label' => null,
    'hint' => null,
    'image' => null,
    'placeholder' => null,
    'size' => 'h-64',
    'required' => false,
    'accept' => 'image/*',
    'model' => 'photo',
])

@php
    $inputId = 'avatar-upload-' . uniqid();
    $defaultPlaceholder = $image;
@endphp

<div class="w-full" x-data="{ uploadProgress: 0 }">
    {{-- Label --}}
    @if($label)
        <label class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="flex flex-col items-center justify-center gap-4">
        {{-- Avatar with File Input --}}
        <label for="{{ $inputId }}" class="cursor-pointer group">
            <div class="relative {{ $size }} overflow-hidden rounded-md transition-all duration-200 ring-4 ring-primary/20 group-hover:ring-primary/40 group-hover:scale-105">
                <img
                    src="{{ $image ?: $defaultPlaceholder }}"
                    alt="{{ $label ?: 'Preview' }}"
                    class="object-cover w-full h-full"
                />

                {{-- Hover Overlay --}}
                <div class="absolute inset-0 flex items-center justify-center transition-opacity duration-200 opacity-0 bg-black/60 group-hover:opacity-100">
                    <div class="text-center text-white">
                        <x-icon name="o-camera" class="w-8 h-8 mx-auto mb-1" />
                        <p class="text-xs font-medium">
                            @if($image)
                                {{ __('Change') }}
                            @else
                                {{ __('Upload') }}
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Upload Progress Overlay --}}
                <div wire:loading wire:target="{{ $model }}"
                     class="absolute inset-0 flex items-center justify-center bg-black/80 backdrop-blur-sm">
                    <div class="text-center text-white">
                        {{-- Circular Progress --}}
                        <div class="relative w-20 h-20 mx-auto mb-2">
                            <svg class="w-20 h-20 transform -rotate-90">
                                {{-- Background circle --}}
                                <circle
                                    cx="40"
                                    cy="40"
                                    r="34"
                                    stroke="currentColor"
                                    stroke-width="6"
                                    fill="none"
                                    class="text-gray-600" />
                                {{-- Progress circle --}}
                                <circle
                                    cx="40"
                                    cy="40"
                                    r="34"
                                    stroke="currentColor"
                                    stroke-width="6"
                                    fill="none"
                                    stroke-linecap="round"
                                    class="text-primary transition-all duration-300"
                                    :stroke-dasharray="`${2 * Math.PI * 34}`"
                                    :stroke-dashoffset="`${2 * Math.PI * 34 * (1 - uploadProgress / 100)}`" />
                            </svg>
                            {{-- Percentage text in center --}}
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-lg font-bold" x-text="uploadProgress + '%'">0%</span>
                            </div>
                        </div>
                        <p class="text-xs font-medium">{{ __('Uploading...') }}</p>
                    </div>
                </div>
            </div>

            <input
                type="file"
                id="{{ $inputId }}"
                wire:model="{{ $model }}"
                accept="{{ $accept }}"
                class="hidden"
                @if($required) required @endif
                x-on:livewire-upload-start="uploadProgress = 0"
                x-on:livewire-upload-progress="uploadProgress = $event.detail.progress"
                x-on:livewire-upload-finish="uploadProgress = 100"
                x-on:livewire-upload-error="uploadProgress = 0"
            />
        </label>

        {{-- Progress Bar --}}
        <div wire:loading wire:target="{{ $model }}" class="w-full max-w-xs">
            <div class="space-y-2">
                <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                    <span class="flex items-center gap-2">
                        <span class="loading loading-spinner loading-xs"></span>
                        <span class="font-medium">{{ __('Uploading...') }}</span>
                    </span>
                    <span class="font-bold text-primary" x-text="uploadProgress + '%'">0%</span>
                </div>
                <div class="relative w-full h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="h-full transition-all duration-300 ease-out bg-gradient-to-r from-primary to-secondary"
                         :style="`width: ${uploadProgress}%`"></div>
                    {{-- Shimmer effect --}}
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer"></div>
                </div>
            </div>
        </div>

        {{-- Hint Text --}}
        @if($hint)
            <p class="text-xs text-center text-gray-500 dark:text-gray-400">
                {{ $hint }}
            </p>
        @endif

        {{-- Success Message --}}
{{--        @if($image)--}}
{{--            <div wire:loading.remove wire:target="{{ $model }}"--}}
{{--                 class="flex items-center gap-2 px-4 py-2 text-sm transition-all duration-200 rounded-lg bg-success/10 text-success">--}}
{{--                <x-icon name="o-check-circle" class="w-5 h-5" />--}}
{{--                <span class="font-medium">{{ __('Image selected') }}</span>--}}
{{--            </div>--}}
{{--        @endif--}}

        {{-- Error Message --}}
        @error($model)
            <div class="flex items-center gap-2 px-3 py-1.5 text-sm rounded-lg bg-error/10 text-error">
                <x-icon name="o-exclamation-circle" class="w-4 h-4" />
                <span>{{ $message }}</span>
            </div>
        @enderror
    </div>
</div>
