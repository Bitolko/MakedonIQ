<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <link rel="icon" href="/favicon.ico">
        <title>Access Restricted | MakedonIQ</title>
        @vite(['resources/css/app.css'])
    </head>
    <body>
        <main class="flex min-h-screen items-center bg-heritage-bg py-12">
            <section class="page-shell">
                <div class="mx-auto max-w-3xl overflow-hidden rounded-[2rem] bg-white shadow-soft">
                    <div class="heritage-pattern p-8 text-white md:p-10">
                        <span class="inline-flex rounded-full bg-white/15 px-4 py-2 text-xs font-black uppercase">403</span>
                        <h1 class="mt-5 text-4xl font-black md:text-5xl">You do not have access to this area.</h1>
                        <p class="mt-4 max-w-2xl text-lg leading-8 text-white/80">
                            Admin pages and private results are protected. Log in with the right account to continue.
                        </p>
                    </div>
                    <div class="grid gap-3 p-6 sm:grid-cols-2 md:p-8">
                        <a href="/dashboard" class="pressable-red inline-flex justify-center rounded-2xl px-6 py-3 text-sm font-black">Go to Dashboard</a>
                        <a href="/login" class="button-soft inline-flex justify-center rounded-2xl px-6 py-3 text-sm font-black">Login</a>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
