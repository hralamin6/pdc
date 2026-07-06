@props([
    'label' => 'Upload Image',
    'model' => null,        // e.g. "featured_image" -> will become wire:model="featured_image"
    'src' => null,          // existing image url (string)
    'accept' => 'image/*',
    'key' => null,
    'id' => Str::uuid(),
])

<div
  x-data="{
        isDropping: false,
        preview: null,
        // handle file input change
        handleFileChange(e) {
            const f = (e.target && e.target.files) ? e.target.files[0] : (e.files ? e.files[0] : null);
            if (f) {
                // revoke old preview if present
                if (this.preview) URL.revokeObjectURL(this.preview);
                this.preview = URL.createObjectURL(f);
            }
        },
        // handle a drop: assign files to the hidden input and dispatch 'change' so Livewire picks up file
        handleDrop(e) {
            e.preventDefault();
            this.isDropping = false;
            const files = e.dataTransfer.files;
            if (!files || files.length === 0) return;
            // set files on the input element
            this.$refs.input.files = files;
            // trigger change
            this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
            // also create preview
            this.handleFileChange({ target: { files }});
        }
    }"
  class="flex flex-col gap-3"
  wire:key="{{ $key ?? $id }}"
>
  {{-- Label --}}
  <label for="file-input-{{ $id }}" class="font-semibold text-sm text-gray-700">
    {{ __($label) }}
  </label>

  {{-- Upload Box --}}
  <div
    x-on:dragover.prevent="isDropping = true"
    x-on:dragleave.prevent="isDropping = false"
    x-on:drop.prevent="handleDrop"
    class="relative flex flex-col items-center justify-center w-48 h-48 border-2 border-dashed rounded-xl cursor-pointer transition-all duration-300 bg-base-200 hover:bg-base-300"
    :class="isDropping ? 'ring ring-primary ring-offset-2' : ''"
  >
    {{-- file input --}}
    <input
      x-ref="input"
      id="file-input-{{ $id }}"
      type="file"
      accept="{{ $accept }}"
      x-on:change="handleFileChange"
      class="absolute inset-0 opacity-0 cursor-pointer"
      @if($model) wire:model="{{ $model }}" @endif
    />

    {{-- Preview (newly selected) --}}
    <template x-if="preview">
      <img :src="preview" alt="Preview" class="w-full h-full object-cover rounded-xl shadow" />
    </template>

    {{-- Existing src (shows only if no preview) --}}
    @if($src)
      <img x-show="!preview" src="{{ $src }}" alt="Existing" class="w-full h-full object-cover rounded-xl shadow" />
    @endif

    {{-- Placeholder when neither preview nor src --}}
    <div x-show="!preview && !@js($src)" class="flex flex-col items-center text-center text-sm text-gray-500 pointer-events-none">
      <x-icon name="o-photo" class="w-10 h-10 opacity-50" />
      <span class="mt-2">Drop image or click to upload</span>
    </div>

    {{-- Loading overlay for Livewire file upload --}}
    <div wire:loading.flex wire:target="{{ $model }}" class="absolute inset-0 bg-base-300 bg-opacity-70 backdrop-blur-sm items-center justify-center rounded-xl">
      <span class="loading loading-spinner text-primary"></span>
    </div>
  </div>
  <!-- Crop Modal -->


  {{-- Progress bar --}}
  <div wire:loading wire:target="{{ $model }}" class="w-48">
    <progress max="100" class="progress progress-primary w-full"></progress>
  </div>

  {{-- Validation error --}}
  @if($model)
    @error($model)
    <p class="text-error text-xs mt-1">{{ $message }}</p>
    @enderror
  @endif
</div>
