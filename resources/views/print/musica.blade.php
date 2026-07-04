<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $musica->nome }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111; margin: 32px; line-height: 1.5; }
        h1 { font-size: 24px; margin: 0 0 4px; }
        .tom { color: #7c3aed; font-weight: bold; margin: 0 0 20px; }
        .letra p { margin: 0; }
        .letra p:empty::before { content: "\00a0"; }
        .letra strong { font-weight: 800; }
        .letra { font-size: 16px; }
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
    <h1>{{ $musica->nome }}</h1>
    @if($musica->tom)
        <p class="tom">Tom: {{ $musica->tom }}</p>
    @endif
    <div class="letra">{!! $musica->letra !!}</div>
</body>
</html>
