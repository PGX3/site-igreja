@php
    $tipos = ['aula' => 'Aula', 'material' => 'Material', 'atividade' => 'Atividade'];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $curso->titulo }}</title>
    @include('print.partials.estilo')
</head>
<body>
    @include('print.partials.controles')

    <!-- Capa -->
    <div class="capa">
        <p class="kicker" style="color: {{ $curso->cor }}">Curso</p>
        @if($curso->capa_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($curso->capa_path) }}" alt="{{ $curso->titulo }}">
        @endif
        <h1 class="capa-titulo">{{ $curso->titulo }}</h1>
        <p class="cover-sub">Igreja em Charqueadas</p>
    </div>

    <!-- Sobre -->
    @if($curso->descricao)
        <div class="modulo">
            <h2 class="modulo-titulo">Sobre o curso</h2>
            <div class="conteudo">{!! $curso->descricao !!}</div>
        </div>
    @endif

    <!-- Módulos e aulas -->
    @foreach($curso->modulos as $i => $modulo)
        <div class="modulo">
            <h2 class="modulo-titulo">Módulo {{ $i + 1 }} · {{ $modulo->titulo }}</h2>
            @if($modulo->descricao)
                <p class="modulo-desc">{{ $modulo->descricao }}</p>
            @endif

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
                <p><em>Sem aulas neste módulo.</em></p>
            @endforelse
        </div>
    @endforeach
</body>
</html>
