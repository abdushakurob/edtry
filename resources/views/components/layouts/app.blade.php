<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name') }}</title>
     @vite(['resources/js/app.js', 'resources/css/app.css'])
     
    <!-- Pass authenticated user data to JavaScript -->
    <script>
        window.Laravel = {
            csrfToken: "{{ csrf_token() }}",
            user: @json(auth()->user())
        }
    </script>
</head>
<body>
    <div id="app">
        {{ $slot }}
    </div>
</body>
</html>