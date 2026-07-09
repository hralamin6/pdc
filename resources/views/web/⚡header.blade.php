<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

new class extends Component {
    public bool $mobileMenu = false;
    public ?string $openMenu = null;

    public array $languages = [
        'en' => ['name' => 'English', 'flag' => '🇬🇧'],
        'ar' => ['name' => 'العربية', 'flag' => '🇸🇦'],
        'bn' => ['name' => 'বাংলা', 'flag' => '🇧🇩'],
    ];

    public function switchLanguage(string $locale): void
    {
        if (array_key_exists($locale, $this->languages)) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            $this->redirect(url()->previous(), navigate: true);
        }
    }

    public function toggleMenu(?string $menu): void
    {
        $this->openMenu = ($this->openMenu === $menu) ? null : $menu;
    }
};
?>

<div
    x-data="{
        scrolled: false,
        openMenu: null,
        mobileOpen: false,
        toggleMenu(name) { this.openMenu = this.openMenu === name ? null : name; }
    }"
    @scroll.window="scrolled = (window.scrollY > 30)"
    @click.outside="openMenu = null"
>
    <nav
        class="fixed top-0 inset-x-0 z-50 transition-all duration-500"
        :class="scrolled
            ? 'bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl shadow-lg shadow-black/5 border-b border-slate-200/50 dark:border-white/5'
            : 'bg-transparent'"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-[72px]">

                {{-- Brand --}}
                <a href="{{ route('web.home') }}" wire:navigate class="flex items-center gap-2.5 shrink-0 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg shadow-primary/30 group-hover:scale-105 transition-transform">
                        <x-icon name="o-moon" class="w-5 h-5 text-white" />
                    </div>
                    <span class="text-lg font-black tracking-tight" :class="scrolled ? 'text-slate-900 dark:text-white' : 'text-white'">
                        {{ setting('app.name', 'PSTU Dawah') }}
                    </span>
                </a>

                {{-- Desktop Mega-Nav --}}
                <div class="hidden lg:flex items-center gap-1">

                    {{-- Home --}}
                    <a href="{{ route('web.home') }}" wire:navigate
                       class="px-4 py-2 rounded-xl text-sm font-semibold transition-all"
                       :class="scrolled
                           ? '{{ request()->routeIs('web.home') ? 'text-primary bg-primary/10' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5' }}'
                           : '{{ request()->routeIs('web.home') ? 'text-white bg-white/15' : 'text-white/80 hover:text-white hover:bg-white/10' }}'">
                        Home
                    </a>

                    {{-- Learn Dropdown --}}
                    <div class="relative" @click.outside="openMenu = null">
                        <button @click="toggleMenu('learn')"
                            class="flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-semibold transition-all"
                            :class="scrolled ? 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5' : 'text-white/80 hover:text-white hover:bg-white/10'">
                            Learn
                            <svg class="w-3.5 h-3.5 transition-transform" :class="openMenu === 'learn' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="openMenu === 'learn'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 -translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl shadow-black/10 dark:shadow-black/40 border border-slate-100 dark:border-white/5 py-2 z-50" @click.outside="openMenu = null">
                            <div class="px-3 py-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">Study Programs</p>
                                <a href="{{ route('web.halaqahs') }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                        <x-icon name="o-book-open" class="w-4 h-4 text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-primary transition-colors">Halaqahs</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Study circles & sessions</p>
                                    </div>
                                </a>
                                <a href="{{ route('web.halaqahs') }}?filter=past" wire:navigate class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                    <div class="w-8 h-8 bg-emerald-500/10 rounded-lg flex items-center justify-center">
                                        <x-icon name="o-academic-cap" class="w-4 h-4 text-emerald-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-emerald-600 transition-colors">Courses & Series</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Multi-session programs</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Community Dropdown --}}
                    <div class="relative" @click.outside="openMenu = null">
                        <button @click="toggleMenu('community')"
                            class="flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-semibold transition-all"
                            :class="scrolled ? 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5' : 'text-white/80 hover:text-white hover:bg-white/10'">
                            Community
                            <svg class="w-3.5 h-3.5 transition-transform" :class="openMenu === 'community' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="openMenu === 'community'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95 -translate-y-2" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl shadow-black/10 dark:shadow-black/40 border border-slate-100 dark:border-white/5 py-2 z-50">
                            <div class="px-3 py-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">People & Content</p>
                                <a href="{{ route('web.members') }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                    <div class="w-8 h-8 bg-violet-500/10 rounded-lg flex items-center justify-center">
                                        <x-icon name="o-users" class="w-4 h-4 text-violet-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-violet-600 transition-colors">Members</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Community directory</p>
                                    </div>
                                </a>
                                <a href="{{ route('web.posts') }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                    <div class="w-8 h-8 bg-amber-500/10 rounded-lg flex items-center justify-center">
                                        <x-icon name="o-newspaper" class="w-4 h-4 text-amber-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-amber-600 transition-colors">Blog & Knowledge</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Articles & reflections</p>
                                    </div>
                                </a>
                                <a href="{{ route('web.campaigns') }}" wire:navigate class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                    <div class="w-8 h-8 bg-rose-500/10 rounded-lg flex items-center justify-center">
                                        <x-icon name="o-heart" class="w-4 h-4 text-rose-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-rose-600 transition-colors">Campaigns</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Support our causes</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- About --}}
                    <a href="#about"
                       class="px-4 py-2 rounded-xl text-sm font-semibold transition-all"
                       :class="scrolled ? 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        About
                    </a>
                </div>

                {{-- Desktop Actions --}}
                <div class="hidden lg:flex items-center gap-2">
                    {{-- Language --}}
                    <x-dropdown class="btn-ghost btn-sm btn-circle shadow-none" no-x-anchor right>
                        <x-slot:trigger>
                            <button class="btn btn-ghost btn-sm btn-circle transition-all"
                                :class="scrolled ? 'text-slate-500 dark:text-slate-400' : 'text-white/70 hover:text-white'">
                                <x-icon name="o-language" class="w-4 h-4" />
                            </button>
                        </x-slot:trigger>
                        @foreach($languages as $code => $lang)
                            <x-menu-item :title="$lang['flag'] . ' ' . $lang['name']"
                                wire:click="switchLanguage('{{ $code }}')"
                                class="rounded-xl m-1 {{ app()->getLocale() === $code ? 'text-primary font-bold bg-primary/10' : '' }}" />
                        @endforeach
                    </x-dropdown>

                    {{-- Theme --}}
                    <x-theme-toggle class="btn btn-ghost btn-sm btn-circle transition-all"
                        ::class="scrolled ? 'text-slate-500 dark:text-slate-400' : 'text-white/70 hover:text-white'" x-cloak />

                    <div class="w-px h-5 bg-white/20 dark:bg-white/10 mx-1" :class="scrolled ? '!bg-slate-200 dark:!bg-white/10' : ''"></div>

                    @auth
                        <a href="{{ route('app.dashboard') }}" wire:navigate
                           class="btn btn-sm bg-gradient-to-r from-primary to-secondary border-none text-white rounded-xl px-5 font-bold shadow-lg shadow-primary/25 hover:scale-105 transition-transform">
                            <x-icon name="o-squares-2x2" class="w-4 h-4" /> Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate
                           class="btn btn-ghost btn-sm rounded-xl font-semibold transition-all"
                           :class="scrolled ? 'text-slate-600 dark:text-slate-300' : 'text-white/80 hover:text-white'">
                            Sign in
                        </a>
                        <a href="{{ route('register') }}" wire:navigate
                           class="btn btn-sm bg-gradient-to-r from-primary to-secondary border-none text-white rounded-xl px-5 font-bold shadow-lg shadow-primary/25 hover:scale-105 transition-transform">
                            Join Free
                        </a>
                    @endauth
                </div>

                {{-- Mobile Hamburger --}}
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden flex flex-col gap-1.5 w-8 h-8 items-center justify-center">
                    <span class="block w-5 h-0.5 transition-all duration-300" :class="[mobileOpen ? 'rotate-45 translate-y-2 bg-slate-900 dark:bg-white' : scrolled ? 'bg-slate-600 dark:bg-slate-300' : 'bg-white']"></span>
                    <span class="block w-5 h-0.5 transition-all duration-300" :class="[mobileOpen ? 'opacity-0' : scrolled ? 'bg-slate-600 dark:bg-slate-300' : 'bg-white']"></span>
                    <span class="block w-5 h-0.5 transition-all duration-300" :class="[mobileOpen ? '-rotate-45 -translate-y-2 bg-slate-900 dark:bg-white' : scrolled ? 'bg-slate-600 dark:bg-slate-300' : 'bg-white']"></span>
                </button>
            </div>
        </div>
    </nav>

    {{-- Mobile Full-Screen Menu --}}
    <div x-show="mobileOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        class="fixed inset-x-0 top-16 z-40 lg:hidden bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-white/5 shadow-2xl">
        <div class="max-w-7xl mx-auto px-4 py-6 space-y-1">
            <a href="{{ route('web.home') }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('web.home') ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5' }} font-semibold transition-colors">
                <x-icon name="o-home" class="w-5 h-5" /> Home
            </a>
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 px-4 pt-4 pb-1">Learn</div>
            <a href="{{ route('web.halaqahs') }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 font-semibold transition-colors">
                <x-icon name="o-book-open" class="w-5 h-5 text-primary" /> Halaqahs
            </a>
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 px-4 pt-4 pb-1">Community</div>
            <a href="{{ route('web.members') }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 font-semibold transition-colors">
                <x-icon name="o-users" class="w-5 h-5 text-violet-600" /> Members
            </a>
            <a href="{{ route('web.posts') }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 font-semibold transition-colors">
                <x-icon name="o-newspaper" class="w-5 h-5 text-amber-600" /> Blog
            </a>
            <a href="{{ route('web.campaigns') }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 font-semibold transition-colors">
                <x-icon name="o-heart" class="w-5 h-5 text-rose-600" /> Campaigns
            </a>

            <div class="border-t border-slate-100 dark:border-white/5 pt-4 mt-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-theme-toggle class="btn btn-ghost btn-sm btn-circle text-slate-500" x-cloak />
                </div>
                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ route('app.dashboard') }}" wire:navigate class="btn btn-primary btn-sm rounded-xl px-5 font-bold">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="btn btn-ghost btn-sm rounded-xl font-semibold">Sign in</a>
                        <a href="{{ route('register') }}" wire:navigate class="btn btn-sm bg-gradient-to-r from-primary to-secondary border-none text-white rounded-xl px-5 font-bold">Join Free</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    {{-- Spacer --}}
    <div class="h-16 lg:h-[72px]"></div>
</div>
