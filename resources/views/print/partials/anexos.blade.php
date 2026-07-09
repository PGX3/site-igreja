@if($aula->anexos->count())
    <div class="anexos">
        <p class="anexos-titulo">Materiais</p>
        <ul>
            @foreach($aula->anexos as $anexo)
                <li>
                    {{ $anexo->rotulo() }}
                    @if($anexo->link())
                        <span class="anexo-url">{{ $anexo->link() }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
