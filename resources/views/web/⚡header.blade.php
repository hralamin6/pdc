<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

new class extends Component {
    public bool $mobileMenu = false;

    public array $languages = [
        'en' => ['name' => 'English'],
        'ar' => ['name' => 'العربية'],
        'bn' => ['name' => 'বাংলা'],
    ];

    public function switchLanguage(string $locale): void
    {
        if (array_key_exists($locale, $this->languages)) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            $this->redirect(url()->previous(), navigate: true);
        }
    }
};
?>

<div x-data="{ scrolled: false }" @scroll.window="scrolled = (window.scrollY > 20)">
    {{-- Main Nav --}}
    <nav class="fixed top-0 inset-x-0 z-50 transition-all duration-300"
         :class="scrolled ? 'bg-base-100/95 dark:bg-base-100/95 backdrop-blur-xl shadow-sm border-b border-base-content/5' : 'bg-transparent'">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-[72px]">

                {{-- Brand --}}
                <a href="{{ route('web.home') }}" wire:navigate class="flex items-center gap-2.5 shrink-0 group">
                    <x-avatar image="{{ getSettingImage('iconImage', 'icon') }}" class="!w-9 !h-9 !rounded-lg" />
                    <span class="text-lg font-extrabold tracking-tight text-base-content">
                        {{ setting('app.name', 'PSTU Dawah') }}
                    </span>
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden lg:flex items-center gap-1">
                    @php
                        $navLinks = [
                            ['route' => 'web.home', 'label' => 'Home', 'icon' => 'o-home'],
                            ['route' => 'web.halaqahs', 'label' => 'Halaqahs', 'icon' => 'o-book-open'],
                            ['route' => 'web.members', 'label' => 'Members', 'icon' => 'o-users'],
                        ];
                    @endphp
                    @foreach($navLinks as $link)
                        <a href="{{ route($link['route']) }}" wire:navigate
                           class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors {{ request()->routeIs($link['route']) ? 'text-primary bg-primary/8' : 'text-base-content/60 hover:text-base-content hover:bg-base-content/5' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>

                {{-- Desktop Actions --}}
                <div class="hidden lg:flex items-center gap-2">
                    {{-- Language --}}
                    <x-dropdown icon="o-language" class="btn-ghost btn-sm btn-circle text-base-content/50 hover:text-base-content shadow-none" right no-x-anchor>
                        @foreach($languages as $code => $lang)
                            <x-menu-item
                                :title="$lang['name']"
                                wire:click="switchLanguage('{{ $code }}')"
                                class="rounded-lg m-1 {{ app()->getLocale() === $code ? 'text-primary font-bold bg-primary/10' : '' }}"
                            />
                        @endforeach
                    </x-dropdown>

                    {{-- Theme --}}
                    <x-theme-toggle class="btn btn-ghost btn-sm btn-circle text-base-content/50 hover:text-base-content" x-cloak />

                    {{-- Separator --}}
                    <div class="w-px h-6 bg-base-content/10 mx-1"></div>

                    {{-- Auth --}}
                    @auth
                        <a href="{{ route('app.dashboard') }}" wire:navigate class="btn btn-primary btn-sm rounded-lg px-5 font-bold shadow-sm">
                            <x-icon name="o-squares-2x2" class="w-4 h-4" />
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="btn btn-ghost btn-sm rounded-lg font-semibold text-base-content/70">
                            Log in
                        </a>
                        <a href="{{ route('register') }}" wire:navigate class="btn btn-primary btn-sm rounded-lg px-5 font-bold shadow-sm">
                            Sign up
                        </a>
                    @endauth
                </div>

                {{-- Mobile Toggle --}}
                <button wire:click="$toggle('mobileMenu')" class="lg:hidden btn btn-ghost btn-sm btn-square text-base-content/70">
                    <x-icon name="{{ $mobileMenu ? 'o-x-mark' : 'o-bars-3' }}" class="w-5 h-5" />
                </button>
            </div>
        </div>
    </nav>

    {{-- Mobile Slide-down Menu --}}
    @if($mobileMenu)
        <div class="fixed inset-x-0 top-16 z-40 lg:hidden bg-base-100 dark:bg-base-100 border-b border-base-content/10 shadow-xl animate-slide-down">
            <div class="max-w-7xl mx-auto px-4 py-4 space-y-1">
                <a href="{{ route('web.home') }}" wire:navigate wire:click="$set('mobileMenu', false)"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('web.home') ? 'text-primary bg-primary/8' : 'text-base-content/70 hover:bg-base-content/5' }}">
                    <x-icon name="o-home" class="w-5 h-5" /> Home
                </a>
                <a href="{{ route('web.halaqahs') }}" wire:navigate wire:click="$set('mobileMenu', false)"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('web.halaqahs') ? 'text-primary bg-primary/8' : 'text-base-content/70 hover:bg-base-content/5' }}">
                    <x-icon name="o-book-open" class="w-5 h-5" /> Halaqahs
                </a>
                <a href="{{ route('web.members') }}" wire:navigate wire:click="$set('mobileMenu', false)"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('web.members') ? 'text-primary bg-primary/8' : 'text-base-content/70 hover:bg-base-content/5' }}">
                    <x-icon name="o-users" class="w-5 h-5" /> Members
                </a>

                <div class="border-t border-base-content/5 pt-3 mt-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        {{-- Language --}}
                        <x-dropdown icon="o-language" class="btn-ghost btn-sm btn-circle text-base-content/50 shadow-none" right no-x-anchor>
                            @foreach($languages as $code => $lang)
                                <x-menu-item
                                    :title="$lang['name']"
                                    wire:click="switchLanguage('{{ $code }}')"
                                    class="rounded-lg m-1 {{ app()->getLocale() === $code ? 'text-primary font-bold bg-primary/10' : '' }}"
                                />
                            @endforeach
                        </x-dropdown>

                        {{-- Theme --}}
                        <x-theme-toggle class="btn btn-ghost btn-sm btn-circle text-base-content/50" x-cloak />
                    </div>

                    <div class="flex items-center gap-2">
                        @auth
                            <a href="{{ route('app.dashboard') }}" wire:navigate class="btn btn-primary btn-sm rounded-lg px-5 font-bold">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="btn btn-ghost btn-sm rounded-lg font-semibold">
                                Log in
                            </a>
                            <a href="{{ route('register') }}" wire:navigate class="btn btn-primary btn-sm rounded-lg px-5 font-bold">
                                Sign up
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Spacer to push content below fixed nav --}}
    <div class="h-16 lg:h-[72px]"></div>
</div>
