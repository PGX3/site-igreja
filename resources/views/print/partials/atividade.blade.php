@php
    $meta = [];
    if ($aula->tipo === 'atividade') {
        if ($aula->data_entrega) {
            $meta[] = 'Entregar até '.$aula->data_entrega->format('d/m/Y');
        }
        if ($aula->pontos) {
            $meta[] = $aula->pontos.' pontos';
        }
    }
@endphp
@if($meta)
    <p class="atividade-meta">{{ implode(' · ', $meta) }}</p>
@endif
