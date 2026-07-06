<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::auth')] #[Title('Confirm Password')] class extends Component
{
    public $password = '';

    public function confirm()
    {
        $this->validate([
            'password' => 'required|current_password',
        ]);

        session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('web.home'));
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
                    {{ __('Confirm Password') }}
                </span>
            </h2>
            <p class="mt-2 text-sm text-center text-gray-600 dark:text-gray-400">
                {{ __('Please confirm your password before continuing') }}
            </p>
        </div>

        <x-card class="shadow-2xl backdrop-blur-sm">
            <div class="text-center mb-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 dark:bg-indigo-900/30 mb-4">
                    <x-icon name="o-shield-check" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
                </div>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
                </p>
            </div>

            <x-form wire:submit="confirm">
                <x-password :label="__('Password')" wire:model="password" icon="o-lock-closed" :placeholder="__('Enter your password')" right clearable autofocus />

                <x-button type="submit" :label="__('Confirm')" icon="o-check-circle" class="btn-primary w-full mt-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 border-0 shadow-lg hover:shadow-xl" spinner="confirm" />
            </x-form>
        </x-card>
    </div>
</div>