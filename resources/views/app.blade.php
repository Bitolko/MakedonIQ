<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="MakedonIQ is a bilingual Macedonian learning quiz platform for language, culture, history, and geography.">
        <meta name="theme-color" content="#a40000">
        <meta property="og:title" content="MakedonIQ - Learn Macedonian Through Fun Quizzes">
        <meta property="og:description" content="MakedonIQ is a bilingual Macedonian learning quiz platform for language, culture, history, and geography.">
        <meta property="og:type" content="website">
        <link rel="icon" href="/favicon.ico">
        <title>MakedonIQ - Learn Macedonian Through Fun Quizzes</title>
        @php
            $authUser = auth()->check() ? [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'preferred_language' => auth()->user()->preferred_language ?? 'en',
                'is_admin' => auth()->user()->is_admin,
            ] : null;
        @endphp
        <script>
            window.MakedonIQ = {
                csrfToken: @json(csrf_token()),
                auth: {
                    user: @json($authUser),
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
