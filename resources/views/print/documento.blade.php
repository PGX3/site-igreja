<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $documento->titulo }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Georgia, 'Times New Roman', serif;
            color: #1a1a1a;
            margin: 18mm;
            line-height: 1.7;
            font-size: 16px;
        }

        /* ─── Timbrado: cabeçalho ─── */
        .timbrado {
            display: flex; align-items: center; gap: 16px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 12px; margin-bottom: 28px;
        }
        .timbrado img { max-height: 68px; max-width: 90px; object-fit: contain; }
        .timbrado .dados { font-family: Arial, Helvetica, sans-serif; }
        .timbrado .nome { font-size: 18px; font-weight: 800; color: #1e3a8a; margin: 0; letter-spacing: 0.02em; }
        .timbrado .linha { font-size: 12px; color: #555; margin: 2px 0 0; }

        /* ─── Corpo (conteúdo TipTap) ─── */
        .corpo { font-size: var(--print-fs, 16px); line-height: var(--print-lh, 1.7); }
        .corpo p { margin: 0 0 12px; }
        .corpo p:empty::before { content: "\00a0"; }
        .corpo h1 { font-size: 22px; margin: 20px 0 8px; }
        .corpo h2 { font-size: 19px; margin: 18px 0 8px; }
        .corpo ul { list-style: disc; padding-left: 22px; margin: 0 0 12px; }
        .corpo ol { list-style: decimal; padding-left: 22px; margin: 0 0 12px; }
        .corpo li { margin: 3px 0; }
        .corpo blockquote { border-left: 3px solid #cbd5e1; padding-left: 14px; margin: 0 0 12px; font-style: italic; color: #444; }
        .corpo img { max-width: 100%; height: auto; }
        .corpo a { color: #1e3a8a; }

        /* ─── Timbrado: rodapé ─── */
        .rodape {
            font-family: Arial, Helvetica, sans-serif;
            border-top: 1px solid #cbd5e1;
            margin-top: 40px; padding-top: 10px;
            font-size: 11px; color: #777; text-align: center; line-height: 1.5;
        }

        /* ─── Layout configurável (toolbar de impressão) ─── */
        .corpo { column-gap: 28px; }
        body.cols-2 .corpo { column-count: 2; }
        body.mar-narrow { margin: 10mm; }   /* só na pré-visualização; o PDF usa o @page injetado */

        @media print {
            body { margin: 0; }
            .noprint { display: none !important; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
    @include('print.partials.controles')
</head>
<body>

    <header class="timbrado">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="Logo">
        @endif
        <div class="dados">
            <p class="nome">{{ $igreja->nome }}</p>
            @if($igreja->endereco || $igreja->cidade)
                <p class="linha">{{ collect([$igreja->endereco, $igreja->cidade])->filter()->implode(' · ') }}</p>
            @endif
        </div>
    </header>

    <main class="corpo">
        {!! $documento->corpo ?: '<p><em>Documento sem conteúdo.</em></p>' !!}
    </main>

    @php
        $contato = collect([
            $igreja->telefone,
            $igreja->email,
            $igreja->site,
        ])->filter()->implode(' · ');
    @endphp
    <footer class="rodape">
        @if($igreja->cnpj)<div>CNPJ {{ $igreja->cnpj }}</div>@endif
        @if($contato)<div>{{ $contato }}</div>@endif
    </footer>

</body>
</html>
