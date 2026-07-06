<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Confirmar participação · {{ $escala->titulo }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            margin: 0; background: #f1f5f9; color: #0f172a;
            min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .card { background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,.08); max-width: 440px; width: 100%; overflow: hidden; }
        .top { background: linear-gradient(135deg, #1e3a8a, #1d4ed8); color: #fff; padding: 24px; }
        .top .eyebrow { font-size: 11px; letter-spacing: .15em; text-transform: uppercase; opacity: .8; margin: 0 0 6px; }
        .top h1 { font-size: 22px; margin: 0; }
        .body { padding: 24px; }
        .row { display: flex; gap: 10px; font-size: 15px; margin-bottom: 8px; color: #334155; }
        .row b { color: #0f172a; }
        .status { display: inline-block; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 999px; margin-top: 6px; }
        .st-conf { background: #dcfce7; color: #15803d; }
        .st-rec { background: #fee2e2; color: #b91c1c; }
        .st-pend { background: #fef9c3; color: #a16207; }
        .actions { display: flex; gap: 12px; margin-top: 22px; }
        .btn { flex: 1; text-align: center; border: 0; border-radius: 12px; padding: 14px; font-size: 15px; font-weight: 700; cursor: pointer; }
        .btn-yes { background: #16a34a; color: #fff; }
        .btn-no { background: #fff; color: #dc2626; border: 1px solid #fecaca; }
        .msg { text-align: center; padding: 8px 0; }
        .msg .emoji { font-size: 44px; }
        .msg p { font-size: 16px; color: #334155; margin: 8px 0 0; }
        .foot { text-align: center; font-size: 12px; color: #94a3b8; padding: 0 24px 20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="top">
            <p class="eyebrow">Convite de escala</p>
            <h1>{{ $escala->titulo }}</h1>
        </div>
        <div class="body">
            <div class="row"><span>👤</span><span>Olá, <b>{{ $em->user?->name }}</b></span></div>
            <div class="row"><span>📅</span><span>{{ $escala->data?->translatedFormat('l, d/m/Y') }}</span></div>
            <div class="row"><span>⏰</span><span>{{ substr((string) $escala->hora_inicio, 0, 5) }} – {{ substr((string) $escala->hora_fim, 0, 5) }}</span></div>
            @if($escala->grupo)
                <div class="row"><span>👥</span><span>{{ $escala->grupo->nome }}</span></div>
            @endif
            @if($em->funcao)
                <div class="row"><span>🎯</span><span>Função: <b>{{ $em->funcao }}</b></span></div>
            @endif

            @if($feito === 'confirmar')
                <div class="msg"><div class="emoji">✅</div><p>Presença <b>confirmada</b>! Obrigado.</p></div>
            @elseif($feito === 'recusar')
                <div class="msg"><div class="emoji">🙏</div><p>Tudo bem, sua ausência foi registrada.</p></div>
            @else
                @if($em->status === 'confirmado')
                    <span class="status st-conf">Você já confirmou</span>
                @elseif($em->status === 'recusado')
                    <span class="status st-rec">Você recusou</span>
                @endif

                <p style="margin-top:18px;font-size:15px;color:#334155;">Você vai participar?</p>
                <div class="actions">
                    <form method="POST" action="{{ $confirmarUrl }}" style="flex:1;">
                        @csrf
                        <button class="btn btn-yes" type="submit">Confirmar</button>
                    </form>
                    <form method="POST" action="{{ $recusarUrl }}" style="flex:1;">
                        @csrf
                        <button class="btn btn-no" type="submit">Não posso</button>
                    </form>
                </div>
            @endif
        </div>
        <p class="foot">{{ config('app.name') }}</p>
    </div>
</body>
</html>
