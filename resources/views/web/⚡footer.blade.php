<?php

use Livewire\Component;

new class extends Component {};
?>

<footer class="bg-slate-900 dark:bg-[#050a15] text-white pt-20 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">

            {{-- Brand --}}
            <div class="lg:col-span-1">
                <a href="{{ route('web.home') }}" wire:navigate class="flex items-center gap-3 mb-6 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg shadow-primary/20">
                        <x-icon name="o-moon" class="w-5 h-5 text-white" />
                    </div>
                    <span class="text-xl font-black tracking-tight">{{ setting('app.name', 'PSTU Dawah') }}</span>
                </a>
                <p class="text-sm text-slate-400 mb-6 leading-relaxed">
                    Uniting students and alumni through faith, knowledge, and brotherhood at PSTU.
                </p>
                <div class="flex gap-3">
                    <a href="#" class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white hover:bg-primary/20 hover:border-primary/30 transition-all">
                        <x-icon name="o-globe-alt" class="w-4 h-4" />
                    </a>
                    <a href="#" class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white hover:bg-primary/20 hover:border-primary/30 transition-all">
                        <x-icon name="o-envelope" class="w-4 h-4" />
                    </a>
                    <a href="#" class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white hover:bg-primary/20 hover:border-primary/30 transition-all">
                        <x-icon name="o-phone" class="w-4 h-4" />
                    </a>
                </div>
            </div>

            {{-- Platform --}}
            <div>
                <h4 class="font-bold text-white mb-6 uppercase tracking-wider text-xs">Platform</h4>
                <ul class="space-y-3 text-sm text-slate-400">
                    <li><a href="{{ route('web.halaqahs') }}" wire:navigate class="hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-book-open" class="w-3.5 h-3.5" /> Halaqahs & Sessions</a></li>
                    <li><a href="{{ route('web.posts') }}" wire:navigate class="hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-newspaper" class="w-3.5 h-3.5" /> Blog & Knowledge</a></li>
                    <li><a href="{{ route('web.campaigns') }}" wire:navigate class="hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-heart" class="w-3.5 h-3.5" /> Campaigns</a></li>
                    <li><a href="{{ route('web.members') }}" wire:navigate class="hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-users" class="w-3.5 h-3.5" /> Members</a></li>
                </ul>
            </div>

            {{-- Resources --}}
            <div>
                <h4 class="font-bold text-white mb-6 uppercase tracking-wider text-xs">Resources</h4>
                <ul class="space-y-3 text-sm text-slate-400">
                    <li><a href="#" class="hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-clock" class="w-3.5 h-3.5" /> Prayer Times</a></li>
                    <li><a href="#" class="hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-clipboard-document-check" class="w-3.5 h-3.5" /> Daily Routine (App)</a></li>
                    <li><a href="#" class="hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-document-text" class="w-3.5 h-3.5" /> Study Materials</a></li>
                    <li><a href="#" class="hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-question-mark-circle" class="w-3.5 h-3.5" /> FAQ</a></li>
                </ul>
            </div>

            {{-- Newsletter --}}
            <div>
                <h4 class="font-bold text-white mb-6 uppercase tracking-wider text-xs">Stay Updated</h4>
                <p class="text-sm text-slate-400 mb-4">Get notified about new sessions and community updates.</p>
                <div class="flex gap-2">
                    <input type="email" placeholder="Your email..." class="flex-1 px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-primary/50 focus:bg-white/10 transition-all" />
                    <button class="btn btn-sm bg-gradient-to-r from-primary to-secondary border-none text-white rounded-xl px-4 font-bold shadow-lg shadow-primary/20 hover:scale-105 transition-transform">
                        <x-icon name="o-paper-airplane" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        {{-- Bottom Bar --}}
        <div class="pt-8 border-t border-white/5 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-sm text-slate-500 text-center md:text-left">
                &copy; {{ date('Y') }} {{ setting('app.name', 'PSTU Dawah Community') }}. All rights reserved.
            </p>
            <div class="flex items-center gap-3 text-sm text-slate-500">
                <x-theme-toggle class="btn btn-ghost btn-xs btn-circle text-slate-500 hover:text-white" x-cloak />
                <span>Built with</span> <x-icon name="o-heart" class="w-4 h-4 text-rose-500" /> <span>in Bangladesh</span>
            </div>
        </div>
    </div>
</footer>
