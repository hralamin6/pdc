@extends('layouts.base')

@section('body')

{{-- Root Container --}} 
<div class="h-[100dvh] flex flex-col font-sans antialiased bg-base-200/50 dark:bg-base-200 overflow-visible">

    {{-- APP MAIN AREA --}}
    <x-main full-width class="flex-1 min-h-0">

        {{-- SIDEBAR --}}
        @persist('sidebar')
        <x-slot:sidebar drawer="main-drawer" collapsible collapse-text="{{ __('Hide it') }}" class="bg-base-100 border-r border-base-content/10 flex flex-col h-full">


            {{-- Sidebar Header (User) --}}
            <div class="sticky top-0 z-10 bg-base-100/70">
                @if($user = auth()->user())
                    <x-list-item :item="$user" no-separator no-hover class="!m-0 pl-2 !pb-0">
                        <x-slot:avatar>
                            <div class="avatar ml-2">
                                <div class="w-8 h-8 rounded-full bg-base-300 ring-2 ring-primary/20">
                                    <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" alt="{{ $user->name }}" class="object-cover" />
                                </div>
                            </div>
                        </x-slot:avatar>
                        <x-slot:actions>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                            <x-button icon="o-power" class="btn-circle btn-ghost btn-xs text-error shadow-none hover:scale-105 transition-transform" tooltip-left="{{ __('Logoff') }}" no-wire-navigate onclick="document.getElementById('logout-form').submit();" />
                        </x-slot:actions>
                    </x-list-item>
                    <x-menu-separator class="opacity-70" />
                @endif
            </div>
            {{-- Scrollable Sidebar Menu --}}
            <div class="flex-1 overflow-y-auto pb-2 mt-2">
                <x-menu activate-by-route class="px-2 !py-0">


                    {{-- Dashboard --}}
                    <x-menu-item title="{{ __('Dashboard') }}" icon="o-home" :link="route('app.dashboard')" route="app.dashboard" wire:navigate />
                    
                    {{-- Daily Reports --}}
                    <x-menu-item title="{{ __('Daily Reports') }}" icon="o-clipboard-document-check" :link="route('app.daily-reports')" route="app.daily-reports" wire:navigate />
                    {{-- Halaqahs --}}
                    <x-menu-item title="{{ __('Halaqahs') }}" icon="o-book-open" :link="route('app.halaqahs')" route="app.halaqahs" wire:navigate />
                    
                    {{-- Donations --}}
                    <x-menu-sub title="{{ __('Donations') }}" icon="o-banknotes">
                        <x-menu-item title="{{ __('My Donations') }}" icon="o-heart" :link="route('app.donations')" route="app.donations" wire:navigate />
                        @if(auth()->user() && auth()->user()->hasRole(['super-admin', 'admin', 'accountant']))
                            <x-menu-item title="{{ __('Admin Dashboard') }}" icon="o-chart-bar" :link="route('app.donations.admin')" route="app.donations.admin" wire:navigate />
                        @endif
                    </x-menu-sub>

                    {{-- Treasury --}}
                    <x-menu-item title="{{ __('Financial Summary') }}" icon="o-currency-bangladeshi" :link="route('app.expenses')" route="app.expenses" wire:navigate />
                    @if(auth()->user() && auth()->user()->hasRole(['super-admin', 'admin', 'accountant']))
                        <x-menu-sub title="{{ __('Treasury') }}" icon="o-building-library">
                            <x-menu-item title="{{ __('Expenses') }}" icon="o-receipt-percent" :link="route('app.expenses.admin')" route="app.expenses.admin" wire:navigate />
                            <x-menu-item title="{{ __('Fund Transfers') }}" icon="o-arrows-right-left" :link="route('app.fund-transfers')" route="app.fund-transfers" wire:navigate />
                            <x-menu-item title="{{ __('Bank Accounts') }}" icon="o-credit-card" :link="route('app.bank-accounts')" route="app.bank-accounts" wire:navigate />
                            <x-menu-item title="{{ __('Categories') }}" icon="o-tag" :link="route('app.expense-categories')" route="app.expense-categories" wire:navigate />
                            <x-menu-item title="{{ __('Monthly Reports') }}" icon="o-document-chart-bar" :link="route('app.treasury-report')" route="app.treasury-report" wire:navigate />
                        </x-menu-sub>
                    @endif

                    {{-- Library --}}
                    <x-menu-sub title="{{ __('Library') }}" icon="o-book-open">
                        <x-menu-item title="{{ __('Browse Catalog') }}" icon="o-squares-2x2" :link="route('app.books')" route="app.books" wire:navigate />
                        <x-menu-item title="{{ __('Community Hubs') }}" icon="o-building-library" :link="route('app.library-hubs')" route="app.library-hubs" wire:navigate />
                        <x-menu-item title="{{ __('My Shelf') }}" icon="o-bookmark-square" :link="route('app.my-books')" route="app.my-books" wire:navigate />
                        <x-menu-item title="{{ __('Borrow Requests') }}" icon="o-inbox" :link="route('app.borrow-requests')" route="app.borrow-requests" wire:navigate />
                        
                        @if(auth()->user() && auth()->user()->hasRole(['super-admin', 'admin', 'mentor']))
                            <x-menu-item title="{{ __('Manage Catalog') }}" icon="o-cog" :link="route('app.books.admin')" route="app.books.admin" wire:navigate />
                            <x-menu-item title="{{ __('Metadata (Authors)') }}" icon="o-tag" :link="route('app.books.metadata')" route="app.books.metadata" wire:navigate />
                        @endif
                    </x-menu-sub>


                    {{-- Quizzes --}}
                    @can('quiz.view')
                    <x-menu-sub title="{{ __('Quizzes') }}" icon="o-question-mark-circle">
                        <x-menu-item title="{{ __('Browse Quizzes') }}" icon="o-squares-2x2" :link="route('app.quiz.manage')" route="app.quiz.manage" wire:navigate />
                        @can('quiz.manage')
                            <x-menu-item title="{{ __('Manage Quizzes') }}" icon="o-cog-6-tooth" :link="route('app.quiz.manage')" route="app.quiz.manage" wire:navigate />
                        @endcan
                        @can('quiz.grade')
                            <x-menu-item title="{{ __('Grade Answers') }}" icon="o-check-badge" :link="route('app.quiz.grade')" route="app.quiz.grade" wire:navigate />
                        @endcan
                        @livewire('quizzes.live-quiz-badge')
                    </x-menu-sub>
                    @endcan

                    {{-- User Settings --}}
                    <x-menu-sub title="{{ __('User Settings') }}" icon="o-user">
                        <x-menu-item title="{{ __('Profile') }}" icon="o-user-circle" :link="route('app.profile')" route="app.profile" wire:navigate />
                        <x-menu-item title="{{ __('Chat') }}" icon="o-chat-bubble-left-right" :link="route('app.chat')" route="app.chat" wire:navigate />
                        <x-menu-item title="{{ __('AI Chat') }}" icon="o-chat-bubble-left-right" :link="route('app.ai-chat')" route="app.ai-chat" wire:navigate />
                        {{-- <x-menu-item title="{{ __('AskAI (DB)') }}" icon="o-circle-stack" :link="route('app.askai')" route="app.askai" wire:navigate /> --}}
                        @can('activity.my')
                            <x-menu-item title="{{ __('Push Notifications') }}" icon="o-bell" :link="route('app.notifications')" route="app.notifications" wire:navigate />
                            <x-menu-item title="{{ __('My Activities') }}" icon="o-clock" :link="route('app.activity.my')" route="app.activity.my" wire:navigate />
                        @endcan
                    </x-menu-sub>

                    {{-- Root Settings --}}
                    <x-menu-sub title="{{ __('Root Settings') }}" icon="o-cog-6-tooth">
                        <x-menu-item title="{{ __('Settings') }}" icon="o-adjustments-horizontal" :link="route('app.settings')" route="app.settings" wire:navigate />
                        
                        @auth
                            {{-- <x-menu-item title="{{ __('Privacy & Security') }}" icon="o-shield-check" :link="route('two-factor.setup')" route="two-factor.setup" wire:navigate /> --}}
                        @endauth
                        
                        @can('roles.view')
                            <x-menu-item title="{{ __('Roles') }}" icon="o-shield-check" :link="route('app.roles')" route="app.roles" wire:navigate />
                        @endcan
                        
                        @can('feedback.manage')
                            <x-menu-item title="{{ __('Anonymous Nasiha') }}" icon="o-inbox" :link="route('app.feedback.admin')" route="app.feedback.admin" wire:navigate />
                        @endcan

                        @can('gallery.manage')
                            <x-menu-item title="{{ __('Showcase Gallery') }}" icon="o-photo" :link="route('app.gallery.admin')" route="app.gallery.admin" wire:navigate />
                        @endcan
                        
                        @can('users.view')
                            <x-menu-item title="{{ __('Users') }}" icon="o-users" :link="route('app.users')" route="app.users" wire:navigate />
                        @endcan

                        <x-menu-item title="{{ __('Backups') }}" icon="o-cloud" :link="route('app.backups')" route="app.backups" wire:navigate />
                        <x-menu-item title="{{ __('Translations') }}" icon="o-language" :link="route('app.translate')" route="app.translate" wire:navigate />
                        <x-menu-item title="{{ __('Pages') }}" icon="o-document-text" :link="route('app.pages')" route="app.pages" wire:navigate />

                        @can('activity.feed')
                            <x-menu-item title="{{ __('Activity Feed') }}" icon="o-list-bullet" :link="route('app.activity.feed')" route="app.activity.feed" wire:navigate />
                        @endcan
                    </x-menu-sub>

                </x-menu>
            </div>

        </x-slot:sidebar>
        @endpersist

        {{-- CONTENT --}}
        <x-slot:content class="bg-base-200/50 flex flex-col h-full flex-1 min-h-0 !p-0">

           {{-- Ensure the header component matches the alias you are using. --}}
   <div class="sticky top-0 z-10 bg-base-200/50"><livewire:app::header/></div> 
            {{-- Main Scrollable Content Area --}}
            <div class="flex-1 overflow-y-auto">
                <main class="px-3 md:px-6 lg:px-8 py-6 relative min-h-full">
                    @yield('content')
                    @isset($slot)
                        {{ $slot }}
                    @endisset
                </main>
            </div>

        </x-slot:content>
    </x-main>

    {{-- Global UI Components --}}
    <x-toast />
    <x-spotlight shortcut="meta.k" />
</div>
@endsection
