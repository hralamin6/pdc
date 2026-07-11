<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        
        {{-- Header Section --}}
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-4">{{ __('Community Showcase') }}</h1>
            <p class="text-lg text-slate-600 dark:text-slate-400">{{ __('Explore the beautiful memories and achievements of our community.') }}</p>
        </div>
        
        {{-- Search --}}
        <div class="max-w-md mx-auto mb-12">
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search albums...') }}" class="w-full pl-12 pr-4 py-3 rounded-2xl border-2 border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-cyan-500 focus:ring-0 transition-colors">
                <x-icon name="o-magnifying-glass" class="absolute left-4 top-3.5 w-5 h-5 text-slate-400" />
            </div>
        </div>

        {{-- Bento Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($this->albums as $album)
                <div class="group relative rounded-[2rem] overflow-hidden bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-800 hover:border-cyan-500 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 {{ $loop->iteration % 5 == 0 ? 'md:col-span-2 md:row-span-2' : '' }}">
                    <div class="h-64 {{ $loop->iteration % 5 == 0 ? 'md:h-[32rem]' : '' }} relative">
                        @if($album->cover_url)
                            <img src="{{ $album->cover_url }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="{{ $album->title }}">
                        @else
                            <div class="w-full h-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center">
                                <x-icon name="o-photo" class="w-12 h-12 text-slate-400" />
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
                        
                        <div class="absolute bottom-0 left-0 right-0 p-6">
                            <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black text-white uppercase tracking-widest mb-3 border border-white/20">{{ $album->category ?? __('General') }}</span>
                            <h3 class="text-2xl font-black text-white mb-2 leading-tight">{{ $album->title }}</h3>
                            <p class="text-slate-300 text-sm line-clamp-2">{{ $album->description }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-20">
                    <x-icon name="o-photo" class="w-16 h-16 mx-auto text-slate-400 mb-4" />
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-2">{{ __('No albums found') }}</h3>
                    <p class="text-slate-500">{{ __('Check back later for more community memories.') }}</p>
                </div>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $this->albums->links() }}
        </div>
    </div>
</div>
