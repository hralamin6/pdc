<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::auth')] #[Title('Sign in to your account')] class extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => ['required', 'email'],
        'password' => ['required', 'min:6'],
    ];
    public function quickLogin(string $role): void
    {
        if ($role === 'admin') {
            $this->email = 'admin@mail.com';
            $this->password = '000000';
        } elseif ($role === 'user') {
            $this->email = 'user@mail.com';
            $this->password = '000000';
        }

        $this->authenticate();
    }
    public function authenticate()
    {
        $this->validate();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', trans('auth.failed'));
            return;
        }
        return redirect()->intended(route('web.home'));
    }
};
?>

<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-900 dark:to-indigo-950">
    <div class="w-full max-w-md">
        {{-- Logo and Header --}}
        <div class="text-center mb-8">
            <a href="{{ route('web.home') }}" wire:navigate class="inline-block">
                <x-logo class="w-auto h-16 mx-auto text-indigo-600 dark:text-indigo-400" />
            </a>

            <h2 class="mt-6 text-3xl font-bold text-center">
                <span class="bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
                    {{ __('Welcome Back') }}
                </span>
            </h2>
            <p class="mt-2 text-sm text-center text-gray-600 dark:text-gray-400">
                {{ __('Sign in to continue to your account') }}
            </p>
        </div>

        <x-card class="shadow-2xl backdrop-blur-sm">
            <x-form wire:submit="authenticate">
                <x-input :label="__('Email Address')" wire:model="email" type="email" icon="o-envelope" :placeholder="__('you@example.com')" />

                {{-- Password Input --}}
                <x-password :label="__('Password')" wire:model="password" icon="o-lock-closed" :placeholder="__('Enter your password')" right clearable />

                {{-- Remember Me & Forgot Password --}}
                <div class="flex items-center justify-between mt-6">
                    <x-checkbox :label="__('Remember me')" wire:model="remember" />
                    @if (Route::has('password.request'))
                        <x-button link="{{ route('password.request') }}" :label="__('Forgot password?')" class="btn-ghost btn-sm text-indigo-600 dark:text-indigo-400" wire:navigate />
                    @endif
                </div>

                {{-- Submit Button --}}
                <x-button type="submit" :label="__('Sign In')" icon="o-arrow-right-on-rectangle" class="btn-primary w-full mt-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 border-0 shadow-lg hover:shadow-xl" spinner="authenticate" />

                {{-- Register Link --}}
                @if (Route::has('register'))
                    <x-slot:actions>
                        <div class="text-center w-full">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __("Don't have an account?") }}</span>
                            <x-button link="{{ route('register') }}" :label="__('Create one')" class="btn-ghost btn-sm text-indigo-600 dark:text-indigo-400 font-semibold" wire:navigate />
                        </div>
                    </x-slot:actions>
                @endif
            </x-form>
            <div class="flex justify-center gap-4 mt-6">
                <x-button  wire:click="quickLogin('admin')" icon="o-user-circle" class="btn-accent btn-md capitalize shadow-sm hover:shadow-md transition duration-150">
                    @lang('Admin')
                </x-button>

                <x-button         wire:click="quickLogin('user')"
                        icon="o-user"
                        class="btn-secondary btn-md capitalize shadow-sm hover:shadow-md transition duration-150"
                >
                    @lang('User')
                </x-button>
            </div>

            <!-- Social Login -->
            <div class="flex flex-col gap-3 mt-6">
                <x-button no-wire-navigate
                        tag="a"
                        link="{{ route('socialite.auth', 'google') }}"
                        class="btn-outline btn-md w-full justify-center bg-base-100 border-base-300 hover:bg-base-200 dark:bg-base-200 dark:border-base-300 transition duration-150"
                >
                    <x-icon name="fab.google" class="text-red-500" />
                    <span class="ml-2">@lang('Login with Google')</span>
                </x-button>

                <x-button  no-wire-navigate
                        tag="a"
                        link="{{ route('socialite.auth', 'github') }}"
                        icon="fab.github"
                        class="btn-outline btn-md w-full justify-center bg-base-100 border-base-300 hover:bg-base-200 dark:bg-base-200 dark:border-base-300 transition duration-150"
                >
                    <span class="ml-2">@lang('Login with GitHub')</span>
                </x-button>
            </div>
        </x-card>
    </div>
</div>