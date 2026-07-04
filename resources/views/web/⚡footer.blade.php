<?php

use Livewire\Component;

new class extends Component {};
?>

<footer class="bg-white dark:bg-[#080d1a] border-t border-base-content/10 pt-16 pb-8 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
            
            {{-- Brand Column --}}
            <div class="col-span-1 md:col-span-1">
                <a href="{{ route('web.home') }}" wire:navigate class="flex items-center gap-3 mb-6 group">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white shadow-md shadow-primary/20">
                        <x-icon name="o-moon" class="w-5 h-5" />
                    </div>
                    <span class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                        {{ setting('app.name', 'PSTU Dawah') }}
                    </span>
                </a>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
                    Uniting students and alumni through faith, knowledge, and brotherhood/sisterhood at PSTU.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center text-slate-500 hover:text-primary hover:bg-primary/10 transition-colors">
                        <x-icon name="o-globe-alt" class="w-4 h-4" />
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center text-slate-500 hover:text-primary hover:bg-primary/10 transition-colors">
                        <x-icon name="o-envelope" class="w-4 h-4" />
                    </a>
                </div>
            </div>

            {{-- Links --}}
            <div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-6 uppercase tracking-wider text-xs">Platform</h4>
                <ul class="space-y-4 text-sm text-slate-500 dark:text-slate-400">
                    <li><a href="#" class="hover:text-primary transition-colors">Halaqahs & Events</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">Knowledge Base</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">Community Forum</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">Charity & Sadaqah</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-6 uppercase tracking-wider text-xs">Resources</h4>
                <ul class="space-y-4 text-sm text-slate-500 dark:text-slate-400">
                    <li><a href="#" class="hover:text-primary transition-colors">Campus Mentors</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">Prayer Times</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">Study Materials</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">FAQ</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-6 uppercase tracking-wider text-xs">Legal</h4>
                <ul class="space-y-4 text-sm text-slate-500 dark:text-slate-400">
                    <li><a href="#" class="hover:text-primary transition-colors">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">Terms of Service</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">Community Guidelines</a></li>
                    <li><a href="#" class="hover:text-primary transition-colors">Contact Us</a></li>
                </ul>
            </div>

        </div>

        <div class="pt-8 border-t border-base-content/10 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-sm text-slate-500 dark:text-slate-400 text-center md:text-left">
                &copy; {{ date('Y') }} {{ setting('app.name', 'PSTU Dawah Community') }}. All rights reserved.
            </p>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>Built with</span> <x-icon name="o-heart" class="w-4 h-4 text-error" /> <span>in Bangladesh</span>
            </div>
        </div>
    </div>
</footer>
