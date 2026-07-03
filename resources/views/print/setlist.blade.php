<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setlist - {{ $escala->titulo }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111; margin: 32px; line-height: 1.5; }
        h1 { font-size: 24px; margin: 0 0 2px; }
        .sub { color: #666; margin: 0 0 24px; font-size: 14px; }
        .musica { margin: 0 0 28px; page-break-inside: avoid; }
        .musica h2 { font-size: 18px; margin: 0 0 2px; }
        .musica .meta { color: #7c3aed; font-weight: bold; font-size: 13px; margin: 0 0 8px; }
        .musica .obs { color: #666; font-weight: normal; }
        .letra p { margin: 0; }
        .letra p:empty::before { content: "\00a0"; }
        .letra strong { font-weight: 800; }
        .num { color: #999; }
        @media print {
            body { margin: 12mm; }
            .noprint { display: none; }
        }
        .noprint { margin-bottom: 20px; }
        .btn { font: inherit; padding: 8px 16px; border: 1px solid #ccc; border-radius: 8px; background: #f5f5f5; cursor: pointer; }
    </style>
</head>
<body onload="window.print()">
    <div class="noprint">
        <button class="btn" onclick="window.print()">Imprimir / Salvar PDF</button>
    </div>

    <h1>{{ $escala->titulo }}</h1>
    <p class="sub">
        {{ $escala->data?->format('d/m/Y') }}
        @if($escala->grupo) · {{ $escala->grupo->nome }} @endif
        · {{ $escala->setlist->count() }} música(s)
    </p>

    @forelse($escala->setlist as $i => $s)
        @if($s->musica)
            <div class="musica">
                <h2><span class="num">{{ $i + 1 }}.</span> {{ $s->musica->nome }}</h2>
                <p class="meta">
                    @if($s->tom ?: $s->musica->tom) Tom: {{ $s->tom ?: $s->musica->tom }} @endif
                    @if($s->observacao) <span class="obs">· {{ $s->observacao }}</span> @endif
                </p>
                <div class="letra">{!! $s->musica->letra !!}</div>
            </div>
        @endif
    @empty
        <p>Nenhuma música no setlist.</p>
    @endforelse
</body>
</html>
