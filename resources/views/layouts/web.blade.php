@extends('layouts.base')

@section('body')
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-gray-900 dark:via-slate-900 dark:to-indigo-950">
        
        {{-- Header --}}
        <livewire:web::header />

        {{-- Main Content --}}
        <main>
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <livewire:web::footer />
        
        {{-- Toast --}}
        <x-toast />
    </div>
@endsection
