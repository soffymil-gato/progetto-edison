@php
    /** @var \App\Support\Area $area */
    $area = \App\Support\Area::current();
    $seo = $seo ?? null;
@endphp
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071a3d">
    @if (! empty($referrerPolicy))
        <meta name="referrer" content="{{ $referrerPolicy }}">
    @endif
    @if ($area !== \App\Support\Area::Public)
        <meta name="robots" content="noindex, nofollow">
    @endif

    @if ($seo)
        <title inertia>{{ $seo['title'] }}</title>
        @if ($seo['description'])
            <meta name="description" content="{{ $seo['description'] }}" inertia="description">
            <meta property="og:description" content="{{ $seo['description'] }}">
        @endif
        <link rel="canonical" href="{{ $seo['canonical'] }}">
        <meta property="og:type" content="website">
        <meta property="og:locale" content="it_IT">
        <meta property="og:site_name" content="{{ $seo['siteName'] }}">
        <meta property="og:title" content="{{ $seo['title'] }}">
        <meta property="og:url" content="{{ $seo['canonical'] }}">
        @if ($seo['jsonLd'])
            <script type="application/ld+json" nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">{!! json_encode($seo['jsonLd'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
        @endif
    @else
        <title inertia>{{ config('app.name') }} — {{ $area->label() }}</title>
    @endif

    <meta name="csp-nonce" content="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    @viteReactRefresh
    @vite(['resources/js/'.$area->value.'.tsx'])
    @inertiaHead
</head>
<body class="area-{{ $area->value }}">
    @inertia
</body>
</html>
