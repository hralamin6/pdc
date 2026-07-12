@section('title', $this->post->title)
@section('description', Str::limit(strip_tags($this->post->content), 333))
@section('image', $this->post->getFirstMediaUrl('featured_image'))

<div>
    {{-- Reading Scroll Progress Indicator (Client-side Alpine.js) --}}
    <div x-data="{ scrollPercent: 0 }" 
         @scroll.window="scrollPercent = (($el.parentElement.scrollTop || document.documentElement.scrollTop) / (document.documentElement.scrollHeight - document.documentElement.clientHeight)) * 100" 
         class="fixed top-0 left-0 w-full h-1 bg-slate-200/30 dark:bg-slate-800/30 z-50">
        <div class="bg-primary h-full transition-all duration-75" :style="`width: ${scrollPercent}%`"></div>
    </div>

    <div class="bg-slate-50/40 dark:bg-slate-950/40 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Breadcrumb --}}
            <nav class="flex flex-wrap items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-400">
                <a wire:navigate href="{{ route('web.home') }}" class="hover:text-primary transition-colors">{{ __('Home') }}</a>
                <span>/</span>
                <a wire:navigate href="{{ route('web.posts') }}" class="hover:text-primary transition-colors">{{ __('Articles') }}</a>
                @if($this->post->category)
                    <span>/</span>
                    <a wire:navigate href="{{ route('web.posts') }}?category={{ $this->post->category->id }}" class="hover:text-primary transition-colors">
                        {{ $this->post->category->name }}
                    </a>
                @endif
            </nav>

            {{-- Main Layout Grid: Left = Post (70%), Right = Sidebar (30%) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-6">
                
                {{-- Left Column: Article Body & Comments --}}
                <div class="lg:col-span-8 space-y-8">
                    
                    {{-- Main Article Card --}}
                    <article class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/80 p-5 sm:p-8 shadow-sm space-y-6">
                        
                        {{-- Category badge & meta --}}
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            @if($this->post->category)
                                <a wire:navigate href="{{ route('web.posts') }}?category={{ $this->post->category->id }}" class="inline-flex items-center px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-black uppercase tracking-widest">
                                    {{ $this->post->category->name }}
                                </a>
                            @endif

                            <div class="flex items-center gap-2 text-xs text-slate-400 font-bold">
                                <span>{{ $this->post->published_at?->format('M d, Y') }}</span>
                                <span>•</span>
                                <span>{{ ceil(str_word_count(strip_tags($this->post->content)) / 200) }} {{ __('min read') }}</span>
                            </div>
                        </div>

                        {{-- Article Title --}}
                        <h1 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white leading-tight tracking-tight">
                            {{ $this->post->title }}
                        </h1>

                        {{-- Author compact top header --}}
                        <div class="flex items-center gap-3 py-3 border-y border-slate-100 dark:border-slate-800/60">
                            <a wire:navigate href="{{ route('web.user', $this->post->user->username ?? $this->post->user->id) }}" class="w-10 h-10 rounded-full overflow-hidden bg-primary/10 flex items-center justify-center text-primary text-sm font-bold shrink-0">
                                @if($this->post->user->avatar_url)
                                    <img src="{{ $this->post->user->avatar_url }}" alt="{{ $this->post->user->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ substr($this->post->user->name ?? 'A', 0, 1) }}
                                @endif
                            </a>
                            <div>
                                <a wire:navigate href="{{ route('web.user', $this->post->user->username ?? $this->post->user->id) }}" class="text-sm font-bold text-slate-800 dark:text-slate-200 hover:text-primary transition-colors block">
                                    {{ $this->post->user->name ?? __('Anonymous') }}
                                </a>
                                <span class="text-[10px] text-slate-400 block font-bold uppercase">{{ __('Article Author') }}</span>
                            </div>
                        </div>

                        {{-- Featured Image Banner --}}
                        @if($this->post->getFirstMediaUrl('featured_image'))
                            <div class="rounded-2xl overflow-hidden border border-slate-200/30 dark:border-slate-850 shadow-md">
                                <img src="{{ $this->post->getFirstMediaUrl('featured_image') }}" alt="{{ $this->post->title }}" class="w-full h-auto max-h-96 object-cover">
                            </div>
                        @endif

                        {{-- Content --}}
                        <div class="prose prose-slate dark:prose-invert max-w-none text-slate-700 dark:text-slate-300 leading-relaxed text-sm sm:text-base">
                            {!! Str::markdown($this->post->content) !!}
                        </div>

                        {{-- Rich Reactions System --}}
                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between gap-4">
                            <div x-data="{ open: false }" class="relative">
                                {{-- Reaction Popover Panel --}}
                                <div x-cloak x-show="open" @click.outside="open = false" 
                                     class="absolute bottom-full left-0 mb-2 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/90 shadow-xl rounded-full p-1.5 flex gap-3 z-30 transition-all">
                                    <button @click="open = false" wire:click="react('like')" class="hover:scale-125 transition text-2xl" title="{{ __('Like') }}">👍</button>
                                    <button @click="open = false" wire:click="react('love')" class="hover:scale-125 transition text-2xl" title="{{ __('Love') }}">❤️</button>
                                    <button @click="open = false" wire:click="react('insightful')" class="hover:scale-125 transition text-2xl" title="{{ __('Insightful') }}">💡</button>
                                    <button @click="open = false" wire:click="react('inspiring')" class="hover:scale-125 transition text-2xl" title="{{ __('Inspiring') }}">🌟</button>
                                </div>
                                
                                {{-- Main Trigger Button --}}
                                @php $userReact = $this->userReactionType; @endphp
                                <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border transition {{ $userReact ? 'bg-primary/10 border-primary/30 text-primary' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900' }}">
                                    <span class="text-lg">
                                        @if($userReact === 'like') 👍
                                        @elseif($userReact === 'love') ❤️
                                        @elseif($userReact === 'insightful') 💡
                                        @elseif($userReact === 'inspiring') 🌟
                                        @else 👍
                                        @endif
                                    </span>
                                    <span class="text-xs font-black uppercase tracking-wider">
                                        @if($userReact)
                                            {{ ucfirst($userReact) }}
                                        @else
                                            {{ __('React') }}
                                        @endif
                                    </span>
                                </button>
                            </div>

                            {{-- Summary Counter (Option A) --}}
                            @php $reactCounts = $this->reactionCounts; @endphp
                            @if($reactCounts['total'] > 0)
                                <div class="flex items-center gap-2.5 bg-slate-50 dark:bg-slate-950/45 px-3.5 py-1.5 rounded-full border border-slate-200/50 dark:border-slate-800/60 shadow-sm">
                                    <div class="flex -space-x-1.5">
                                        @if($reactCounts['like'] > 0) <span class="text-sm select-none" title="{{ __('Like') }}">👍</span> @endif
                                        @if($reactCounts['love'] > 0) <span class="text-sm select-none" title="{{ __('Love') }}">❤️</span> @endif
                                        @if($reactCounts['insightful'] > 0) <span class="text-sm select-none" title="{{ __('Insightful') }}">💡</span> @endif
                                        @if($reactCounts['inspiring'] > 0) <span class="text-sm select-none" title="{{ __('Inspiring') }}">🌟</span> @endif
                                    </div>
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                                        {{ $reactCounts['total'] }} {{ trans_choice('reaction|reactions', $reactCounts['total']) }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Keywords tag cloud --}}
                        @if($this->post->meta_keywords)
                            <div class="pt-4 border-t border-slate-100 dark:border-slate-800/65">
                                <h4 class="text-xs font-black uppercase text-slate-400 tracking-wider mb-2.5">{{ __('Article Tags') }}</h4>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach(explode(',', $this->post->meta_keywords) as $keyword)
                                        <a wire:navigate href="{{ route('web.posts') }}?tag={{ urlencode(trim($keyword)) }}" class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800/70 text-slate-600 dark:text-slate-400 hover:border-slate-350 dark:hover:border-slate-700 transition">
                                            #{{ trim($keyword) }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </article>

                    {{-- Detailed Author Card (Option A) --}}
                    @php $author = $this->post->user; @endphp
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/80 p-5 sm:p-8 shadow-sm flex flex-col sm:flex-row gap-5 items-start">
                        <a wire:navigate href="{{ route('web.user', $author->username ?? $author->id) }}" class="w-16 h-16 rounded-full overflow-hidden bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white text-2xl font-black shrink-0 hover:scale-105 transition duration-300">
                            @if($author->avatar_url)
                                <img src="{{ $author->avatar_url }}" alt="{{ $author->name }}" class="w-full h-full object-cover">
                            @else
                                {{ substr($author->name ?? 'A', 0, 1) }}
                            @endif
                        </a>
                        <div class="flex-grow min-w-0">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <a wire:navigate href="{{ route('web.user', $author->username ?? $author->id) }}" class="font-black text-lg text-slate-900 dark:text-white hover:text-primary transition-colors">
                                        {{ $author->name }}
                                    </a>
                                    <span class="text-xs text-slate-400 block mt-0.5 font-bold uppercase tracking-wider">{{ __('Article Contributor') }}</span>
                                </div>
                                
                                {{-- Social links --}}
                                @if($author->detail)
                                    <div class="flex items-center gap-2">
                                        @if($author->detail->website)
                                            <a href="{{ $author->detail->website }}" target="_blank" rel="noopener" class="w-7 h-7 rounded-lg border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-400 hover:text-primary hover:border-primary transition-colors" title="{{ __('Website') }}"><x-icon name="o-globe-alt" class="w-4 h-4" /></a>
                                        @endif
                                        @if($author->detail->facebook)
                                            <a href="{{ $author->detail->facebook }}" target="_blank" rel="noopener" class="w-7 h-7 rounded-lg border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-400 hover:text-primary hover:border-primary transition-colors" title="{{ __('Facebook') }}"><x-icon name="o-link" class="w-4 h-4" /></a>
                                        @endif
                                        @if($author->detail->twitter)
                                            <a href="{{ $author->detail->twitter }}" target="_blank" rel="noopener" class="w-7 h-7 rounded-lg border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-400 hover:text-primary hover:border-primary transition-colors" title="{{ __('Twitter') }}"><x-icon name="o-link" class="w-4 h-4" /></a>
                                        @endif
                                        @if($author->detail->linkedin)
                                            <a href="{{ $author->detail->linkedin }}" target="_blank" rel="noopener" class="w-7 h-7 rounded-lg border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-400 hover:text-primary hover:border-primary transition-colors" title="{{ __('LinkedIn') }}"><x-icon name="o-link" class="w-4 h-4" /></a>
                                        @endif
                                        @if($author->detail->github)
                                            <a href="{{ $author->detail->github }}" target="_blank" rel="noopener" class="w-7 h-7 rounded-lg border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-400 hover:text-primary hover:border-primary transition-colors" title="{{ __('GitHub') }}"><x-icon name="o-link" class="w-4 h-4" /></a>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-3 leading-relaxed">
                                {{ $author->detail->bio ?? __('A dedicated member of the PSTU Dawah community contributing valuable reflections and insights.') }}
                            </p>

                            <div class="flex items-center gap-4 mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-400">
                                <span class="flex items-center gap-1.5 font-bold uppercase">
                                    <x-icon name="o-document-text" class="w-4 h-4" />
                                    {{ $author->posts()->count() }} {{ __('Articles Written') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Threaded Comments Section (Option B) --}}
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/60 dark:border-slate-800/80 p-5 sm:p-8 shadow-sm space-y-6">
                        <h3 class="font-black text-slate-900 dark:text-white text-lg flex items-center gap-2">
                            <x-icon name="o-chat-bubble-left-right" class="w-5 h-5 text-primary" />
                            {{ __('Comments & Discussion') }}
                            <span class="badge badge-neutral text-xs ml-1">{{ count($this->comments) }}</span>
                        </h3>

                        {{-- Add comment form --}}
                        @auth
                            <div class="space-y-3">
                                <x-textarea wire:model.defer="newCommentContent" placeholder="{{ __('Share your thoughts about this article...') }}" rows="3" required />
                                <x-button label="{{ __('Post Comment') }}" class="btn-primary btn-sm rounded-xl font-black uppercase text-xs" wire:click="submitComment" spinner="submitComment" />
                            </div>
                        @else
                            <div class="bg-slate-50 dark:bg-slate-950 p-4 text-center rounded-2xl border border-slate-200/30 dark:border-slate-800/80">
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ __('Please') }} <a href="{{ route('login') }}" class="text-primary font-black hover:underline">{{ __('log in') }}</a> {{ __('to participate in the discussion.') }}
                                </p>
                            </div>
                        @endauth

                        {{-- Comments list --}}
                        <div class="space-y-5 pt-4">
                            @forelse($this->comments as $comment)
                                <div wire:key="comment-{{ $comment->id }}" class="p-4 bg-slate-50 dark:bg-slate-950/40 rounded-2xl border border-slate-200/40 dark:border-slate-800/60 space-y-3">
                                    
                                    {{-- Author header & Actions --}}
                                    <div class="flex justify-between items-start">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full overflow-hidden bg-primary/10 flex items-center justify-center text-primary text-xs font-bold shrink-0">
                                                @if($comment->user->avatar_url)
                                                    <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}" class="w-full h-full object-cover">
                                                @else
                                                    {{ substr($comment->user->name ?? 'A', 0, 1) }}
                                                @endif
                                            </div>
                                            <div>
                                                <span class="text-xs font-black text-slate-800 dark:text-slate-200 block">{{ $comment->user->name ?? __('Member') }}</span>
                                                <span class="text-[10px] text-slate-405 font-bold block">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-3">
                                            @auth
                                                <button wire:click="setReplyingTo({{ $comment->id }})" class="text-[10px] font-black uppercase text-primary hover:underline">
                                                    {{ __('Reply') }}
                                                </button>
                                            @endauth
                                            @if(auth()->check() && ($comment->user_id === auth()->id() || auth()->user()->hasAnyRole(['super-admin', 'admin'])))
                                                <button wire:click="deleteComment({{ $comment->id }})" confirm="{{ __('Are you sure you want to delete this comment?') }}" class="text-rose-500 hover:text-rose-700 transition-colors">
                                                    <x-icon name="o-trash" class="w-3.5 h-3.5" />
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed pl-1">
                                        {{ $comment->content }}
                                    </p>

                                    {{-- Inline Replying Form --}}
                                    @if($replyingToId === $comment->id)
                                        <div class="pl-4 border-l-2 border-primary space-y-2 mt-2">
                                            <x-textarea wire:model.defer="newReplyContent" placeholder="{{ __('Type your reply here...') }}" rows="2" />
                                            <div class="flex items-center gap-2">
                                                <x-button label="{{ __('Post Reply') }}" class="btn-primary btn-xs" wire:click="submitReply({{ $comment->id }})" spinner="submitReply({{ $comment->id }})" />
                                                <x-button label="{{ __('Cancel') }}" class="btn-ghost btn-xs" wire:click="setReplyingTo(null)" />
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Threaded replies loop --}}
                                    @if($comment->replies->count() > 0)
                                        <div class="pl-4 border-l-2 border-slate-200 dark:border-slate-800 space-y-3.5 mt-3.5">
                                            @foreach($comment->replies as $reply)
                                                <div wire:key="reply-{{ $reply->id }}" class="space-y-1.5">
                                                    <div class="flex justify-between items-start">
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-6 h-6 rounded-full overflow-hidden bg-primary/10 flex items-center justify-center text-primary text-[9px] font-bold shrink-0">
                                                                @if($reply->user->avatar_url)
                                                                    <img src="{{ $reply->user->avatar_url }}" alt="{{ $reply->user->name }}" class="w-full h-full object-cover">
                                                                @else
                                                                    {{ substr($reply->user->name ?? 'A', 0, 1) }}
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <span class="text-xs font-black text-slate-800 dark:text-slate-200 block">{{ $reply->user->name ?? __('Member') }}</span>
                                                                <span class="text-[9px] text-slate-400 font-bold block">{{ $reply->created_at->diffForHumans() }}</span>
                                                            </div>
                                                        </div>
                                                        @if(auth()->check() && ($reply->user_id === auth()->id() || auth()->user()->hasAnyRole(['super-admin', 'admin'])))
                                                            <button wire:click="deleteComment({{ $reply->id }})" confirm="{{ __('Are you sure you want to delete this reply?') }}" class="text-rose-500 hover:text-rose-700 transition-colors">
                                                                <x-icon name="o-trash" class="w-3 h-3" />
                                                            </button>
                                                        @endif
                                                    </div>
                                                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed pl-1">
                                                        {{ $reply->content }}
                                                    </p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                </div>
                            @empty
                                <p class="text-slate-400 text-sm italic py-4">{{ __('No comments yet. Be the first to start the discussion!') }}</p>
                            @endforelse
                        </div>

                    </div>

                </div>

                {{-- Right Column: Details Sidebar --}}
                <aside class="lg:col-span-4 space-y-6">
                    
                    {{-- Article Info Widget --}}
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 p-5 shadow-sm space-y-3.5">
                        <h4 class="font-black text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider pb-2 border-b border-slate-100 dark:border-slate-850">
                            {{ __('Article Stats') }}
                        </h4>
                        
                        <div class="space-y-2.5 text-xs font-bold text-slate-650 dark:text-slate-350">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">{{ __('Category') }}</span>
                                <span class="text-primary">{{ $this->post->category->name ?? __('Uncategorized') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">{{ __('Published') }}</span>
                                <span>{{ $this->post->published_at?->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">{{ __('Views') }}</span>
                                <span>{{ number_format($this->post->views_count) }} {{ __('views') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">{{ __('Comments') }}</span>
                                <span>{{ count($this->comments) }} {{ __('discussion threads') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Share Widget --}}
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 p-5 shadow-sm">
                        <h4 class="font-black text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider mb-3">
                            {{ __('Share Article') }}
                        </h4>
                        
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ $this->shareUrl }}" target="_blank" rel="noopener" class="px-3 py-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-600 dark:text-blue-400 inline-flex items-center gap-2 transition bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Facebook
                            </a>
                            <a href="https://x.com/intent/tweet?url={{ $this->shareUrl }}&text={{ $this->shareText }}" target="_blank" rel="noopener" class="px-3 py-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 inline-flex items-center gap-2 transition bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> X (Twitter)
                            </a>
                            <a href="https://wa.me/?text={{ $this->shareText }}%20{{ $this->shareUrl }}" target="_blank" rel="noopener" class="px-3 py-2 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 inline-flex items-center gap-2 transition bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> WhatsApp
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $this->shareUrl }}" target="_blank" rel="noopener" class="px-3 py-2 rounded-lg hover:bg-sky-50 dark:hover:bg-sky-900/20 text-sky-600 dark:text-sky-400 inline-flex items-center gap-2 transition bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span> LinkedIn
                            </a>
                            <a href="https://www.reddit.com/submit?url={{ $this->shareUrl }}&title={{ $this->shareText }}" target="_blank" rel="noopener" class="px-3 py-2 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/20 text-orange-600 dark:text-orange-400 inline-flex items-center gap-2 transition bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Reddit
                            </a>
                            <a href="https://t.me/share/url?url={{ $this->shareUrl }}&text={{ $this->shareText }}" target="_blank" rel="noopener" class="px-3 py-2 rounded-lg hover:bg-cyan-50 dark:hover:bg-cyan-900/20 text-cyan-600 dark:text-cyan-400 inline-flex items-center gap-2 transition bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span> Telegram
                            </a>
                            <a href="mailto:?subject={{ $this->shareText }}&body={{ $this->shareText }}%0A%0A{{ $this->shareUrl }}" class="px-3 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 inline-flex items-center gap-2 transition bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-bold font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-450"></span> {{ __('Email') }}
                            </a>
                            <a href="https://www.pinterest.com/pin/create/button/?url={{ $this->shareUrl }}&description={{ $this->shareText }}" target="_blank" rel="noopener" class="px-3 py-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 inline-flex items-center gap-2 transition bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Pinterest
                            </a>
                            <button 
                              type="button"
                              @click="
                                const title = decodeURIComponent('{{ $this->shareText }}');
                                const url = decodeURIComponent('{{ $this->shareUrl }}');
                                if (navigator.share) {
                                  navigator.share({ title, text: title, url }).catch(()=>{});
                                } else {
                                  alert('{{ __('Sharing not supported on this browser.') }}');
                                }
                              "
                              class="px-3 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 inline-flex items-center gap-2 col-span-2 transition bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-bold justify-center"
                            >
                              <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> {{ __('Device Share') }}
                            </button>
                        </div>
                    </div>

                    {{-- Related Posts Widget --}}
                    @if($this->relatedPosts->isNotEmpty())
                        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 p-5 shadow-sm space-y-4">
                            <h4 class="font-black text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider pb-2 border-b border-slate-100 dark:border-slate-850">
                                {{ __('Related Articles') }}
                            </h4>
                            
                            <div class="space-y-4">
                                @foreach($this->relatedPosts as $relatedPost)
                                    <a wire:navigate href="{{ route('web.post', $relatedPost->slug) }}" class="flex gap-3 group/rel">
                                        <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 border border-slate-200/30 dark:border-slate-800">
                                            @if($relatedPost->getFirstMediaUrl('featured_image'))
                                                <img src="{{ $relatedPost->getFirstMediaUrl('featured_image') }}" alt="{{ $relatedPost->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center bg-slate-150 dark:bg-slate-850">
                                                    <x-icon name="o-newspaper" class="w-5 h-5 text-slate-300 dark:text-slate-700" />
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow min-w-0">
                                            <h5 class="text-xs font-black text-slate-800 dark:text-slate-200 group-hover/rel:text-primary transition-colors line-clamp-2 leading-tight">
                                                {{ $relatedPost->title }}
                                            </h5>
                                            <span class="text-[10px] text-slate-400 block mt-1 font-bold">{{ $relatedPost->published_at?->format('M d, Y') }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Trending Posts Widget --}}
                    @if($this->trendingPosts->isNotEmpty())
                        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 p-5 shadow-sm space-y-4">
                            <h4 class="font-black text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider pb-2 border-b border-slate-100 dark:border-slate-850">
                                {{ __('Trending Articles') }}
                            </h4>
                            
                            <div class="space-y-4">
                                @foreach($this->trendingPosts as $trendPost)
                                    <a wire:navigate href="{{ route('web.post', $trendPost->slug) }}" class="flex gap-3 group/trend">
                                        <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 border border-slate-200/30 dark:border-slate-800">
                                            @if($trendPost->getFirstMediaUrl('featured_image'))
                                                <img src="{{ $trendPost->getFirstMediaUrl('featured_image') }}" alt="{{ $trendPost->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center bg-slate-150 dark:bg-slate-850">
                                                    <x-icon name="o-newspaper" class="w-5 h-5 text-slate-300 dark:text-slate-700" />
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow min-w-0">
                                            <h5 class="text-xs font-black text-slate-800 dark:text-slate-200 group-hover/trend:text-primary transition-colors line-clamp-2 leading-tight">
                                                {{ $trendPost->title }}
                                            </h5>
                                            <span class="text-[10px] text-slate-400 block mt-1 font-bold">{{ $trendPost->published_at?->format('M d, Y') }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </aside>

            </div>
        </div>
    </div>
</div>