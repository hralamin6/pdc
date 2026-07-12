<div class="h-[calc(100vh-80px)] -mt-4 flex flex-col">
    {{-- Toolbar --}}
    <div class="flex items-center justify-between p-3 bg-base-200 border-b border-base-content/10 shrink-0">
        <div class="flex items-center gap-3">
            <x-button icon="o-arrow-left" class="btn-ghost btn-sm" link="{{ route('app.book-details', $book->slug) }}" wire:navigate />
            <h1 class="font-bold text-sm md:text-base line-clamp-1">{{ $book->title }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <x-button icon="o-arrow-down-tray" class="btn-primary btn-sm" link="{{ $this->resolvedPdfUrl }}" external download tooltip="{{ __('Download PDF') }}" />
        </div>
    </div>

    {{-- Reader Container --}}
    <div class="flex-grow w-full bg-base-300 relative overflow-hidden">
        {{-- Desktop Native PDF --}}
        <object data="{{ $this->resolvedPdfUrl }}#toolbar=1&navpanes=0" type="application/pdf" class="w-full h-full hidden md:block">
            <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center">
                <x-icon name="o-exclamation-triangle" class="w-12 h-12 text-warning mb-2" />
                <p>{{ __("Your browser doesn't support built-in PDFs or this is an external link.") }}</p>
                <a href="{{ $this->resolvedPdfUrl }}" class="btn btn-primary mt-4" target="_blank" download>{{ __('Open / Download PDF instead') }}</a>
            </div>
        </object>

        {{-- Mobile Google Viewer Fallback (Since mobile browsers don't natively embed PDFs in iframes well) --}}
        <iframe src="https://docs.google.com/gview?url={{ urlencode(url($this->resolvedPdfUrl)) }}&embedded=true" class="w-full h-full border-none md:hidden" frameborder="0"></iframe>
    </div>
</div>
