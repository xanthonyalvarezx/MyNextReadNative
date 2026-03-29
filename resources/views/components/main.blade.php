<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MY NEXT READ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="app-body">
    @include('components.nav')
    <main class="app-main">
        {{ $slot }}
    </main>
</body>

</html>
