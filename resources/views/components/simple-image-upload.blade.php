@props([
    'label' => 'Image',
    'hint' => null,
    'maxSize' => 2048, // KB
    'existingImage' => null,
    'placeholder' => null,
    'accept' => 'image/png,image/jpeg,image/jpg,image/webp',
    'required' => false,
])

@php
    $defaultPlaceholder = asset('logo.png');
@endphp

<div x-data="{
    uploading: false,
    progress: 0,
    previewUrl: null,
    isDragging: false,

    init() {
        $watch('uploading', value => {
            if (!value) {
                this.progress = 0;
            }
        });
    },

    handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            this.validateAndPreview(file);
        }
    },

    handleDrop(event) {
        this.isDragging = false;
        const files = event.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            if (this.validateAndPreview(file)) {
                // Set the file to the input element
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                this.$refs.fileInput.files = dataTransfer.files;

                // Trigger change event for Livewire
                this.$refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    },

    validateAndPreview(file) {
        // Validate file type
        const validTypes = '{{ $accept }}'.split(',');
        const isValid = validTypes.some(type => file.type === type.trim());

        if (!isValid) {
            alert('Invalid file type. Please upload: ' + validTypes.join(', '));
            return false;
        }

        // Validate file size
        const maxSizeBytes = {{ $maxSize }} * 1024;
        if (file.size > maxSizeBytes) {
            alert('File size exceeds {{ $maxSize }}KB limit');
            return false;
        }

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            this.previewUrl = e.target.result;
        };
        reader.readAsDataURL(file);

        return true;
    },

    removeImage() {
        this.previewUrl = null;
        this.$refs.fileInput.value = '';

        // Trigger change event to notify Livewire
        this.$refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
    },

    triggerFileInput() {
        this.$refs.fileInput.click();
    }
}" class="w-full">

    {{-- Label --}}
    @if($label)
        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    {{-- Upload Area --}}
    <div class="relative">
        {{-- Image Preview --}}
        <div
            @click="triggerFileInput"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            class="relative overflow-hidden transition-all duration-200 border-2 border-dashed rounded-lg cursor-pointer group"
            :class="{
                'border-primary bg-primary/5': isDragging,
                'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500': !isDragging
            }">

            {{-- Main Image Display --}}
            <div class="relative aspect-video bg-gray-100 dark:bg-gray-800">
                <img
                    src="{{ $existingImage ?: ($placeholder ?: $defaultPlaceholder) }}"
                    :src="previewUrl || '{{ $existingImage }}' || '{{ $placeholder ?: $defaultPlaceholder }}'"
                    alt="{{ $label }}"
                    class="object-cover w-full h-full"
                    :class="{
                        'opacity-50': !previewUrl && !'{{ $existingImage }}',
                        'opacity-100': previewUrl || '{{ $existingImage }}'
                    }">

                {{-- Overlay on Hover --}}
                <div class="absolute inset-0 flex items-center justify-center transition-opacity duration-200 opacity-0 bg-black/50 group-hover:opacity-100">
                    <div class="text-center text-white">
                        <x-icon name="o-cloud-arrow-up" class="w-12 h-12 mx-auto mb-2" />
                        <p class="text-sm font-medium">
                            <span x-show="!previewUrl && !'{{ $existingImage }}'">{{ __('Click or drag to upload') }}</span>
                            <span x-show="previewUrl || '{{ $existingImage }}'">{{ __('Click to change image') }}</span>
                        </p>
                        <p class="mt-1 text-xs opacity-80">{{ $hint ?: __('Drag and drop or click to browse') }}</p>
                    </div>
                </div>

                {{-- Image Type Badge --}}
                <div
                    x-show="previewUrl || '{{ $existingImage }}'"
                    class="absolute px-2 py-1 text-xs font-medium text-white rounded-md top-2 left-2 bg-black/50 backdrop-blur-sm">
                    <span x-show="previewUrl">{{ __('New') }}</span>
                    <span x-show="!previewUrl && '{{ $existingImage }}'">{{ __('Current') }}</span>
                </div>

                {{-- Remove Button --}}
                <button
                    type="button"
                    @click.stop="removeImage"
                    x-show="previewUrl || '{{ $existingImage }}'"
                    class="absolute p-1.5 text-white transition-colors duration-200 bg-red-500 rounded-full top-2 right-2 hover:bg-red-600">
                    <x-icon name="o-x-mark" class="w-4 h-4" />
                </button>
            </div>

            {{-- Progress Bar --}}
            <div
                x-show="uploading"
                x-cloak
                class="absolute bottom-0 left-0 right-0 h-1 overflow-hidden bg-gray-200 dark:bg-gray-700">
                <div
                    class="h-full transition-all duration-300 bg-primary"
                    :style="`width: ${progress}%`"></div>
            </div>
        </div>

        {{-- Hidden File Input --}}
        <input
            type="file"
            x-ref="fileInput"
            {{ $attributes->wire('model') }}
            accept="{{ $accept }}"
            @change="handleFileSelect"
            class="hidden"
            @if($required) required @endif
            x-on:livewire-upload-start="uploading = true"
            x-on:livewire-upload-finish="uploading = false"
            x-on:livewire-upload-cancel="uploading = false"
            x-on:livewire-upload-error="uploading = false"
            x-on:livewire-upload-progress="progress = $event.detail.progress" />
    </div>

    {{-- Upload Status --}}
    <div x-show="uploading" x-cloak class="flex items-center gap-2 mt-2 text-sm text-gray-600 dark:text-gray-400">
        <span class="loading loading-spinner loading-sm"></span>
        <span>{{ __('Uploading') }}... <span x-text="progress"></span>%</span>
    </div>

    {{-- Hint Text --}}
    @if($hint)
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            {{ $hint }}
        </p>
    @endif

    {{-- Error Message --}}
    @error($attributes->wire('model')->value)
        <p class="mt-2 text-sm text-red-600 dark:text-red-400">
            {{ $message }}
        </p>
    @enderror
</div>

