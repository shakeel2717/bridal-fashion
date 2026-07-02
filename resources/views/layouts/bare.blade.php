<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Software Activation</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    @yield('content')
    @livewireScripts
</body>
</html>