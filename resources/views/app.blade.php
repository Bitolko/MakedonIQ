<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>MakedonIQ</title>
        <script>
            window.MakedonIQ = {
                csrfToken: @json(csrf_token()),
                auth: {
                    user: @json(auth()->check() ? [
                        'name' => auth()->user()->name,
                        'email' => auth()->user()->email,
                    ] : null),
                },
                old: @json(session()->getOldInput()),
                errors: @json($errors->getMessages()),
                status: @json(session('status')),
            };
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div id="app" data-page="{{ $page ?? 'Home' }}"></div>
    </body>
</html>
