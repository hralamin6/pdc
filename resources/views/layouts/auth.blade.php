@extends('layouts.base')

@section('body')
    <div  class="bg-base-300">
        @yield('content')

        @isset($slot)
            {{ $slot }}
        @endisset
    </div>
@endsection
