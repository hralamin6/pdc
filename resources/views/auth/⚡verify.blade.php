<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::auth')] #[Title('Verify Your Email')] class extends Component {
    public $resent = false;

    public function mount()
    {
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->intended(route('web.home'));
        }
    }
 
    public function resend()
    {
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->intended(route('web.home'));
        }

        Auth::user()->sendEmailVerificationNotification();

        $this->resent = true;

        session()->flash('resent', __('A fresh verification link has been sent to your email address.'));
    }
};
?>

<div
    class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-indigo-950">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="{{ route('web.home') }}" wire:navigate class="inline-block">
                <x-logo class="w-auto h-16 mx-auto text-indigo-600 dark:text-indigo-400" />
            </a>

            <h2 class="mt-6 text-3xl font-bold text-center">
                <span
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
                    {{ __('Verify Your Email') }}
                </span>
            </h2>
            <p class="mt-2 text-sm text-center text-gray-600 dark:text-gray-400">
                {{ __('Check your inbox for the verification link') }}
            </p>
        </div>

        <x-card class="shadow-2xl backdrop-blur-sm">
            <div class="text-center">
                <div
                    class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 dark:bg-indigo-900/30 mb-4">
                    <x-icon name="o-envelope" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
                </div>

                <p class="text-gray-700 dark:text-gray-300 mb-6">
                    {{ __('Before proceeding, please check your email for a verification link. If you did not receive the email, click the button below to request another.') }}
                </p>

                @if (session('resent'))
                    <x-alert icon="o-check-circle" class="mb-6 alert-success">
                        {{ session('resent') }}
                    </x-alert>
                @endif

                <x-form wire:submit="resend">
                    <x-button type="submit" :label="__('Resend Verification Email')" icon="o-paper-airplane"
                        class="btn-primary w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 border-0 shadow-lg hover:shadow-xl"
                        spinner="resend" />
                </x-form>

                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Wrong email address?') }}
                    </p>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <x-button type="submit" :label="__('Sign out and register again')"
                            class="btn-ghost btn-sm text-indigo-600 dark:text-indigo-400 font-semibold mt-2" />
                    </form>
                </div>
            </div>
        </x-card>
    </div>
</div>