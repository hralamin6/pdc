@props([
    'label' => null,
    'hint' => null,
    'multiple' => false,
    'maxSize' => 2048, // KB
    'maxFiles' => 5,
    'accept' => 'image/*',
    'required' => false,
    'disabled' => false,
    'existingImages' => [],
    'cropAspectRatio' => null, // e.g., '16/9', '1/1', '4/3'
    'maxWidth' => 1920,
    'maxHeight' => 1080,
    'compressionQuality' => 0.85,
])

@php
  $uuid = 'image-input-' . Str::uuid();
  $errorBag = $attributes->wire('model')->value();
@endphp

<div
  x-data="imageUploader({
        multiple: {{ $multiple ? 'true' : 'false' }},
        maxSize: {{ $maxSize }},
        maxFiles: {{ $maxFiles }},
        cropAspectRatio: {{ $cropAspectRatio ? "'{$cropAspectRatio}'" : 'null' }},
        maxWidth: {{ $maxWidth }},
        maxHeight: {{ $maxHeight }},
        compressionQuality: {{ $compressionQuality }},
        existingImages: {{ json_encode($existingImages) }},
    })"
  class="w-full"
  x-cloak
>
  {{-- Label --}}
  @if($label)
    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ $label }}
      @if($required)
        <span class="text-red-500">*</span>
      @endif
    </label>
  @endif

  {{-- Drag & Drop Zone --}}
  <div
    @dragover.prevent="dragOver = true"
    @dragleave.prevent="dragOver = false"
    @drop.prevent="handleDrop($event)"
    :class="{ 'border-primary bg-primary/5': dragOver }"
    class="relative border-2 border-dashed rounded-lg transition-colors duration-200
               border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500"
  >
    <input
      type="file"
      :id="'{{ $uuid }}'"
      @change="handleFileSelect($event)"
      :accept="'{{ $accept }}'"
      :multiple="multiple"
      :disabled="disabled || (multiple && files.length >= maxFiles)"
      {{ $attributes->wire('model') }}
      class="hidden"
    />

    <label
      :for="'{{ $uuid }}'"
      class="flex flex-col items-center justify-center px-6 py-8 cursor-pointer"
      :class="{ 'opacity-50 cursor-not-allowed': disabled || (multiple && files.length >= maxFiles) }"
    >
      <x-icon name="o-cloud-arrow-up" class="w-12 h-12 mb-3 text-gray-400 dark:text-gray-500" />
      <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">
        <span class="font-semibold">{{ __('Click to upload') }}</span>
        {{ __('or drag and drop') }}
      </p>
      <p class="text-xs text-gray-500 dark:text-gray-500">
        {{ __('PNG, JPG, WEBP up to') }} {{ number_format($maxSize / 1024, 1) }}MB
        @if($multiple)
          ({{ __('max') }} {{ $maxFiles }} {{ __('files') }})
        @endif
      </p>
    </label>

    {{-- Upload Progress --}}
    <div x-show="uploading" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-gray-800/90 rounded-lg">
      <div class="text-center">
        <div class="loading loading-spinner loading-lg text-primary"></div>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          {{ __('Processing') }} <span x-text="uploadProgress"></span>%
        </p>
      </div>
    </div>
  </div>

  {{-- Hint --}}
  @if($hint)
    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
      {{ $hint }}
    </div>
  @endif

  {{-- Error Messages --}}
  @error($errorBag)
  <div class="mt-2 text-sm text-red-600 dark:text-red-400">
    {{ $message }}
  </div>
  @enderror

  <div x-show="error" class="mt-2 text-sm text-red-600 dark:text-red-400" x-text="error"></div>

  {{-- Image Previews (Sortable List) --}}
  <div
    x-show="files.length > 0"
    class="mt-4 space-y-2"
    x-ref="sortableContainer"
  >
    <div class="flex items-center justify-between mb-2">
      <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
        <span x-text="files.length"></span>
        <span x-text="files.length === 1 ? '{{ __('image') }}' : '{{ __('images') }}'"></span>
      </p>
      @if($multiple)
        <button
          type="button"
          @click="clearAll"
          class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
        >
          {{ __('Clear all') }}
        </button>
      @endif
    </div>

    <template x-for="(file, index) in files" :key="file.id">
      <div
        class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg group hover:border-gray-300 dark:hover:border-gray-600 transition-all cursor-move"
        :data-index="index"
        draggable="true"
        @dragstart="handleDragStart($event, index)"
        @dragend="handleDragEnd"
        @dragover.prevent="handleDragOver($event, index)"
      >
        {{-- Preview Thumbnail --}}
        <div class="relative flex-shrink-0">
          <img
            :src="file.preview"
            :alt="file.name"
            src=""
            alt="Preview"
            class="object-cover w-16 h-16 rounded-lg"
          />
          <div class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/40 rounded-lg transition-colors">
            <button
              type="button"
              @click="previewImage(index)"
              class="opacity-0 group-hover:opacity-100 p-1.5 bg-white/90 rounded-full hover:bg-white transition-all"
            >
              <x-icon name="o-eye" class="w-4 h-4 text-gray-700" />
            </button>
          </div>
        </div>

        {{-- File Info --}}
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" x-text="file.name"></p>
          <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
            <span x-text="formatFileSize(file.size)"></span>
            <span>•</span>
            <span x-text="file.dimensions"></span>
            @if($multiple)
              <span>•</span>
              <span class="flex items-center gap-1">
                                <x-icon name="o-arrows-up-down" class="w-3 h-3" />
                                {{ __('Drag to reorder') }}
                            </span>
            @endif
          </div>

          {{-- Progress Bar --}}
          <div x-show="file.uploading" class="w-full mt-2 bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
            <div
              class="bg-primary h-1.5 rounded-full transition-all duration-300"
              :style="`width: ${file.progress}%`"
            ></div>
          </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="openCropModal(index)"
            class="p-2 text-gray-400 hover:text-primary dark:text-gray-500 dark:hover:text-primary transition-colors"
            title="{{ __('Crop/Resize') }}"
          >
            <x-icon name="o-scissors" class="w-4 h-4" />
          </button>
          <button
            type="button"
            @click="removeFile(index)"
            class="p-2 text-gray-400 hover:text-red-600 dark:text-gray-500 dark:hover:text-red-400 transition-colors"
            title="{{ __('Remove') }}"
          >
            <x-icon name="o-trash" class="w-4 h-4" />
          </button>
        </div>
      </div>
    </template>
  </div>

  {{-- Image Preview Modal --}}
  <div
    x-show="showPreview"
    @click="showPreview = false"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
  >
    <div @click.stop class="relative max-w-4xl max-h-[90vh]">
      <button
        @click="showPreview = false"
        class="absolute -top-10 right-0 p-2 text-white hover:text-gray-300"
      >
        <x-icon name="o-x-mark" class="w-6 h-6" />
      </button>
      <img
        :src="previewSrc"
        src=""
        class="max-w-full max-h-[85vh] rounded-lg shadow-2xl"
        alt="Preview"
      />
    </div>
  </div>

  {{-- Crop Modal --}}
  <div
    x-show="showCropModal"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
  >
    <div @click.stop class="w-full max-w-3xl bg-white dark:bg-gray-800 rounded-lg shadow-xl">
      <div class="p-6 border-b dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ __('Crop & Resize Image') }}
          </h3>
          <button @click="closeCropModal" class="text-gray-400 hover:text-gray-500">
            <x-icon name="o-x-mark" class="w-6 h-6" />
          </button>
        </div>
      </div>

      <div class="p-6">
        <div class="mb-4 overflow-hidden bg-gray-100 dark:bg-gray-900 rounded-lg" style="max-height: 400px;">
          <canvas x-ref="cropCanvas" class="max-w-full mx-auto"></canvas>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block mb-1 text-sm text-gray-700 dark:text-gray-300">{{ __('Width') }}</label>
            <input
              type="number"
              x-model.number="cropWidth"
              @input="updateCropPreview"
              class="w-full px-3 py-2 text-sm border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
          </div>
          <div>
            <label class="block mb-1 text-sm text-gray-700 dark:text-gray-300">{{ __('Height') }}</label>
            <input
              type="number"
              x-model.number="cropHeight"
              @input="updateCropPreview"
              class="w-full px-3 py-2 text-sm border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3 p-6 border-t dark:border-gray-700">
        <button
          @click="applyCrop"
          type="button"
          class="btn btn-primary btn-sm"
        >
          <x-icon name="o-check" class="w-4 h-4" />
          {{ __('Apply') }}
        </button>
        <button
          @click="closeCropModal"
          type="button"
          class="btn btn-ghost btn-sm"
        >
          {{ __('Cancel') }}
        </button>
      </div>
    </div>
  </div>
