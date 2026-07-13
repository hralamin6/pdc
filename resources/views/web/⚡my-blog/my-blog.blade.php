<div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-16">

    {{-- Hero Header --}}
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white py-16 relative overflow-hidden">
        <div class="absolute top-0 right-1/4 w-72 h-72 bg-primary/10 rounded-full blur-[120px]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex items-center gap-2 text-rose-400 font-bold text-xs uppercase tracking-widest mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/20 text-primary-focus text-xs font-black uppercase tracking-widest">
                    <x-icon name="o-pencil-square" class="w-3.5 h-3.5" />
                    {{ __('Writing Desk') }}
                </span>
                <span>/</span>
                <span>{{ __('Studio') }}</span>
            </div>
            <h1 class="text-3xl md:text-5xl font-black mb-4 tracking-tight leading-tight">{{ __('My Blog Studio') }}</h1>
            <p class="text-white/60 text-sm md:text-base max-w-2xl leading-relaxed">
                {{ __('Draft, design, publish, and manage your articles directly from your user dashboard.') }}
            </p>
        </div>
    </div>

    {{-- Overlap Container --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- LEFT COLUMN: Post Feed & Management --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Search & Controls Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <div class="relative w-full sm:max-w-md">
                        <x-icon name="o-magnifying-glass" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search your articles...') }}"
                            class="w-full pl-10 pr-4 py-2.5 text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                    </div>

                    <button wire:click="openCreateModal" class="w-full sm:w-auto btn btn-primary btn-sm rounded-xl px-5 font-bold shadow-lg shadow-primary/25 hover:scale-105 transition-transform flex items-center gap-2 justify-center">
                        <x-icon name="o-plus" class="w-4 h-4" /> {{ __('Create New Article') }}
                    </button>
                </div>

                {{-- Articles List Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
                    <h2 class="text-lg font-black text-slate-900 dark:text-white mb-6 uppercase tracking-wider text-xs border-b border-slate-100 dark:border-slate-800 pb-3">
                        {{ __('My Articles') }}
                    </h2>

                    <div class="space-y-6">
                        @forelse($this->myPosts as $post)
                            <div wire:key="post-{{ $post->id }}" class="group flex flex-col md:flex-row gap-5 p-4 rounded-2xl hover:bg-slate-50/50 dark:hover:bg-slate-950/20 border border-transparent hover:border-slate-100 dark:hover:border-slate-850 transition-all duration-300">
                                
                                {{-- Post Cover --}}
                                <div class="w-full md:w-44 h-28 shrink-0 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-850 relative">
                                    @if($post->getFirstMediaUrl('featured_image'))
                                        <img src="{{ $post->getFirstMediaUrl('featured_image') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $post->title }}" />
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-indigo-50/50 to-slate-100 dark:from-slate-800 dark:to-slate-900 text-slate-300 dark:text-slate-700">
                                            <x-icon name="o-newspaper" class="w-8 h-8 mb-1" />
                                            <span class="text-[9px] uppercase font-bold text-slate-400">{{ __('No Cover') }}</span>
                                        </div>
                                    @endif

                                    <div class="absolute top-2 right-2">
                                        <span class="px-2 py-0.5 text-[8px] font-black uppercase rounded shadow-sm {{ $post->status === 'published' ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white' }}">
                                            {{ $post->status_label }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Post Details --}}
                                <div class="flex-grow flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1.5">
                                            @if($post->category)
                                                <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-400 rounded-md">
                                                    {{ $post->category->name }}
                                                </span>
                                            @endif
                                            <span class="text-[10px] font-bold text-slate-400">
                                                {{ $post->published_at ? $post->published_at->format('M d, Y') : __('Draft') }}
                                            </span>
                                        </div>

                                        <h3 class="font-bold text-slate-800 dark:text-slate-100 leading-snug line-clamp-1 mb-1 group-hover:text-primary transition-colors">
                                            {{ $post->title }}
                                        </h3>

                                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                                            {{ $post->excerpt }}
                                        </p>
                                    </div>

                                    {{-- Post Actions --}}
                                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100 dark:border-slate-850/80">
                                        <div class="flex items-center gap-3">
                                            <span class="flex items-center gap-1 text-[10px] font-bold text-slate-400" title="{{ __('Page Views') }}">
                                                <x-icon name="o-eye" class="w-3.5 h-3.5" />
                                                {{ number_format($post->views_count) }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('web.post', $post->slug) }}" target="_blank" class="btn btn-ghost btn-xs text-slate-650 dark:text-slate-450 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg p-1" title="{{ __('View Article') }}">
                                                <x-icon name="o-arrow-top-right-on-square" class="w-4 h-4" />
                                            </a>
                                            
                                            <button wire:click="openEditModal({{ $post->id }})" class="btn btn-ghost btn-xs text-primary hover:bg-primary/5 rounded-lg px-2" title="{{ __('Edit') }}">
                                                <x-icon name="o-pencil" class="w-3 h-3" /> {{ __('Edit') }}
                                            </button>

                                            <button wire:click="deletePost({{ $post->id }})" wire:confirm="{{ __('Are you sure you want to delete this article?') }}" class="btn btn-ghost btn-circle btn-xs text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20" title="{{ __('Delete') }}">
                                                <x-icon name="o-trash" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @empty
                            <div class="text-center py-16 bg-slate-50/50 dark:bg-slate-950/20 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-8 shadow-sm">
                                <x-icon name="o-newspaper" class="w-12 h-12 text-slate-300 dark:text-slate-700 mx-auto mb-3" />
                                <h3 class="text-base font-black text-slate-800 dark:text-slate-200 mb-1">{{ __('No Articles Found') }}</h3>
                                <p class="text-xs text-slate-450 dark:text-slate-500 max-w-xs mx-auto mb-4">{{ __('Start sharing your insights with the community by writing your first article.') }}</p>
                                <button wire:click="openCreateModal" class="btn btn-primary btn-xs rounded-xl px-4 font-bold shadow shadow-primary/25">
                                    <x-icon name="o-plus" class="w-3.5 h-3.5" /> {{ __('Create New Article') }}
                                </button>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-8 border-t border-slate-100 dark:border-slate-800 pt-4">
                        {{ $this->myPosts->links() }}
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: Profile Stats & Design Guidelines --}}
            <div class="space-y-6">
                
                {{-- Quick Stats Panel --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
                    <h3 class="font-black text-slate-900 dark:text-white text-base mb-4 flex items-center gap-2 uppercase tracking-wider text-xs">
                        <x-icon name="o-chart-bar" class="w-4 h-4 text-primary" />
                        {{ __('Analytics Overview') }}
                    </h3>

                    @php
                        $postsQuery = \App\Models\Post::where('user_id', auth()->id());
                        $totalCount = $postsQuery->count();
                        $publishedCount = $postsQuery->clone()->whereNotNull('published_at')->count();
                        $draftCount = $totalCount - $publishedCount;
                        $totalViews = $postsQuery->clone()->sum('views_count');
                    @endphp

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-2xl border border-slate-200/50 dark:border-slate-850">
                                <span class="text-[10px] font-black uppercase text-slate-400 block mb-1">{{ __('Total Articles') }}</span>
                                <span class="text-2xl font-black text-slate-800 dark:text-white">{{ $totalCount }}</span>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-2xl border border-slate-200/50 dark:border-slate-850">
                                <span class="text-[10px] font-black uppercase text-slate-400 block mb-1">{{ __('Total Reads') }}</span>
                                <span class="text-2xl font-black text-primary">{{ number_format($totalViews) }}</span>
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-2xl border border-slate-200/50 dark:border-slate-850 space-y-3">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">{{ __('Published') }}</span>
                                <span class="font-black text-emerald-600 dark:text-emerald-400">{{ $publishedCount }}</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $totalCount ? ($publishedCount / $totalCount) * 100 : 0 }}%"></div>
                            </div>
                            
                            <div class="flex justify-between items-center text-xs pt-1">
                                <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">{{ __('Drafts') }}</span>
                                <span class="font-black text-amber-500">{{ $draftCount }}</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-amber-500 h-full rounded-full" style="width: {{ $totalCount ? ($draftCount / $totalCount) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Author Guidelines Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
                    <h3 class="font-black text-slate-900 dark:text-white text-base mb-4 flex items-center gap-2 uppercase tracking-wider text-xs">
                        <x-icon name="o-light-bulb" class="w-4 h-4 text-amber-500" />
                        {{ __('Content Guidelines') }}
                    </h3>

                    <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-400 leading-relaxed font-semibold">
                        <li class="flex gap-2">
                            <span class="text-primary font-black">1.</span>
                            <span>{{ __('Write clearly with focused paragraphs. You can use standard Markdown tags to format headings, lists, and code blocks.') }}</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary font-black">2.</span>
                            <span>{{ __('Set short, compelling meta descriptions and target keywords to help your posts rank on search engines.') }}</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary font-black">3.</span>
                            <span>{{ __('Generate custom editorial banners with our AI Studio or upload high-resolution landscape images.') }}</span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>
    </div>

    {{-- ==================== CREATE / EDIT MODAL ==================== --}}
    <x-modal wire:model="editModal" title="{{ $editingPost ? __('Edit Article') : __('New Article') }}" class="backdrop-blur-sm" box-class="w-11/12 max-w-4xl p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800 shadow-2xl" persistent>
        <div class="space-y-6">
            
            {{-- Title & Category --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">{{ __('Article Title *') }}</label>
                    <input type="text" wire:model="postTitle" placeholder="{{ __('Type article title...') }}"
                        class="w-full px-4 py-3 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                    @error('postTitle') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">{{ __('Category *') }}</label>
                    <select wire:model="postCategoryId" class="w-full px-4 py-3 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350">
                        <option value="">{{ __('Select Category') }}</option>
                        @foreach($this->categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('postCategoryId') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Cover Image & Excerpt --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Cover Image --}}
                <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-2xl border border-slate-200/50 dark:border-slate-850 space-y-4">
                    <x-file :label="__('Cover Image')" wire:model="featuredImageFile" accept="image/*" crop-after-change class="w-full">
                        <div x-data="{ hasImage: false }" class="w-full h-32 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-slate-150 dark:bg-slate-900 flex items-center justify-center cursor-pointer group shadow-inner relative">
                            <img src="{{ $this->getFeaturedImagePreviewUrl() ?? $existingFeaturedImageUrl ?? 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' }}" 
                                 class="w-full h-full object-cover"
                                 @load="hasImage = !$event.target.src.includes('data:image/gif')"
                                 x-show="hasImage" />
                            
                            <div x-show="!hasImage" class="text-center p-4">
                                <x-icon name="o-photo" class="w-8 h-8 mx-auto text-slate-400 mb-1 group-hover:scale-105 transition-transform" />
                                <span class="text-xs font-bold text-slate-650 dark:text-slate-350 block">{{ __('Click to Upload Cover') }}</span>
                                <span class="text-[10px] text-slate-500 block mt-0.5">{{ __('JPG, PNG, WebP up to 2MB') }}</span>
                            </div>
                        </div>
                    </x-file>
                    
                    <x-input :label="__('Cover Image URL')" wire:model="featuredImageUrl" type="url" :placeholder="__('https://.../image.jpg')" class="rounded-xl bg-white dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-xs" />
                    @error('featuredImageUrl') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Excerpt & Settings --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">{{ __('Excerpt') }}</label>
                        <textarea wire:model="postExcerpt" rows="2" placeholder="{{ __('Short description (optional)...') }}" class="w-full px-4 py-3 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350 resize-none"></textarea>
                    </div>
                    
                    <div class="flex items-center bg-slate-50 dark:bg-slate-950 px-4 py-3 rounded-xl border border-slate-200/50 dark:border-slate-850">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" wire:model="postIsPublished" class="checkbox checkbox-primary checkbox-sm rounded-md" />
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('Publish Immediately') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="space-y-1">
                <x-editor wire:model="postContent" :label="__('Article Body *')" :config="config('editor.default')" />
                @error('postContent') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- Simple SEO --}}
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-3 uppercase tracking-wider">{{ __('SEO Meta (Optional)') }}</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" wire:model="postMetaTitle" placeholder="{{ __('Meta Title') }}" class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                    <input type="text" wire:model="postMetaKeywords" placeholder="{{ __('Keywords (comma separated)') }}" class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                </div>
            </div>

        </div>

        <x-slot:actions>
            <button type="button" wire:click="$set('editModal', false)" class="btn btn-ghost btn-sm rounded-xl font-bold">{{ __('Cancel') }}</button>
            <button type="button" wire:click="savePost" class="btn btn-primary btn-sm rounded-xl font-bold shadow-lg shadow-primary/25" spinner="savePost">
                {{ $editingPost ? __('Update Article') : __('Publish/Draft Article') }}
            </button>
        </x-slot:actions>
    </x-modal>

@assets
    <script src="{{ asset('tiny.js') }}"></script>
@endassets
</div>

