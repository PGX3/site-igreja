@php
    $tipos = ['aula' => 'Aula', 'material' => 'Material', 'atividade' => 'Atividade'];
    $tipoLabel = $tipos[$aula->tipo] ?? 'Aula';
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $aula->titulo }}</title>
    @include('print.partials.estilo')
</head>
<body>
    @include('print.partials.controles')

    <p class="trilha">{{ $aula->modulo->curso->titulo }} · {{ $aula->modulo->titulo }}</p>
    <p class="tipo">{{ $tipoLabel }}</p>
    <h1>{{ $aula->titulo }}</h1>
    @include('print.partials.atividade', ['aula' => $aula])

    @if($aula->youtube_url)
        <p class="video">Vídeo da aula: <a href="{{ $aula->youtube_url }}">{{ $aula->youtube_url }}</a></p>
    @endif

    <div class="conteudo">{!! $aula->conteudo ?: '<p><em>Sem conteúdo escrito.</em></p>' !!}</div>

    @include('print.partials.anexos', ['aula' => $aula])
</body>
</html>
