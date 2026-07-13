<div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-16" x-data="{ lightboxOpen: false, lightboxUrl: '' }">

    {{-- Hero Header --}}
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white py-16 relative overflow-hidden">
        <div class="absolute top-0 right-1/4 w-72 h-72 bg-emerald-500/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-primary/10 rounded-full blur-[140px]"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex items-center gap-2 text-primary font-bold text-xs uppercase tracking-widest mb-3">
                <a href="{{ route('web.showcase') }}" wire:navigate class="hover:underline">{{ __('Showcase') }}</a>
                <span>/</span>
                <span>{{ $album->category ?? __('Gallery') }}</span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black mb-4 tracking-tight leading-tight">{{ $album->title }}</h1>
            
            @if($album->description)
                <p class="text-white/70 text-sm md:text-base max-w-2xl leading-relaxed whitespace-pre-line">
                    {{ $album->description }}
                </p>
            @endif
        </div>
    </div>

    {{-- Overlapping Content Area --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
        
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
            
            <div class="flex items-center justify-between mb-8">
                <h3 class="font-black text-slate-800 dark:text-white text-xl flex items-center gap-2">
                    <x-icon name="o-photo" class="w-6 h-6 text-primary" />
                    {{ __('Album Photos') }}
                </h3>
                <div class="text-sm font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-4 py-1.5 rounded-full">
                    {{ $album->getMedia('gallery_images')->count() }} {{ __('Items') }}
                </div>
            </div>

            @if($album->getMedia('gallery_images')->isEmpty())
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <x-icon name="o-camera" class="w-10 h-10 text-slate-400" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">{{ __('No Photos Yet') }}</h3>
                    <p class="text-slate-500 text-sm mt-1">{{ __('This album is currently empty.') }}</p>
                </div>
            @else
                <div class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
                    @foreach($album->getMedia('gallery_images') as $media)
                        <div 
                            class="relative group rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 break-inside-avoid shadow-sm hover:shadow-xl transition-all duration-300 cursor-pointer border border-slate-200/50 dark:border-slate-700/50"
                            @click="lightboxUrl = '{{ $media->getUrl('large') ?: $media->getUrl() }}'; lightboxOpen = true"
                        >
                            <img 
                                src="{{ $media->getUrl('large') ?: $media->getUrl() }}" 
                                alt="{{ $album->title }}" 
                                class="w-full h-auto object-cover transform group-hover:scale-105 transition-transform duration-500 ease-out"
                                loading="lazy"
                            />
                            
                            {{-- Hover Overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-black/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-4">
                                <div class="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                    <x-icon name="o-arrows-pointing-out" class="w-6 h-6 text-white drop-shadow-md mx-auto mb-2" />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

    </div>

    {{-- Alpine Lightbox Modal --}}
    <div 
        x-show="lightboxOpen" 
        style="display: none;"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/95 backdrop-blur-sm p-4 sm:p-8"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <button @click="lightboxOpen = false" class="absolute top-6 right-6 text-white/50 hover:text-white transition-colors bg-black/20 hover:bg-black/40 rounded-full p-2 backdrop-blur-md">
            <x-icon name="o-x-mark" class="w-6 h-6" />
        </button>

        <img 
            :src="lightboxUrl" 
            class="max-w-full max-h-full object-contain rounded-lg shadow-2xl ring-1 ring-white/10"
            @click.outside="lightboxOpen = false"
            x-transition:enter="transition ease-out duration-300 delay-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
        >
    </div>

</div>
