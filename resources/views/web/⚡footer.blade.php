<?php

use Livewire\Component;
use App\Models\Feedback;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public bool $feedbackModal = false;
    public string $feedbackType = 'advice';
    public string $feedbackMessage = '';

    public function submitFeedback()
    {
        $this->validate([
            'feedbackType' => 'required|in:advice,complaint,suggestion',
            'feedbackMessage' => 'required|min:5|max:1000',
        ]);

        Feedback::create([
            'type' => $this->feedbackType,
            'message' => $this->feedbackMessage,
        ]);

        $this->reset(['feedbackMessage', 'feedbackModal', 'feedbackType']);
        $this->success(__('Feedback sent anonymously. JazakAllah Khair!'));
    }
};
?>

<footer class="bg-white dark:bg-[#050a15] text-slate-900 dark:text-white pt-20 pb-8 border-t border-slate-100 dark:border-white/5">
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
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
                    {{ __('Uniting students and alumni through faith, knowledge, and brotherhood at PSTU.') }}
                </p>
                <div class="flex gap-3">
                    <a href="#" class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-primary/10 dark:hover:bg-primary/20 hover:border-primary/30 transition-all">
                        <x-icon name="o-globe-alt" class="w-4 h-4" />
                    </a>
                    <a href="#" class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-primary/10 dark:hover:bg-primary/20 hover:border-primary/30 transition-all">
                        <x-icon name="o-envelope" class="w-4 h-4" />
                    </a>
                    <a href="#" class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-primary/10 dark:hover:bg-primary/20 hover:border-primary/30 transition-all">
                        <x-icon name="o-phone" class="w-4 h-4" />
                    </a>
                </div>
            </div>

            {{-- Platform --}}
            <div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-6 uppercase tracking-wider text-xs">{{ __('Platform') }}</h4>
                <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
                    <li><a href="{{ route('web.halaqahs') }}" wire:navigate class="hover:text-primary dark:hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-book-open" class="w-3.5 h-3.5" /> {{ __('Halaqahs & Sessions') }}</a></li>
                    <li><a href="{{ route('web.posts') }}" wire:navigate class="hover:text-primary dark:hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-newspaper" class="w-3.5 h-3.5" /> {{ __('Blog & Knowledge') }}</a></li>
                    <li><a href="{{ route('web.campaigns') }}" wire:navigate class="hover:text-primary dark:hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-heart" class="w-3.5 h-3.5" /> {{ __('Campaigns') }}</a></li>
                    <li><a href="{{ route('web.members') }}" wire:navigate class="hover:text-primary dark:hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-users" class="w-3.5 h-3.5" /> {{ __('Members') }}</a></li>
                </ul>
            </div>

            {{-- Resources --}}
            <div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-6 uppercase tracking-wider text-xs">{{ __('Resources') }}</h4>
                <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
                    <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-clock" class="w-3.5 h-3.5" /> {{ __('Prayer Times') }}</a></li>
                    <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-clipboard-document-check" class="w-3.5 h-3.5" /> {{ __('Daily Routine (App)') }}</a></li>
                    <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-document-text" class="w-3.5 h-3.5" /> {{ __('Study Materials') }}</a></li>
                    <li><a href="#" class="hover:text-primary dark:hover:text-white transition-colors flex items-center gap-2"><x-icon name="o-question-mark-circle" class="w-3.5 h-3.5" /> {{ __('FAQ') }}</a></li>
                </ul>
            </div>

            {{-- Anonymous Feedback --}}
            <div>
                <h4 class="font-bold text-slate-900 dark:text-white mb-6 uppercase tracking-wider text-xs">{{ __('Anonymous Nasiha') }}</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">{{ __('Have some advice or feedback? Send it to the Shura securely and anonymously.') }}</p>
                <button wire:click="$set('feedbackModal', true)" class="btn btn-block bg-gradient-to-r from-primary to-secondary border-none text-white rounded-xl font-bold shadow-lg shadow-primary/20 hover:scale-105 transition-transform">
                    <x-icon name="o-paper-airplane" class="w-4 h-4 mr-2" /> {{ __('Drop a Message') }}
                </button>
            </div>
        </div>

        {{-- Feedback Modal --}}
        <x-modal wire:model="feedbackModal" title="{{ __('Anonymous Feedback') }}" separator>
            <div class="space-y-4">
                <x-select label="{{ __('Type of Feedback') }}" wire:model="feedbackType" :options="[
                    ['id' => 'advice', 'name' => __('General Advice (Nasiha)')],
                    ['id' => 'suggestion', 'name' => __('Feature Suggestion')],
                    ['id' => 'complaint', 'name' => __('Complaint / Concern')]
                ]" option-value="id" option-label="name" />
                
                <x-textarea
                    label="{{ __('Your Message') }}"
                    wire:model="feedbackMessage"
                    placeholder="{{ __('Write your thoughts here... We don\'t track who sends this.') }}"
                    rows="4"
                />
            </div>
            <x-slot:actions>
                <x-button label="{{ __('Cancel') }}" @click="$wire.feedbackModal = false" />
                <x-button label="{{ __('Send Anonymously') }}" wire:click="submitFeedback" class="btn-primary" spinner="submitFeedback" />
            </x-slot:actions>
        </x-modal>

        {{-- Bottom Bar --}}
        <div class="pt-8 border-t border-slate-200 dark:border-white/5 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-sm text-slate-500 text-center md:text-left">
                &copy; {{ date('Y') }} {{ setting('app.name', 'PSTU Dawah Community') }}. {{ __('All rights reserved.') }}
            </p>
            <div class="flex items-center gap-3 text-sm text-slate-500">
                <x-theme-toggle class="btn btn-ghost btn-xs btn-circle text-slate-500 hover:text-slate-900 dark:hover:text-white" x-cloak />
                <span>{{ __('Built with') }}</span> <x-icon name="o-heart" class="w-4 h-4 text-rose-500" /> <span>{{ __('in Bangladesh') }}</span>
            </div>
        </div>
    </div>
</footer>
