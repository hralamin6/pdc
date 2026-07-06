<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::auth')] #[Title('Reset Password')] class extends Component
{
    public $email = '';
    public $emailSentMessage = false;

    public function sendResetPasswordLink()
    {
        $this->validate([
            'email' => ['required', 'email'],
        ]);

        $response = Password::broker()->sendResetLink(['email' => $this->email]);

        if ($response == Password::RESET_LINK_SENT) {
            $this->emailSentMessage = trans($response);
            return;
        }

        $this->addError('email', trans($response));
    }
};
?>

<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-indigo-950">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="{{ route('web.home') }}" wire:navigate class="inline-block">
                <x-logo class="w-auto h-16 mx-auto text-indigo-600 dark:text-indigo-400" />
            </a>

            <h2 class="mt-6 text-3xl font-bold text-center">
                <span class="bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
                    {{ __('Reset Password') }}
                </span>
            </h2>
            <p class="mt-2 text-sm text-center text-gray-600 dark:text-gray-400">
                {{ __('Enter your email to receive a reset link') }}
            </p>
        </div>

        <x-card class="shadow-2xl backdrop-blur-sm">
            @if ($emailSentMessage)
                <x-alert icon="o-check-circle" class="mb-6 alert-success">
                    {{ $emailSentMessage }}
                </x-alert>
                
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {{ __('Check your email for the password reset link.') }}
                    </p>
                    <x-button link="{{ route('login') }}" :label="__('Back to Login')" class="btn-ghost text-indigo-600 dark:text-indigo-400" wire:navigate />
                </div>
            @else
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 dark:bg-indigo-900/30 mb-4">
                        <x-icon name="o-key" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.') }}
                    </p>
                </div>

                <x-form wire:submit="sendResetPasswordLink">
                    <x-input :label="__('Email Address')" wire:model="email" type="email" icon="o-envelope" :placeholder="__('you@example.com')" />

                    <x-button type="submit" :label="__('Send Reset Link')" icon="o-paper-airplane" class="btn-primary w-full mt-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 border-0 shadow-lg hover:shadow-xl" spinner="sendResetPasswordLink" />

                    <x-slot:actions>
                        <div class="text-center w-full">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Remember your password?') }}</span>
                            <x-button link="{{ route('login') }}" :label="__('Sign in')" class="btn-ghost btn-sm text-indigo-600 dark:text-indigo-400 font-semibold" wire:navigate />
                        </div>
                    </x-slot:actions>
                </x-form>
            @endif
        </x-card>
    </div>
</div>