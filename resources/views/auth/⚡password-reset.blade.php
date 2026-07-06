<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::auth')] #[Title('Reset Password')] class extends Component
{
    public $token;
    public $email = '';
    public $password = '';
    public $password_confirmation = '';

    public function mount($token)
    {
        $this->email = request()->query('email', '');
        $this->token = $token;
    }

    public function resetPassword()
    {
        $this->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $response = Password::broker()->reset(
            [
                'token' => $this->token,
                'email' => $this->email,
                'password' => $this->password
            ],
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));

                Auth::guard()->login($user);
            }
        );

        if ($response == Password::PASSWORD_RESET) {
            session()->flash('status', trans($response));
            return redirect()->intended(route('web.home'));        }

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
                {{ __('Enter your new password') }}
            </p>
        </div>

        <x-card class="shadow-2xl backdrop-blur-sm">
            <div class="text-center mb-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 dark:bg-indigo-900/30 mb-4">
                    <x-icon name="o-lock-closed" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
                </div>
            </div>

            <x-form wire:submit="resetPassword">
                <x-input :label="__('Email Address')" wire:model="email" type="email" icon="o-envelope" :placeholder="__('you@example.com')" readonly />

                <x-password :label="__('New Password')" wire:model="password" icon="o-lock-closed" :placeholder="__('Enter new password')" :hint="__('Minimum 8 characters')" right clearable />

                <x-password :label="__('Confirm Password')" wire:model="password_confirmation" icon="o-lock-closed" :placeholder="__('Confirm new password')" right clearable />

                <x-button type="submit" :label="__('Reset Password')" icon="o-check" class="btn-primary w-full mt-6 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 border-0 shadow-lg hover:shadow-xl" spinner="resetPassword" />
            </x-form>
        </x-card>
    </div>
</div>