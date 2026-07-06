@php
    // Meta por página, renderizada no servidor (crawlers e redes sociais leem no HTML inicial).
    // Os controllers passam a prop `meta`; sem ela, caem nos defaults abaixo.
    $siteName = config('app.name', 'Igreja em Charqueadas');
    $meta = $page['props']['meta'] ?? [];
    $defaultDesc = 'Uma igreja simples em Charqueadas/RS, vivendo o que Cristo ensinou.';

    $metaTitle = $meta['title'] ?? $siteName;
    $metaDesc = $meta['description'] ?? $defaultDesc;
    $ogTitle = $meta['og_title'] ?? $metaTitle;
    $ogDesc = $meta['og_description'] ?? $metaDesc;
    $ogImage = $meta['og_image'] ?? url('/apple-touch-icon.png');
    $canonical = url()->current();

    $jsonLd = $page['props']['jsonLd'] ?? null;

    // Construído aqui (dentro do @php) para o Blade não confundir '@context'/'@type' com diretivas.
    $churchLd = [
        '@context' => 'https://schema.org',
        '@type' => 'Church',
        'name' => $siteName,
        'url' => url('/'),
        'description' => $defaultDesc,
        'telephone' => '+5551992824071',
        'image' => url('/apple-touch-icon.png'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Rua Rui Barbosa, 1433',
            'addressLocality' => 'Charqueadas',
            'addressRegion' => 'RS',
            'addressCountry' => 'BR',
        ],
        'areaServed' => 'Charqueadas, RS',
    ];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#00a7ff" />

    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}" />
    <link rel="canonical" href="{{ $canonical }}" />

    {{-- Open Graph --}}
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{ $siteName }}" />
    <meta property="og:locale" content="pt_BR" />
    <meta property="og:title" content="{{ $ogTitle }}" />
    <meta property="og:description" content="{{ $ogDesc }}" />
    <meta property="og:image" content="{{ $ogImage }}" />
    <meta property="og:url" content="{{ $canonical }}" />

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $ogTitle }}" />
    <meta name="twitter:description" content="{{ $ogDesc }}" />
    <meta name="twitter:image" content="{{ $ogImage }}" />

    {{-- Icons --}}
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />

    {{-- Dados estruturados: identidade da igreja (presente em todas as páginas) --}}
    <script type="application/ld+json">
    {!! json_encode($churchLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    {{-- Dados estruturados específicos da página (culto, evento), quando houver --}}
    @if ($jsonLd)
    <script type="application/ld+json">
    {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
  </head>
  <body class="bg-black text-white antialiased">
    @inertia
  </body>
</html>
