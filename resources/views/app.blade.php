<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#00a7ff" />

    <title inertia>Igreja em Charqueadas</title>
    <meta name="description" inertia="description"
          content="Uma igreja simples em Charqueadas/RS, vivendo o que Cristo ensinou." />

    {{-- Open Graph --}}
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Igreja em Charqueadas" />
    <meta property="og:locale" content="pt_BR" />
    <meta property="og:title" inertia="og:title" content="Igreja em Charqueadas" />
    <meta property="og:description" inertia="og:description"
          content="Uma igreja simples em Charqueadas/RS, vivendo o que Cristo ensinou." />
    <meta property="og:image" inertia="og:image" content="{{ url('/apple-touch-icon.png') }}" />
    <meta property="og:url" inertia="og:url" content="{{ url()->current() }}" />

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" inertia="twitter:title" content="Igreja em Charqueadas" />
    <meta name="twitter:description" inertia="twitter:description"
          content="Uma igreja simples em Charqueadas/RS, vivendo o que Cristo ensinou." />
    <meta name="twitter:image" inertia="twitter:image" content="{{ url('/apple-touch-icon.png') }}" />

    {{-- Icons --}}
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
  </head>
  <body class="bg-black text-white antialiased">
    @inertia
  </body>
</html>
