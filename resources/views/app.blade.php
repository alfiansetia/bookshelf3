<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: hsl(40 30% 98%);
            }

            html.dark {
                background-color: hsl(200 12% 8%);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="manifest" href="/manifest.webmanifest">

        {{-- PWA & warna antarmuka peramban --}}
        <meta name="theme-color" content="{{ config('bookshelf.pwa.theme_color') }}" media="(prefers-color-scheme: light)">
        <meta name="theme-color" content="{{ config('bookshelf.pwa.theme_color_dark') }}" media="(prefers-color-scheme: dark)">
        <meta name="application-name" content="{{ config('bookshelf.pwa.short_name') }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="{{ config('bookshelf.pwa.short_name') }}">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>

            {{-- Bawaan yang dipakai saat SSR mati; halaman publik menimpanya lewat head-key yang sama. --}}
            <meta data-inertia="description" name="description" content="{{ config('bookshelf.seo.description') }}">
            <meta data-inertia="og:site_name" property="og:site_name" content="{{ config('bookshelf.seo.title_suffix') }}">
            <meta data-inertia="og:type" property="og:type" content="website">
            <meta data-inertia="og:title" property="og:title" content="{{ config('app.name', 'Laravel') }}">
            <meta data-inertia="og:description" property="og:description" content="{{ config('bookshelf.seo.description') }}">
            <meta data-inertia="og:image" property="og:image" content="{{ url(config('bookshelf.seo.image')) }}">
            <meta data-inertia="og:image:width" property="og:image:width" content="1200">
            <meta data-inertia="og:image:height" property="og:image:height" content="630">
            <meta data-inertia="og:locale" property="og:locale" content="{{ config('bookshelf.seo.locale') }}">
            <meta data-inertia="twitter:card" name="twitter:card" content="summary_large_image">
            <meta data-inertia="twitter:title" name="twitter:title" content="{{ config('app.name', 'Laravel') }}">
            <meta data-inertia="twitter:description" name="twitter:description" content="{{ config('bookshelf.seo.description') }}">
            <meta data-inertia="twitter:image" name="twitter:image" content="{{ url(config('bookshelf.seo.image')) }}">
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
