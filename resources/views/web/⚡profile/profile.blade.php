<div class="bg-slate-50/40 dark:bg-slate-950/40 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="profile()">
        
        {{-- ========================================== --}}
        {{-- PAGE HEADER --}}
        {{-- ========================================== --}}
        <div class="border-b border-slate-200 dark:border-slate-800/80 pb-8 mb-10 text-center md:text-left">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-black uppercase tracking-widest mb-3">
                <x-icon name="o-user" class="w-3.5 h-3.5" />
                {{ __('Account Hub') }}
            </span>
            <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-3">
                {{ __('My Profile') }}
            </h1>
            <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400 max-w-xl leading-relaxed">
                {{ __('Manage your personal details, secure your account, track active sessions, and review activity logs.') }}
            </p>
        </div>

        {{-- ========================================== --}}
        {{-- MAIN GRID --}}
        {{-- ========================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- ========================================== --}}
            {{-- LEFT SIDEBAR / TAB NAVIGATION --}}
            {{-- ========================================== --}}
            <div class="lg:col-span-3 space-y-6">
                {{-- User Preview Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 p-6 flex flex-col items-center text-center shadow-sm">
                    <div class="relative mb-4 group w-24 h-24 rounded-full ring-4 ring-primary/10 overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200/30 dark:border-slate-800">
                        <img src="{{ $avatarUrl }}" alt="{{ $name }}" class="w-full h-full object-cover" />
                    </div>
                    <h3 class="font-black text-lg text-slate-800 dark:text-slate-100 truncate max-w-full">
                        {{ $name }}
                    </h3>
                    <p class="text-xs font-semibold text-slate-450 dark:text-slate-400 truncate max-w-full mb-4">
                        {{ $email }}
                    </p>
                    @if($occupation)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-black bg-primary/10 text-primary uppercase tracking-widest">
                            {{ $occupation }}
                        </span>
                    @endif
                </div>

                {{-- Navigation Menu --}}
                <nav class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 p-3 space-y-1 shadow-sm">
                    <button @click="activeTab = 'view'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-black transition-all"
                            :class="activeTab === 'view' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-950 dark:hover:text-white'">
                        <x-icon name="o-eye" class="w-4 h-4" />
                        {{ __('View Profile') }}
                    </button>
                    <button @click="activeTab = 'general'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-black transition-all"
                            :class="activeTab === 'general' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-950 dark:hover:text-white'">
                        <x-icon name="o-user" class="w-4 h-4" />
                        {{ __('Account Settings') }}
                    </button>
                    <button @click="activeTab = 'details'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-black transition-all"
                            :class="activeTab === 'details' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-950 dark:hover:text-white'">
                        <x-icon name="o-identification" class="w-4 h-4" />
                        {{ __('Personal Details') }}
                    </button>
                    <button @click="activeTab = 'social'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-black transition-all"
                            :class="activeTab === 'social' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-950 dark:hover:text-white'">
                        <x-icon name="o-share" class="w-4 h-4" />
                        {{ __('Social Profiles') }}
                    </button>
                    <button @click="activeTab = 'address'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-black transition-all"
                            :class="activeTab === 'address' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-950 dark:hover:text-white'">
                        <x-icon name="o-map-pin" class="w-4 h-4" />
                        {{ __('Address Details') }}
                    </button>
                    <button @click="activeTab = 'images'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-black transition-all"
                            :class="activeTab === 'images' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-950 dark:hover:text-white'">
                        <x-icon name="o-photo" class="w-4 h-4" />
                        {{ __('Customize Media') }}
                    </button>
                    <button @click="activeTab = 'security'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-black transition-all"
                            :class="activeTab === 'security' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-950 dark:hover:text-white'">
                        <x-icon name="o-lock-closed" class="w-4 h-4" />
                        {{ __('Security Settings') }}
                    </button>
                    <button @click="activeTab = 'sessions'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-black transition-all"
                            :class="activeTab === 'sessions' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-950 dark:hover:text-white'">
                        <x-icon name="o-computer-desktop" class="w-4 h-4" />
                        {{ __('Active Sessions') }}
                    </button>
                    <button @click="activeTab = 'activities'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-black transition-all"
                            :class="activeTab === 'activities' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-950 dark:hover:text-white'">
                        <x-icon name="o-clipboard-document-list" class="w-4 h-4" />
                        {{ __('Activity History') }}
                    </button>
                </nav>
            </div>

            {{-- ========================================== --}}
            {{-- RIGHT CONTENT AREA --}}
            {{-- ========================================== --}}
            <div class="lg:col-span-9 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 p-6 md:p-8 shadow-sm min-h-[500px]">

                {{-- 1. VIEW PROFILE TAB --}}
                <div x-show="activeTab === 'view'" x-cloak class="space-y-8 animate-fadeIn">
                    {{-- Banner image --}}
                    <div class="relative w-full h-48 md:h-64 rounded-2xl overflow-hidden bg-gradient-to-r from-primary/10 to-secondary/10 border border-slate-200/40 dark:border-slate-800">
                        @if($bannerUrl)
                            <img src="{{ $bannerUrl }}" alt="{{ __('Banner') }}" class="w-full h-full object-cover" />
                        @endif
                    </div>

                    {{-- Quick header --}}
                    <div class="flex flex-col md:flex-row items-center md:items-end gap-6 -mt-20 px-6 relative z-10">
                        <div class="w-28 h-28 rounded-full ring-4 ring-white dark:ring-slate-900 overflow-hidden shadow-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/30 dark:border-slate-800">
                            <img src="{{ $avatarUrl }}" alt="{{ $name }}" class="w-full h-full object-cover" />
                        </div>
                        <div class="text-center md:text-left flex-grow">
                            <h2 class="text-3xl font-black text-slate-800 dark:text-slate-100 leading-tight">
                                {{ $name }}
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-bold mt-1.5 uppercase tracking-widest flex items-center justify-center md:justify-start gap-1">
                                <x-icon name="o-envelope" class="w-4 h-4 text-primary" />
                                {{ $email }}
                            </p>
                        </div>
                    </div>

                    {{-- Bio description --}}
                    @if($bio)
                        <div class="bg-slate-50/50 dark:bg-slate-950/20 border border-slate-200/50 dark:border-slate-800/80 p-6 rounded-2xl">
                            <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase text-xs tracking-wider mb-2 flex items-center gap-1.5 border-b border-slate-100 dark:border-slate-900 pb-2">
                                <x-icon name="o-document-text" class="w-4 h-4 text-primary" />
                                {{ __('About Me') }}
                            </h4>
                            <p class="text-slate-650 dark:text-slate-350 font-medium whitespace-pre-line text-sm leading-relaxed">
                                {{ $bio }}
                            </p>
                        </div>
                    @endif

                    {{-- Details split grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 border-t border-slate-100 dark:border-slate-850">
                        {{-- Personal detail points --}}
                        <div class="space-y-4">
                            <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase text-xs tracking-wider flex items-center gap-1.5 border-b border-slate-100 dark:border-slate-900 pb-2">
                                <x-icon name="o-user" class="w-4 h-4 text-primary" />
                                {{ __('Personal Details') }}
                            </h4>

                            <div class="space-y-3.5">
                                <div class="flex items-start gap-3 text-sm">
                                    <x-icon name="o-phone" class="w-4 h-4 text-slate-450 mt-0.5" />
                                    <div>
                                        <p class="text-xs text-slate-450 dark:text-slate-400 font-bold uppercase tracking-wider">{{ __('Phone Number') }}</p>
                                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ $phone ?: __('Not specified') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 text-sm">
                                    <x-icon name="o-calendar" class="w-4 h-4 text-slate-450 mt-0.5" />
                                    <div>
                                        <p class="text-xs text-slate-450 dark:text-slate-400 font-bold uppercase tracking-wider">{{ __('Date of Birth') }}</p>
                                        <p class="font-bold text-slate-800 dark:text-slate-200">
                                            {{ $date_of_birth ? \Carbon\Carbon::parse($date_of_birth)->format('M d, Y') : __('Not specified') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 text-sm">
                                    <x-icon name="o-user-circle" class="w-4 h-4 text-slate-450 mt-0.5" />
                                    <div>
                                        <p class="text-xs text-slate-455 dark:text-slate-400 font-bold uppercase tracking-wider">{{ __('Gender') }}</p>
                                        <p class="font-bold text-slate-800 dark:text-slate-200 capitalize">{{ $gender ?: __('Not specified') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 text-sm">
                                    <x-icon name="o-globe-alt" class="w-4 h-4 text-slate-450 mt-0.5" />
                                    <div>
                                        <p class="text-xs text-slate-450 dark:text-slate-400 font-bold uppercase tracking-wider">{{ __('Website') }}</p>
                                        @if($website)
                                            <a href="{{ $website }}" target="_blank" class="font-bold text-primary hover:underline truncate block max-w-[240px]">{{ $website }}</a>
                                        @else
                                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ __('Not specified') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Address detail points --}}
                        <div class="space-y-4">
                            <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase text-xs tracking-wider flex items-center gap-1.5 border-b border-slate-100 dark:border-slate-900 pb-2">
                                <x-icon name="o-map-pin" class="w-4 h-4 text-primary" />
                                {{ __('Address') }}
                            </h4>

                            @php
                                $user = Auth::user();
                                $hasAddress = $division_id || $district_id || $upazila_id || $union_id || $address || $postal_code;
                            @endphp

                            @if($hasAddress)
                                <div class="space-y-3.5">
                                    @if($division_id)
                                        <div class="flex items-start gap-3 text-sm">
                                            <x-icon name="o-map" class="w-4 h-4 text-slate-450 mt-0.5" />
                                            <div>
                                                <p class="text-xs text-slate-450 dark:text-slate-400 font-bold uppercase tracking-wider">{{ __('Division') }}</p>
                                                <p class="font-bold text-slate-800 dark:text-slate-200">{{ $user->detail->division->name ?? __('N/A') }}</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if($district_id)
                                        <div class="flex items-start gap-3 text-sm">
                                            <x-icon name="o-map" class="w-4 h-4 text-slate-455 mt-0.5" />
                                            <div>
                                                <p class="text-xs text-slate-455 dark:text-slate-400 font-bold uppercase tracking-wider">{{ __('District') }}</p>
                                                <p class="font-bold text-slate-800 dark:text-slate-200">{{ $user->detail->district->name ?? __('N/A') }}</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if($upazila_id)
                                        <div class="flex items-start gap-3 text-sm">
                                            <x-icon name="o-map" class="w-4 h-4 text-slate-455 mt-0.5" />
                                            <div>
                                                <p class="text-xs text-slate-455 dark:text-slate-400 font-bold uppercase tracking-wider">{{ __('Upazila') }}</p>
                                                <p class="font-bold text-slate-800 dark:text-slate-200">{{ $user->detail->upazila->name ?? __('N/A') }}</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if($union_id)
                                        <div class="flex items-start gap-3 text-sm">
                                            <x-icon name="o-map" class="w-4 h-4 text-slate-455 mt-0.5" />
                                            <div>
                                                <p class="text-xs text-slate-455 dark:text-slate-400 font-bold uppercase tracking-wider">{{ __('Union') }}</p>
                                                <p class="font-bold text-slate-800 dark:text-slate-200">{{ $user->detail->union->name ?? __('N/A') }}</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if($address)
                                        <div class="flex items-start gap-3 text-sm">
                                            <x-icon name="o-home" class="w-4 h-4 text-slate-455 mt-0.5" />
                                            <div>
                                                <p class="text-xs text-slate-455 dark:text-slate-400 font-bold uppercase tracking-wider">{{ __('Street Address') }}</p>
                                                <p class="font-bold text-slate-800 dark:text-slate-200">{{ $address }}</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if($postal_code)
                                        <div class="flex items-start gap-3 text-sm">
                                            <x-icon name="o-envelope" class="w-4 h-4 text-slate-455 mt-0.5" />
                                            <div>
                                                <p class="text-xs text-slate-455 dark:text-slate-400 font-bold uppercase tracking-wider">{{ __('Postal Code') }}</p>
                                                <p class="font-bold text-slate-800 dark:text-slate-200">{{ $postal_code }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="text-sm text-slate-450 dark:text-slate-500 font-semibold">{{ __('No address details set.') }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Social links panel --}}
                    @php
                        $hasSocial = $facebook || $twitter || $instagram || $linkedin || $youtube || $github;
                    @endphp
                    @if($hasSocial)
                        <div class="pt-6 border-t border-slate-100 dark:border-slate-850">
                            <h4 class="font-black text-slate-800 dark:text-slate-200 uppercase text-xs tracking-wider mb-4 flex items-center gap-1.5 border-b border-slate-100 dark:border-slate-900 pb-2">
                                <x-icon name="o-share" class="w-4 h-4 text-primary" />
                                {{ __('Social Profiles') }}
                            </h4>
                            <div class="flex flex-wrap gap-3">
                                @if($facebook)
                                    <a href="{{ $facebook }}" target="_blank" class="btn btn-sm btn-outline rounded-xl font-bold flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                        <x-icon name="o-link" class="w-4 h-4 text-blue-600" /> {{ __('Facebook') }}
                                    </a>
                                @endif
                                @if($twitter)
                                    <a href="{{ $twitter }}" target="_blank" class="btn btn-sm btn-outline rounded-xl font-bold flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                        <x-icon name="o-link" class="w-4 h-4 text-sky-400" /> {{ __('Twitter') }}
                                    </a>
                                @endif
                                @if($instagram)
                                    <a href="{{ $instagram }}" target="_blank" class="btn btn-sm btn-outline rounded-xl font-bold flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                        <x-icon name="o-link" class="w-4 h-4 text-pink-500" /> {{ __('Instagram') }}
                                    </a>
                                @endif
                                @if($linkedin)
                                    <a href="{{ $linkedin }}" target="_blank" class="btn btn-sm btn-outline rounded-xl font-bold flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                        <x-icon name="o-link" class="w-4 h-4 text-blue-500" /> {{ __('LinkedIn') }}
                                    </a>
                                @endif
                                @if($youtube)
                                    <a href="{{ $youtube }}" target="_blank" class="btn btn-sm btn-outline rounded-xl font-bold flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                        <x-icon name="o-link" class="w-4 h-4 text-red-600" /> {{ __('YouTube') }}
                                    </a>
                                @endif
                                @if($github)
                                    <a href="{{ $github }}" target="_blank" class="btn btn-sm btn-outline rounded-xl font-bold flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                        <x-icon name="o-link" class="w-4 h-4 text-slate-800 dark:text-white" /> {{ __('GitHub') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- 2. GENERAL INFORMATION TAB --}}
                <div x-show="activeTab === 'general'" x-cloak class="space-y-6 animate-fadeIn">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight mb-1">
                            {{ __('Account Settings') }}
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                            {{ __('Update your primary name and email address here.') }}
                        </p>
                    </div>

                    <form wire:submit="saveGeneral" class="space-y-5 max-w-xl">
                        <div>
                            <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Display Name') }} *</label>
                            <input type="text" wire:model="name" required class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Email Address') }} *</label>
                            <input type="email" wire:model="email" required class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="btn btn-primary rounded-xl px-6 font-bold shadow-lg shadow-primary/25">
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                    </form>
                </div>

                {{-- 3. PERSONAL DETAILS TAB --}}
                <div x-show="activeTab === 'details'" x-cloak class="space-y-6 animate-fadeIn">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight mb-1">
                            {{ __('Personal Details') }}
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                            {{ __('Provide additional details about yourself for directory listing.') }}
                        </p>
                    </div>

                    <form wire:submit="saveDetails" class="space-y-5 max-w-xl">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Phone Number') }}</label>
                                <input type="tel" wire:model="phone" :placeholder="__('017xxxxxxxx')" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Date of Birth') }}</label>
                                <input type="date" wire:model="date_of_birth" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Gender') }}</label>
                            <select wire:model="gender" class="select select-bordered w-full rounded-2xl text-xs bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-350">
                                <option value="">{{ __('Select Gender') }}</option>
                                <option value="male">{{ __('Male') }}</option>
                                <option value="female">{{ __('Female') }}</option>
                                <option value="other">{{ __('Other') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Occupation') }}</label>
                            <input type="text" wire:model="occupation" placeholder="{{ __('e.g. Student, Developer') }}" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Street Address') }}</label>
                            <textarea wire:model="address" rows="2" placeholder="{{ __('House/Road, Sector...') }}" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350 resize-none"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Postal Code') }}</label>
                            <input type="text" wire:model="postal_code" placeholder="{{ __('Postal Code') }}" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Bio / Short Description') }}</label>
                            <textarea wire:model="bio" rows="4" placeholder="{{ __('Tell the community about yourself...') }}" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350 resize-none"></textarea>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="btn btn-primary rounded-xl px-6 font-bold shadow-lg shadow-primary/25">
                                {{ __('Save Details') }}
                            </button>
                        </div>
                    </form>
                </div>

                {{-- 4. SOCIAL PROFILES TAB --}}
                <div x-show="activeTab === 'social'" x-cloak class="space-y-6 animate-fadeIn">
                    <div>
                        <h2 class="text-2xl font-black text-slate-850 dark:text-slate-100 tracking-tight mb-1">
                            {{ __('Social Profiles') }}
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                            {{ __('Connect your external websites and social networking links.') }}
                        </p>
                    </div>

                    <form wire:submit="saveSocialMedia" class="space-y-5 max-w-xl">
                        <div>
                            <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Website') }}</label>
                            <input type="url" wire:model="website" :placeholder="__('https://website.com')" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Facebook URL') }}</label>
                                <input type="url" wire:model="facebook" :placeholder="__('https://facebook.com/...')" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Twitter URL') }}</label>
                                <input type="url" wire:model="twitter" :placeholder="__('https://twitter.com/...')" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Instagram URL') }}</label>
                                <input type="url" wire:model="instagram" :placeholder="__('https://instagram.com/...')" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('LinkedIn URL') }}</label>
                                <input type="url" wire:model="linkedin" :placeholder="__('https://linkedin.com/...')" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('YouTube Channel URL') }}</label>
                                <input type="url" wire:model="youtube" :placeholder="__('https://youtube.com/...')" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('GitHub Profile URL') }}</label>
                                <input type="url" wire:model="github" :placeholder="__('https://github.com/...')" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="btn btn-primary rounded-xl px-6 font-bold shadow-lg shadow-primary/25">
                                {{ __('Save Profiles') }}
                            </button>
                        </div>
                    </form>
                </div>

                {{-- 5. ADDRESS DETAILS TAB --}}
                <div x-show="activeTab === 'address'" x-cloak class="space-y-6 animate-fadeIn">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight mb-1">
                            {{ __('Address Information') }}
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                            {{ __('Select your location boundary using the searchable lookup below.') }}
                        </p>
                    </div>

                    <form wire:submit="saveAddress" class="space-y-5 max-w-xl">
                        <x-choices
                            label="{{ __('Division') }}"
                            wire:model.live="division_id"
                            :options="$divisions"
                            option-label="name"
                            option-value="id"
                            placeholder="{{ __('Search Division...') }}"
                            single
                            searchable
                        />

                        @if(count($districts) > 0)
                            <x-choices
                                label="{{ __('District') }}"
                                wire:model.live="district_id"
                                :options="$districts"
                                option-label="name"
                                option-value="id"
                                placeholder="{{ __('Search District...') }}"
                                single
                                searchable
                            />
                        @endif

                        @if(count($upazilas) > 0)
                            <x-choices
                                label="{{ __('Upazila') }}"
                                wire:model.live="upazila_id"
                                :options="$upazilas"
                                option-label="name"
                                option-value="id"
                                placeholder="{{ __('Search Upazila...') }}"
                                single
                                searchable
                            />
                        @endif

                        @if(count($unions) > 0)
                            <x-choices
                                label="{{ __('Union') }}"
                                wire:model.live="union_id"
                                :options="$unions"
                                option-label="name"
                                option-value="id"
                                placeholder="{{ __('Search Union...') }}"
                                single
                                searchable
                            />
                        @endif

                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="btn btn-primary rounded-xl px-6 font-bold shadow-lg shadow-primary/25">
                                {{ __('Save Address') }}
                            </button>
                        </div>
                    </form>
                </div>

                {{-- 6. MEDIA CUSTOMIZATION TAB --}}
                <div x-show="activeTab === 'images'" x-cloak class="space-y-8 animate-fadeIn">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight mb-1">
                            {{ __('Customize Media') }}
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                            {{ __('Upload a custom profile image and account cover banner.') }}
                        </p>
                    </div>

                    {{-- Avatar card --}}
                    <div class="bg-slate-50/50 dark:bg-slate-950/40 p-6 rounded-3xl border border-slate-200/50 dark:border-slate-800/80">
                        <h3 class="font-black text-slate-800 dark:text-slate-200 text-base mb-4 flex items-center gap-2 border-b border-slate-100 dark:border-slate-900 pb-2">
                            <x-icon name="o-user" class="w-5 h-5 text-primary" />
                            {{ __('Profile Avatar') }}
                        </h3>

                        <div class="space-y-4 max-w-xl">
                            <div>
                                <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Avatar Image URL') }}</label>
                                <input type="url" wire:model="image_url" :placeholder="__('https://example.com/avatar.jpg')" class="w-full px-3 py-2.5 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                            </div>
                            
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest text-center">{{ __('OR') }}</div>
                            
                            <x-avatar-upload :label="__('Upload from Device')" model="photo" :image="$photo ? $photo->temporaryUrl() : $avatarUrl" :hint="__('PNG, JPG, WEBP formats up to 10MB')" />

                            <div class="flex gap-2 pt-2">
                                <button type="button" wire:click="savePhoto" class="btn btn-primary rounded-xl px-5 font-bold shadow-lg shadow-primary/25">
                                    {{ __('Save Avatar') }}
                                </button>
                                <button type="button" wire:click="$set('photo', null); $set('image_url', '')" class="btn btn-ghost rounded-xl font-bold">
                                    {{ __('Reset Form') }}
                                </button>
                                @if($avatarUrl && !str_contains($avatarUrl, 'ui-avatars.com'))
                                    <button type="button" wire:click="removePhoto" wire:confirm="{{ __('Delete your profile avatar?') }}" class="btn btn-ghost text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-xl font-bold ml-auto">
                                        {{ __('Delete Avatar') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Cover banner card --}}
                    <div class="bg-slate-50/50 dark:bg-slate-950/40 p-6 rounded-3xl border border-slate-200/50 dark:border-slate-800/80">
                        <h3 class="font-black text-slate-800 dark:text-slate-200 text-base mb-2 flex items-center gap-2 border-b border-slate-100 dark:border-slate-900 pb-2">
                            <x-icon name="o-photo" class="w-5 h-5 text-primary" />
                            {{ __('Profile Banner') }}
                        </h3>
                        <p class="text-[11px] text-slate-450 font-bold uppercase tracking-wider mb-4">
                            {{ __('Recommended dimensions: 1500x500 pixels. Max file size: 10MB.') }}
                        </p>

                        <div class="space-y-4 max-w-xl">
                            <div>
                                <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Banner Image URL') }}</label>
                                <input type="url" wire:model="banner_url" :placeholder="__('https://example.com/banner.jpg')" class="w-full px-3 py-2.5 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                            </div>
                            
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest text-center">{{ __('OR') }}</div>
                            
                            <x-file wire:model="banner_photo" :label="__('Upload Banner File')" accept="image/*" />

                            @if($banner_photo)
                                <div class="mt-2 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden h-36">
                                    <img src="{{ $banner_photo->temporaryUrl() }}" alt="{{ __('Banner Preview') }}" class="w-full h-full object-cover" />
                                </div>
                            @elseif($bannerUrl)
                                <div class="mt-2 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden h-36">
                                    <img src="{{ $bannerUrl }}" alt="{{ __('Banner Current') }}" class="w-full h-full object-cover" />
                                </div>
                            @endif

                            <div class="flex gap-2 pt-2">
                                <button type="button" wire:click="saveBanner" class="btn btn-primary rounded-xl px-5 font-bold shadow-lg shadow-primary/25">
                                    {{ __('Save Banner') }}
                                </button>
                                <button type="button" wire:click="$set('banner_photo', null); $set('banner_url', '')" class="btn btn-ghost rounded-xl font-bold">
                                    {{ __('Reset Form') }}
                                </button>
                                @if($bannerUrl)
                                    <button type="button" wire:click="removeBanner" wire:confirm="{{ __('Delete your banner image?') }}" class="btn btn-ghost text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-xl font-bold ml-auto">
                                        {{ __('Delete Banner') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 7. SECURITY SETTINGS TAB --}}
                <div x-show="activeTab === 'security'" x-cloak class="space-y-6 animate-fadeIn">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight mb-1">
                            {{ __('Security Settings') }}
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                            {{ __('Update your account password to secure authentication access.') }}
                        </p>
                    </div>

                    <form wire:submit="savePassword" class="space-y-5 max-w-xl">
                        <div>
                            <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Current Password') }} *</label>
                            <input type="password" wire:model="current_password" required class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('New Password') }} *</label>
                            <input type="password" wire:model="password" required class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                            <span class="text-[10px] text-slate-400 font-semibold mt-1 block">{{ __('Minimum 8 characters') }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5 uppercase">{{ __('Confirm New Password') }} *</label>
                            <input type="password" wire:model="password_confirmation" required class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="btn btn-primary rounded-xl px-6 font-bold shadow-lg shadow-primary/25">
                                {{ __('Change Password') }}
                            </button>
                        </div>
                    </form>
                </div>

                {{-- 8. ACTIVE SESSIONS TAB --}}
                <div x-show="activeTab === 'sessions'" x-cloak class="space-y-6 animate-fadeIn">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight mb-1">
                            {{ __('Active Sessions') }}
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                            {{ __('Review and terminate logged-in web sessions on other browsers or devices.') }}
                        </p>
                    </div>

                    <div class="space-y-4">
                        @forelse($this->sessions as $session)
                            <div class="flex items-start gap-4 p-5 border rounded-2xl {{ $session->is_current ? 'border-primary bg-primary/5 dark:bg-primary/10' : 'border-slate-200 dark:border-slate-800/80 bg-slate-50/20 dark:bg-slate-950/20' }}">
                                <div class="flex-shrink-0">
                                    @php
                                        $agent = strtolower($session->user_agent ?? '');
                                        $isMobile = str_contains($agent, 'mobile') || str_contains($agent, 'android') || str_contains($agent, 'iphone');
                                        $isTablet = str_contains($agent, 'tablet') || str_contains($agent, 'ipad');
                                    @endphp
                                    <div class="w-12 h-12 rounded-2xl {{ $session->is_current ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-400' }} flex items-center justify-center">
                                        @if($isMobile)
                                            <x-icon name="o-device-phone-mobile" class="w-6 h-6" />
                                        @elseif($isTablet)
                                            <x-icon name="o-device-tablet" class="w-6 h-6" />
                                        @else
                                            <x-icon name="o-computer-desktop" class="w-6 h-6" />
                                        @endif
                                    </div>
                                </div>

                                <div class="flex-grow min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-bold text-slate-900 dark:text-white">
                                            {{ $session->is_current ? __('This Device') : __('Other Browser Session') }}
                                        </h4>
                                        @if($session->is_current)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black bg-primary/20 text-primary uppercase tracking-wider">
                                                {{ __('Active') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 space-y-1 font-semibold">
                                        <p class="flex items-center gap-1.5"><x-icon name="o-globe-alt" class="w-3.5 h-3.5" /> {{ $session->ip_address }}</p>
                                        <p class="flex items-center gap-1.5 truncate"><x-icon name="o-information-circle" class="w-3.5 h-3.5 shrink-0" /> <span class="truncate">{{ Str::limit($session->user_agent, 80) }}</span></p>
                                        <p class="flex items-center gap-1.5"><x-icon name="o-clock" class="w-3.5 h-3.5" /> {{ __('Last active') }}: {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</p>
                                    </div>
                                </div>

                                @if(!$session->is_current)
                                    <button wire:click="logoutSession('{{ $session->id }}')" wire:confirm="{{ __('Terminating session? You will be logged out of that device.') }}" class="btn btn-ghost btn-xs text-xs font-black text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg px-2">
                                        {{ __('Logout') }}
                                    </button>
                                @endif
                            </div>
                        @empty
                            <p class="text-slate-500 dark:text-slate-400 text-center py-6">{{ __('No active session log available.') }}</p>
                        @endforelse
                    </div>

                    @if(count($this->sessions) > 1)
                        <div class="pt-6 border-t border-slate-100 dark:border-slate-850 flex items-center justify-between gap-4">
                            <div>
                                <h4 class="font-black text-slate-800 dark:text-slate-100 text-sm mb-1">{{ __('Logout All Other Sessions') }}</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-450 font-medium">{{ __('Terminates authentication access on all devices except your current browser.') }}</p>
                            </div>
                            <button wire:click="logoutAllOtherSessions" wire:confirm="{{ __('Log out of all other sessions? This session will remain logged in.') }}" class="btn btn-error btn-sm rounded-xl font-bold shadow-lg shadow-error/20">
                                {{ __('Logout Others') }}
                            </button>
                        </div>
                    @endif
                </div>

                {{-- 9. ACTIVITY HISTORY TAB --}}
                <div x-show="activeTab === 'activities'" x-cloak class="space-y-6 animate-fadeIn">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight mb-1">
                            {{ __('Activity History') }}
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                            {{ __('Track account transactions, content creation, edits, and security logs.') }}
                        </p>
                    </div>

                    <div class="space-y-4">
                        @forelse($this->activities as $act)
                            <div class="p-5 border border-slate-200/50 dark:border-slate-800/80 rounded-2xl bg-slate-50/30 dark:bg-slate-950/20 flex gap-4">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 border border-slate-200/20 dark:border-slate-850">
                                    @php
                                        $icon = match($act->event) {
                                            'created' => 'o-plus-circle',
                                            'updated' => 'o-pencil-square',
                                            'deleted' => 'o-trash',
                                            default => 'o-bolt'
                                        };
                                    @endphp
                                    <x-icon name="{{ $icon }}" class="w-5 h-5" />
                                </div>
                                <div class="flex-grow min-w-0">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-bold text-slate-800 dark:text-slate-250 text-sm">
                                                {{ $act->description }}
                                            </p>
                                            <p class="text-[9px] uppercase font-black text-slate-400 dark:text-slate-500 tracking-widest mt-1.5">
                                                {{ __('Subject') }}: {{ class_basename($act->subject_type) }} #{{ $act->subject_id }}
                                            </p>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-450 dark:text-slate-500 whitespace-nowrap">
                                            {{ $act->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <div class="mt-2.5 flex items-center gap-4 text-xs font-bold text-slate-500 dark:text-slate-450">
                                        <span class="flex items-center gap-1"><x-icon name="o-globe-alt" class="w-3.5 h-3.5" /> {{ $act->ip_address ?? '127.0.0.1' }}</span>
                                        <span class="flex items-center gap-1 truncate"><x-icon name="o-computer-desktop" class="w-3.5 h-3.5 shrink-0" /> <span class="truncate max-w-[200px]">{{ Str::limit($act->user_agent, 30) }}</span></span>
                                    </div>
                                    
                                    @if($act->properties && count($act->properties) > 0)
                                        <div class="mt-4 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl p-4 shadow-sm overflow-hidden">
                                            @php
                                                $hasOld = isset($act->properties['old']);
                                                $hasNew = isset($act->properties['attributes']);
                                                $isUpdate = $hasOld && $hasNew;
                                            @endphp

                                            @if($isUpdate)
                                                <div class="font-bold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-1.5 text-xs">
                                                    <x-icon name="o-arrows-right-left" class="w-4 h-4 text-primary" /> {{ __('Changes') }}
                                                </div>
                                                <div class="space-y-2">
                                                    @foreach($act->properties['attributes'] as $key => $newValue)
                                                        @if(!in_array($key, ['updated_at', 'created_at', 'id']))
                                                            @php
                                                                $oldValue = $act->properties['old'][$key] ?? null;
                                                                if ($oldValue === $newValue) continue;
                                                                
                                                                $oldStr = is_array($oldValue) ? json_encode($oldValue) : (string)($oldValue ?? 'null');
                                                                $newStr = is_array($newValue) ? json_encode($newValue) : (string)($newValue ?? 'null');
                                                            @endphp
                                                            <div class="flex items-start gap-3 text-[11px] font-mono">
                                                                <span class="text-slate-500 font-bold shrink-0 w-24 truncate mt-0.5" title="{{ $key }}">{{ __(str_replace('_', ' ', Str::title($key))) }}:</span>
                                                                <div class="flex-1 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                                                    <span class="bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 px-2 py-0.5 rounded border border-rose-100 dark:border-rose-900/50 break-all line-through decoration-rose-300 dark:decoration-rose-700">{{ $oldStr }}</span>
                                                                    <x-icon name="o-arrow-right" class="w-3 h-3 text-slate-400 hidden sm:block shrink-0" />
                                                                    <span class="bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded border border-emerald-100 dark:border-emerald-900/50 break-all">{{ $newStr }}</span>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @elseif($hasNew)
                                                <div class="font-bold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-1.5 text-xs">
                                                    <x-icon name="o-document-plus" class="w-4 h-4 text-emerald-500" /> {{ __('Added Values') }}
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] font-mono">
                                                    @foreach($act->properties['attributes'] as $key => $value)
                                                        @if(!in_array($key, ['updated_at', 'created_at', 'id']))
                                                            <div class="flex items-start gap-2">
                                                                <span class="text-slate-500 font-bold shrink-0">{{ __(str_replace('_', ' ', Str::title($key))) }}:</span>
                                                                <span class="text-slate-700 dark:text-slate-300 break-all">{{ is_array($value) ? json_encode($value) : (string)($value ?? 'null') }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @elseif($hasOld && !$hasNew)
                                                <div class="font-bold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-1.5 text-xs">
                                                    <x-icon name="o-document-minus" class="w-4 h-4 text-rose-500" /> {{ __('Removed Values') }}
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] font-mono">
                                                    @foreach($act->properties['old'] as $key => $value)
                                                        @if(!in_array($key, ['updated_at', 'created_at', 'id']))
                                                            <div class="flex items-start gap-2">
                                                                <span class="text-slate-500 font-bold shrink-0">{{ __(str_replace('_', ' ', Str::title($key))) }}:</span>
                                                                <span class="text-slate-700 dark:text-slate-300 break-all">{{ is_array($value) ? json_encode($value) : (string)($value ?? 'null') }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="font-bold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-1.5 text-xs">
                                                    <x-icon name="o-code-bracket" class="w-4 h-4 text-primary" /> {{ __('Details') }}
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px] font-mono">
                                                    @foreach($act->properties as $key => $value)
                                                        <div class="flex items-start gap-2">
                                                            <span class="text-slate-500 font-bold shrink-0">{{ __(str_replace('_', ' ', Str::title($key))) }}:</span>
                                                            <span class="text-slate-700 dark:text-slate-300 break-all">{{ is_array($value) ? json_encode($value) : (string)($value ?? 'null') }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 border border-dashed border-slate-200 dark:border-slate-800 rounded-3xl">
                                <x-icon name="o-clipboard-document" class="w-12 h-12 mx-auto mb-3 opacity-50 text-slate-350" />
                                <p class="font-bold text-slate-450 uppercase tracking-wider text-xs">{{ __('No activity logs found for your account.') }}</p>
                            </div>
                        @endforelse

                        <div class="mt-4">
                            {{ $this->activities->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ========================================== --}}
        {{-- ALPINE.JS TABS CONTROL --}}
        {{-- ========================================== --}}
        @script
        <script>
            Alpine.data('profile', () => ({
                activeTab: window.location.hash.slice(1) || 'view',

                init() {
                    this.$watch('activeTab', (value) => {
                        window.location.hash = value;
                    });

                    window.addEventListener('hashchange', () => {
                        const hash = window.location.hash.slice(1);
                        const validTabs = ['view', 'general', 'details', 'social', 'address', 'images', 'security', 'sessions', 'activities'];
                        if (hash && validTabs.includes(hash)) {
                            this.activeTab = hash;
                        }
                    });
                }
            }));
        </script>
        @endscript
    </div>
</div>