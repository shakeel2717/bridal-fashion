<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Dulhan House') }} — @yield('title', 'Login')</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @livewireStyles
</head>
<body style="overflow:auto;">

<div class="auth-wrapper">
    @yield('content')
</div>

@livewireScripts
@stack('scripts')
</body>
</html>