</div>

@script
<script>
  Alpine.data('imageUploader', (config) => ({
    files: [],
    dragOver: false,
    disabled: false,
    uploading: false,
    uploadProgress: 0,
    error: null,
    showPreview: false,
    previewSrc: '',
    showCropModal: false,
    cropIndex: null,
    cropWidth: 0,
    cropHeight: 0,
    cropImageData: null,
    draggedIndex: null,
    multiple: config.multiple,
    maxSize: config.maxSize,
    maxFiles: config.maxFiles,
    cropAspectRatio: config.cropAspectRatio,
    maxWidth: config.maxWidth,
    maxHeight: config.maxHeight,
    compressionQuality: config.compressionQuality,

    init() {
      // Initialize with existing images if provided

      if (config.existingImages && config.existingImages.length > 0) {
        this.files = config.existingImages.map((img, index) => ({
          id: `existing-${index}`,
          name: img.name || 'Image',
          preview: img.url,
          size: img.size || 0,
          dimensions: img.dimensions || '',
          existing: true,
          uploading: false,
          progress: 100
        }));
      }

      // Make sortable container
      this.$nextTick(() => {
        this.initSortable();
      });
    },

    initSortable() {
      // Sortable functionality is handled by drag events
    },

    handleFileSelect(event) {
      const selectedFiles = Array.from(event.target.files);
      this.processFiles(selectedFiles);
    },

    handleDrop(event) {
      this.dragOver = false;
      const droppedFiles = Array.from(event.dataTransfer.files);
      this.processFiles(droppedFiles);
    },

    async processFiles(selectedFiles) {
      this.error = null;

      // Filter image files
      const imageFiles = selectedFiles.filter(file => file.type.startsWith('image/'));

      if (imageFiles.length !== selectedFiles.length) {
        this.error = '{{ __("Some files were skipped (only images allowed)") }}';
      }

      // Check max files limit
      if (this.multiple) {
        const remaining = this.maxFiles - this.files.length;
        if (imageFiles.length > remaining) {
          this.error = `{{ __("Maximum") }} ${this.maxFiles} {{ __("files allowed") }}`;
          imageFiles.splice(remaining);
        }
      } else {
        imageFiles.splice(1); // Keep only first file for single upload
      }

      // Process each file
      for (const file of imageFiles) {
        // Check file size
        if (file.size > this.maxSize * 1024) {
          this.error = `${file.name}: {{ __("File too large") }} (max ${this.maxSize / 1024}MB)`;
          continue;
        }

        await this.addFile(file);
      }
    },

    async addFile(file) {
      const fileId = `file-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

      const fileObj = {
        id: fileId,
        name: file.name,
        size: file.size,
        preview: '',
        dimensions: '',
        file: file,
        uploading: true,
        progress: 0
      };

      // Add to files array
      if (this.multiple) {
        this.files.push(fileObj);
      } else {
        this.files = [fileObj];
      }

      // Read and compress image
      try {
        const result = await this.compressImage(file);
        const index = this.files.findIndex(f => f.id === fileId);

        if (index !== -1) {
          this.files[index].preview = result.preview;
          this.files[index].dimensions = result.dimensions;
          this.files[index].file = result.file;
          this.files[index].uploading = false;
          this.files[index].progress = 100;
        }
      } catch (error) {
        console.error('Error processing image:', error);
        this.removeFileById(fileId);
        this.error = '{{ __("Error processing image") }}';
      }
    },

    compressImage(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();

        reader.onload = (e) => {
          const img = new Image();

          img.onload = () => {
            let width = img.width;
            let height = img.height;
            const dimensions = `${width} × ${height}`;

            // Resize if larger than max dimensions
            if (width > this.maxWidth || height > this.maxHeight) {
              const ratio = Math.min(this.maxWidth / width, this.maxHeight / height);
              width = Math.floor(width * ratio);
              height = Math.floor(height * ratio);
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            canvas.toBlob((blob) => {
              const compressedFile = new File([blob], file.name, {
                type: file.type,
                lastModified: Date.now()
              });

              resolve({
                preview: canvas.toDataURL(file.type, this.compressionQuality),
                dimensions: dimensions,
                file: compressedFile
              });
            }, file.type, this.compressionQuality);
          };

          img.onerror = reject;
          img.src = e.target.result;
        };

        reader.onerror = reject;
        reader.readAsDataURL(file);
      });
    },

    removeFile(index) {
      this.files.splice(index, 1);
    },

    removeFileById(id) {
      const index = this.files.findIndex(f => f.id === id);
      if (index !== -1) {
        this.files.splice(index, 1);
      }
    },

    clearAll() {
      if (confirm('{{ __("Remove all images?") }}')) {
        this.files = [];
      }
    },

    previewImage(index) {
      this.previewSrc = this.files[index].preview;
      this.showPreview = true;
    },

    openCropModal(index) {
      this.cropIndex = index;
      const file = this.files[index];

      const img = new Image();
      img.onload = () => {
        this.cropWidth = img.width;
        this.cropHeight = img.height;
        this.cropImageData = img;
        this.showCropModal = true;

        this.$nextTick(() => {
          this.updateCropPreview();
        });
      };
      img.src = file.preview;
    },

    updateCropPreview() {
      if (!this.$refs.cropCanvas || !this.cropImageData) return;

      const canvas = this.$refs.cropCanvas;
      const ctx = canvas.getContext('2d');

      canvas.width = this.cropWidth;
      canvas.height = this.cropHeight;

      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(this.cropImageData, 0, 0, this.cropWidth, this.cropHeight);
    },

    async applyCrop() {
      const canvas = this.$refs.cropCanvas;
      const file = this.files[this.cropIndex];

      canvas.toBlob((blob) => {
        const croppedFile = new File([blob], file.name, {
          type: file.file.type,
          lastModified: Date.now()
        });

        this.files[this.cropIndex].preview = canvas.toDataURL(file.file.type, this.compressionQuality);
        this.files[this.cropIndex].dimensions = `${this.cropWidth} × ${this.cropHeight}`;
        this.files[this.cropIndex].file = croppedFile;
        this.files[this.cropIndex].size = croppedFile.size;

        this.closeCropModal();
      }, file.file.type, this.compressionQuality);
    },

    closeCropModal() {
      this.showCropModal = false;
      this.cropIndex = null;
      this.cropImageData = null;
    },

    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    },

    // Drag and drop sorting
    handleDragStart(event, index) {
      this.draggedIndex = index;
      event.dataTransfer.effectAllowed = 'move';
      event.target.style.opacity = '0.5';
    },

    handleDragEnd(event) {
      event.target.style.opacity = '1';
    },

    handleDragOver(event, index) {
      if (this.draggedIndex === null || this.draggedIndex === index) return;

      // Swap items
      const draggedItem = this.files[this.draggedIndex];
      this.files.splice(this.draggedIndex, 1);
      this.files.splice(index, 0, draggedItem);
      this.draggedIndex = index;
    }
  }));
</script>
@endscript

