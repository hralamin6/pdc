<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::web')] #[Title('Create a new account')] class extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';

    public function rules(): array
    { 
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'regex:/^[^@]+@(gmail\.com|[a-zA-Z0-9-]+\.pstu\.ac\.bd)$/i'
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.regex' => __('Only @gmail.com and PSTU edu mails are allowed.'),
        ];
    }

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        event(new Registered($user));

        Auth::login($user);
        if (! $user->roles()->exists()) {
            $user->assignRole('user');
        }
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
                    {{ __('Create Account') }}
                </span>
            </h2>
            <p class="mt-2 text-sm text-center text-gray-600 dark:text-gray-400">
                {{ __('Create your account to get started') }}
            </p>
        </div>

        <x-card class="shadow-2xl backdrop-blur-sm">
            <x-form wire:submit="register">
                <x-input :label="__('Full Name')" wire:model="name" type="text" icon="o-user" :placeholder="__('John Doe')" />

                <x-input :label="__('Email Address')" wire:model="email" type="email" icon="o-envelope" :placeholder="__('you@gmail.com')" :hint="__('Only @gmail.com or @departmentName.pstu.ac.bd emails')" />

                <x-password :label="__('Password')" wire:model="password" icon="o-lock-closed" :placeholder="__('Enter your password')" :hint="__('Minimum 8 characters')" right clearable />

                <x-password :label="__('Confirm Password')" wire:model="password_confirmation" icon="o-lock-closed" :placeholder="__('Confirm your password')" right clearable />

                <x-button type="submit" :label="__('Create Account')" icon="o-user-plus" class="btn-primary w-full mt-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 border-0 shadow-lg hover:shadow-xl" spinner="register" />

                @if (Route::has('login'))
                    <x-slot:actions>
                        <div class="text-center w-full">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Already have an account?') }}</span>
                            <x-button link="{{ route('login') }}" :label="__('Sign in')" class="btn-ghost btn-sm text-indigo-600 dark:text-indigo-400 font-semibold" wire:navigate />
                        </div>
                    </x-slot:actions>
                @endif
            </x-form>
        </x-card>
    </div>
</div>