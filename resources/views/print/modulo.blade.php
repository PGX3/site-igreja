@php
    $tipos = ['aula' => 'Aula', 'material' => 'Material', 'atividade' => 'Atividade'];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $modulo->titulo }}</title>
    @include('print.partials.estilo')
</head>
<body>
    @include('print.partials.controles')

    <!-- Capa -->
    <div class="capa">
        <p class="kicker" style="color: {{ $modulo->curso->cor }}">{{ $modulo->curso->titulo }}</p>
        <h1 class="capa-titulo">{{ $modulo->titulo }}</h1>
        @if($modulo->descricao)
            <p class="cover-sub">{{ $modulo->descricao }}</p>
        @endif
        <p class="cover-sub">{{ $modulo->aulas->count() }} {{ $modulo->aulas->count() === 1 ? 'aula' : 'aulas' }}</p>
    </div>

    <!-- Aulas -->
    @forelse($modulo->aulas as $aula)
        <div class="aula">
            <p class="tipo">{{ $tipos[$aula->tipo] ?? 'Aula' }}</p>
            <h1>{{ $aula->titulo }}</h1>
            @include('print.partials.atividade', ['aula' => $aula])
            @if($aula->youtube_url)
                <p class="video">Vídeo da aula: <a href="{{ $aula->youtube_url }}">{{ $aula->youtube_url }}</a></p>
            @endif
            <div class="conteudo">{!! $aula->conteudo ?: '<p><em>Sem conteúdo escrito.</em></p>' !!}</div>
            @include('print.partials.anexos', ['aula' => $aula])
        </div>
    @empty
        <p><em>Este módulo ainda não tem aulas.</em></p>
    @endforelse
</body>
</html>
