<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-canvas">
    <main class="relative flex min-h-dvh items-center justify-center overflow-hidden px-6 py-16">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(40rem_22rem_at_50%_0%,rgb(42_111_255/0.14),transparent_70%)]" aria-hidden="true"></div>
        <div class="relative w-full max-w-lg text-center">
            <p class="font-display text-7xl font-semibold tracking-tight text-brand-200 sm:text-8xl">@yield('code')</p>
            <h1 class="mt-4 text-2xl font-semibold text-ink sm:text-3xl">@yield('title')</h1>
            <p class="mt-3 text-base leading-relaxed text-fg-muted">@yield('message')</p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="inline-flex h-11 items-center justify-center rounded-control border border-line-strong bg-surface px-5 text-sm font-medium text-fg hover:bg-brand-50">Torna indietro</a>
                <a href="{{ url('/') }}" class="inline-flex h-11 items-center justify-center rounded-control bg-brand-600 px-5 text-sm font-medium text-white hover:bg-brand-700">Vai alla home</a>
            </div>
        </div>
    </main>
</body>
</html>
