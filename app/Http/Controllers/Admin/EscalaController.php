<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Culto;
use App\Models\Escala;
use App\Models\EscalaMembro;
use App\Models\Evento;
use App\Models\Grupo;
use App\Models\Musica;
use App\Models\User;
use App\Notifications\EscalaConvite;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

class EscalaController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Escala::with(['grupo', 'createdBy', 'culto', 'evento', 'escalaMembros.user'])
            ->withCount('escalaMembros')
            ->withCount(['escalaMembros as confirmados_count' => fn ($q) => $q->where('status', 'confirmado')])
            ->latest('data');

        if ($user->isLider()) {
            $query->whereIn('grupo_id', $user->grupoIds());
        }

        $escalas = $query->get()->map(fn ($e) => [
            'id' => $e->id,
            'titulo' => $e->titulo,
            'data' => $e->data?->format('Y-m-d'),
            'hora_inicio' => $e->hora_inicio,
            'hora_fim' => $e->hora_fim,
            'status' => $e->status,
            'grupo' => $e->grupo?->only('id', 'nome'),
            'created_by' => $e->createdBy?->only('id', 'name'),
            'total_membros' => $e->escala_membros_count,
            'confirmados' => $e->confirmados_count,
            'vinculo' => $this->vinculoLabel($e),
            'membros' => $e->escalaMembros->map(fn ($em) => [
                'nome' => $em->user?->name,
                'funcao' => $em->funcao,
                'status' => $em->status,
            ])->values(),
        ]);

        return Inertia::render('Admin/Escalas/Index', compact('escalas'));
    }

    public function create()
    {
        $user = auth()->user();
        $grupos = $user->isPastor()
            ? Grupo::with(['membros' => fn ($q) => $q->select('users.id', 'users.name'), 'funcoes:id,grupo_id,nome'])->get(['id', 'nome'])
            : Grupo::with(['membros' => fn ($q) => $q->select('users.id', 'users.name'), 'funcoes:id,grupo_id,nome'])->whereIn('id', $user->grupoIds())->get(['id', 'nome']);

        return Inertia::render('Admin/Escalas/Form', [
            'grupos' => $grupos,
            'cultos' => $this->cultosOpcoes(),
            'eventos' => $this->eventosOpcoes(),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'titulo' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'data' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fim' => 'required',
            'grupo_id' => 'required|exists:grupos,id',
            'culto_id' => 'nullable|exists:cultos,id',
            'evento_id' => 'nullable|exists:eventos,id',
            'membros' => 'nullable|array',
            'membros.*.user_id' => 'exists:users,id',
            'membros.*.funcao' => 'nullable|string|max:100',
        ], [], [
            'titulo' => 'título',
            'data' => 'data',
            'hora_inicio' => 'horário de início',
            'hora_fim' => 'horário de fim',
            'grupo_id' => 'grupo',
            'culto_id' => 'culto',
            'evento_id' => 'evento',
        ]);

        if (! empty($data['culto_id']) && ! empty($data['evento_id'])) {
            return back()->withErrors(['culto_id' => 'Selecione apenas um vínculo: culto OU evento.'])->withInput();
        }

        if ($user->isLider() && ! in_array((int) $data['grupo_id'], $user->grupoIds())) {
            abort(403);
        }

        $escala = Escala::create(array_merge($data, [
            'created_by' => $user->id,
            'status' => 'pendente',
        ]));

        $membrosRequest = $request->membros ?? [];

        foreach ($membrosRequest as $membro) {
            EscalaMembro::create([
                'escala_id' => $escala->id,
                'user_id' => $membro['user_id'],
                'funcao' => $membro['funcao'] ?? null,
            ]);
        }

        // Enviar notificações
        $escala->load('grupo');
        foreach ($membrosRequest as $membro) {
            $notificado = User::find($membro['user_id']);
            if ($notificado) {
                try {
                    $notificado->notify(new EscalaConvite($escala, $membro['funcao'] ?? null));
                } catch (\Throwable $e) {
                    Log::warning("Notificação falhou [user {$notificado->id}]: {$e->getMessage()}");
                }
            }
        }

        return redirect()->route('admin.escalas.show', $escala)
            ->with('success', 'Escala criada!');
    }

    public function duplicar(Escala $escala)
    {
        $user = auth()->user();
        if ($user->isLider() && ! in_array($escala->grupo_id, $user->grupoIds())) {
            abort(403);
        }

        $escala->load(['escalaMembros', 'setlist', 'notas', 'assets']);

        $nova = Escala::create([
            'titulo' => $escala->titulo.' (cópia)',
            'descricao' => $escala->descricao,
            'data' => $escala->data?->copy()->addWeek(),
            'hora_inicio' => $escala->hora_inicio,
            'hora_fim' => $escala->hora_fim,
            'grupo_id' => $escala->grupo_id,
            'culto_id' => $escala->culto_id,
            'evento_id' => $escala->evento_id,
            'status' => 'pendente',
            'created_by' => $user->id,
        ]);

        foreach ($escala->escalaMembros as $m) {
            $nova->escalaMembros()->create([
                'user_id' => $m->user_id,
                'funcao' => $m->funcao,
            ]);
        }

        foreach ($escala->setlist as $s) {
            $nova->setlist()->create([
                'musica_id' => $s->musica_id,
                'tom' => $s->tom,
                'ordem' => $s->ordem,
                'observacao' => $s->observacao,
            ]);
        }

        foreach ($escala->notas as $n) {
            $nova->notas()->create([
                'titulo' => $n->titulo,
                'corpo' => $n->corpo,
                'created_by' => $user->id,
            ]);
        }

        $nova->assets()->attach($escala->assets->pluck('id'));

        return redirect()->route('admin.escalas.edit', $nova)
            ->with('success', 'Escala duplicada! Ajuste a data e confirme.');
    }

    public function enviarWhatsapp(Escala $escala, \App\Services\CallMeBot $bot)
    {
        $user = auth()->user();
        if ($user->isLider() && ! in_array($escala->grupo_id, $user->grupoIds())) {
            abort(403);
        }

        $escala->load(['grupo', 'escalaMembros.user', 'setlist.musica']);
        $grupo = $escala->grupo;

        if (! $grupo?->whatsapp_apikey) {
            return back()->with('error', 'Configure a API Key do WhatsApp nas configurações do grupo.');
        }

        $ok = $bot->enviar($grupo->whatsapp_phone, $grupo->whatsapp_apikey, $this->mensagemWhatsapp($escala));

        return $ok
            ? back()->with('success', 'Escala enviada no WhatsApp do grupo!')
            : back()->with('error', 'Não foi possível enviar. Verifique a API Key/número do grupo.');
    }

    private function mensagemWhatsapp(Escala $escala): string
    {
        $data = $escala->data?->translatedFormat('d/m/Y (l)') ?? '';
        $inicio = substr((string) $escala->hora_inicio, 0, 5);
        $fim = substr((string) $escala->hora_fim, 0, 5);

        $l = [];
        $l[] = "*{$escala->titulo}*";
        $l[] = "📅 {$data}";
        $l[] = "⏰ {$inicio} - {$fim}";
        if ($escala->grupo) {
            $l[] = "👥 {$escala->grupo->nome}";
        }

        if ($escala->escalaMembros->count()) {
            $l[] = '';
            $l[] = '*Equipe:*';
            foreach ($escala->escalaMembros as $m) {
                $nome = $m->user?->name ?? '—';
                $l[] = $m->funcao ? "• {$nome}: {$m->funcao}" : "• {$nome}";
            }
        }

        $setlist = $escala->setlist->filter(fn ($s) => $s->musica);
        if ($setlist->count()) {
            $l[] = '';
            $l[] = '*Setlist:*';
            $i = 1;
            foreach ($setlist as $s) {
                $tom = $s->tom ?: $s->musica->tom;
                $l[] = "{$i}. {$s->musica->nome}".($tom ? " ({$tom})" : '');
                $i++;
            }
        }

        return implode("\n", $l);
    }

    public function enviarConvites(Escala $escala, \App\Services\CallMeBot $bot)
    {
        $user = auth()->user();
        if ($user->isLider() && ! in_array($escala->grupo_id, $user->grupoIds())) {
            abort(403);
        }

        $escala->load(['grupo', 'escalaMembros.user']);

        $enviados = 0;
        foreach ($escala->escalaMembros as $em) {
            $u = $em->user;
            if (! $u?->callmebot_apikey) {
                continue;
            }
            $ok = $bot->enviar($u->telefone, $u->callmebot_apikey, $this->conviteMensagem($escala, $em));
            if ($ok) {
                $enviados++;
            }
        }

        return $enviados
            ? back()->with('success', "Convite enviado automaticamente para {$enviados} pessoa(s).")
            : back()->with('error', 'Ninguém tem API Key do CallMeBot configurada. Use o botão do WhatsApp em cada pessoa.');
    }

    private function conviteUrl(EscalaMembro $em): string
    {
        return URL::signedRoute('convite.show', ['escalaMembro' => $em->id]);
    }

    private function conviteMensagem(Escala $escala, EscalaMembro $em): string
    {
        $data = $escala->data?->translatedFormat('d/m/Y (l)') ?? '';
        $inicio = substr((string) $escala->hora_inicio, 0, 5);
        $fim = substr((string) $escala->hora_fim, 0, 5);
        $nome = $em->user?->name ?? '';

        $l = [];
        $l[] = "Olá, {$nome}! Você foi escalado(a):";
        $l[] = "*{$escala->titulo}*";
        $l[] = "📅 {$data}";
        $l[] = "⏰ {$inicio} - {$fim}";
        if ($escala->grupo) {
            $l[] = "👥 {$escala->grupo->nome}";
        }
        if ($em->funcao) {
            $l[] = "🎯 Função: {$em->funcao}";
        }
        $l[] = '';
        $l[] = 'Confirme sua presença:';
        $l[] = $this->conviteUrl($em);

        return implode("\n", $l);
    }

    public function show(Escala $escala)
    {
        $user = auth()->user();
        if ($user->isLider() && ! in_array($escala->grupo_id, $user->grupoIds())) {
            abort(403);
        }

        $escala->load([
            'grupo',
            'createdBy',
            'culto',
            'evento',
            'escalaMembros.user',
            'assets.createdBy',
            'notas.createdBy',
            'setlist.musica',
        ]);

        $temMusicas = (bool) $escala->grupo?->tem_musicas;

        return Inertia::render('Admin/Escalas/Show', [
            'can_manage' => $user->isPastor() || in_array($escala->grupo_id, $user->grupoIds()),
            'musicas' => $temMusicas
                ? Musica::orderBy('nome')->get(['id', 'nome', 'tom'])
                : [],
            'biblioteca_assets' => Asset::latest()->get()->map(fn ($a) => [
                'id' => $a->id,
                'tipo' => $a->tipo,
                'titulo' => $a->titulo,
                'arquivo_path' => $a->arquivo_path,
                'arquivo_nome' => $a->arquivo_nome,
            ]),
            'escala' => [
                'id' => $escala->id,
                'titulo' => $escala->titulo,
                'descricao' => $escala->descricao,
                'data' => $escala->data?->format('Y-m-d'),
                'hora_inicio' => $escala->hora_inicio,
                'hora_fim' => $escala->hora_fim,
                'status' => $escala->status,
                'grupo' => $escala->grupo ? [
                    'id' => $escala->grupo->id,
                    'nome' => $escala->grupo->nome,
                    'tem_musicas' => (bool) $escala->grupo->tem_musicas,
                    'tem_whatsapp' => (bool) $escala->grupo->whatsapp_apikey,
                ] : null,
                'created_by' => $escala->createdBy?->only('id', 'name'),
                'assets' => $escala->assets->map(fn ($a) => [
                    'id' => $a->id,
                    'tipo' => $a->tipo,
                    'titulo' => $a->titulo,
                    'arquivo_path' => $a->arquivo_path,
                    'arquivo_nome' => $a->arquivo_nome,
                    'created_by' => $a->createdBy?->only('id', 'name'),
                ]),
                'notas' => $escala->notas->map(fn ($n) => [
                    'id' => $n->id,
                    'titulo' => $n->titulo,
                    'corpo' => $n->corpo,
                    'created_by' => $n->createdBy?->only('id', 'name'),
                    'created_at' => $n->created_at->format('d/m/Y'),
                ]),
                'setlist' => $escala->setlist->map(fn ($s) => [
                    'id' => $s->id,
                    'ordem' => $s->ordem,
                    'tom' => $s->tom,
                    'observacao' => $s->observacao,
                    'musica' => $s->musica ? [
                        'id' => $s->musica->id,
                        'nome' => $s->musica->nome,
                        'tom' => $s->musica->tom,
                        'letra' => $s->musica->letra,
                        'link' => $s->musica->link,
                    ] : null,
                ]),
                'culto' => $escala->culto ? [
                    'id' => $escala->culto->id,
                    'nome' => $escala->culto->nome,
                    'dia_semana' => $escala->culto->dia_semana,
                    'horario' => $escala->culto->horario,
                ] : null,
                'evento' => $escala->evento ? [
                    'id' => $escala->evento->id,
                    'nome' => $escala->evento->nome,
                    'data_evento' => $escala->evento->data_evento?->format('Y-m-d'),
                    'horario' => $escala->evento->horario,
                    'local' => $escala->evento->local,
                ] : null,
                'membros' => $escala->escalaMembros->map(fn ($em) => [
                    'id' => $em->id,
                    'user' => $em->user?->only('id', 'name', 'email'),
                    'funcao' => $em->funcao,
                    'status' => $em->status,
                    'observacao' => $em->observacao,
                    'confirmado_em' => $em->confirmado_em?->format('d/m/Y H:i'),
                    'telefone' => $em->user?->telefone,
                    'tem_apikey' => (bool) $em->user?->callmebot_apikey,
                    'convite_url' => $this->conviteUrl($em),
                ]),
            ],
        ]);
    }

    public function edit(Escala $escala)
    {
        $user = auth()->user();
        if ($user->isLider() && ! in_array($escala->grupo_id, $user->grupoIds())) {
            abort(403);
        }

        $grupos = $user->isPastor()
            ? Grupo::with(['membros' => fn ($q) => $q->select('users.id', 'users.name'), 'funcoes:id,grupo_id,nome'])->get(['id', 'nome'])
            : Grupo::with(['membros' => fn ($q) => $q->select('users.id', 'users.name'), 'funcoes:id,grupo_id,nome'])->whereIn('id', $user->grupoIds())->get(['id', 'nome']);

        $escala->load('escalaMembros');

        return Inertia::render('Admin/Escalas/Form', [
            'grupos' => $grupos,
            'cultos' => $this->cultosOpcoes(),
            'eventos' => $this->eventosOpcoes(),
            'escala' => [
                'id' => $escala->id,
                'titulo' => $escala->titulo,
                'descricao' => $escala->descricao,
                'data' => $escala->data?->format('Y-m-d'),
                'hora_inicio' => $escala->hora_inicio ? substr($escala->hora_inicio, 0, 5) : '',
                'hora_fim' => $escala->hora_fim ? substr($escala->hora_fim, 0, 5) : '',
                'status' => $escala->status,
                'grupo_id' => $escala->grupo_id,
                'culto_id' => $escala->culto_id,
                'evento_id' => $escala->evento_id,
                'membros' => $escala->escalaMembros->map(fn ($em) => [
                    'user_id' => $em->user_id,
                    'funcao' => $em->funcao,
                ]),
            ],
        ]);
    }

    public function update(Request $request, Escala $escala)
    {
        $user = auth()->user();
        if ($user->isLider() && $escala->grupo_id !== $user->grupo_id) {
            abort(403);
        }

        $data = $request->validate([
            'titulo' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'data' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fim' => 'required',
            'grupo_id' => 'required|exists:grupos,id',
            'culto_id' => 'nullable|exists:cultos,id',
            'evento_id' => 'nullable|exists:eventos,id',
            'status' => 'required|in:pendente,confirmada,em_andamento,concluida,cancelada',
            'membros' => 'nullable|array',
            'membros.*.user_id' => 'exists:users,id',
            'membros.*.funcao' => 'nullable|string|max:100',
        ], [], [
            'titulo' => 'título',
            'data' => 'data',
            'hora_inicio' => 'horário de início',
            'hora_fim' => 'horário de fim',
            'grupo_id' => 'grupo',
            'culto_id' => 'culto',
            'evento_id' => 'evento',
            'status' => 'status',
        ]);

        if (! empty($data['culto_id']) && ! empty($data['evento_id'])) {
            return back()->withErrors(['culto_id' => 'Selecione apenas um vínculo: culto OU evento.'])->withInput();
        }

        if ($user->isLider() && ! in_array((int) $data['grupo_id'], $user->grupoIds())) {
            abort(403);
        }

        $escala->update(array_merge($data, ['updated_by' => $user->id]));

        // IDs existentes antes do sync (para detectar novos)
        $existingIds = $escala->escalaMembros()->pluck('user_id')->map(fn ($id) => (int) $id)->toArray();

        // Sync membros preservando status dos já confirmados
        $newIds = collect($request->membros ?? [])->pluck('user_id')->map(fn ($id) => (int) $id)->toArray();
        $escala->escalaMembros()->whereNotIn('user_id', $newIds)->delete();

        foreach ($request->membros ?? [] as $membro) {
            EscalaMembro::updateOrCreate(
                ['escala_id' => $escala->id, 'user_id' => $membro['user_id']],
                ['funcao' => $membro['funcao'] ?? null]
            );
        }

        // Notificar apenas os membros recem-adicionados
        $addedIds = array_diff($newIds, $existingIds);
        if (count($addedIds)) {
            $escala->load('grupo');
            $membrosMap = collect($request->membros ?? [])->keyBy('user_id');
            foreach ($addedIds as $userId) {
                $notificado = User::find($userId);
                if ($notificado) {
                    try {
                        $funcao = $membrosMap[$userId]['funcao'] ?? null;
                        $notificado->notify(new EscalaConvite($escala, $funcao));
                    } catch (\Throwable $e) {
                        Log::warning("Notificação falhou [user {$userId}]: {$e->getMessage()}");
                    }
                }
            }
        }

        return redirect()->route('admin.escalas.show', $escala)
            ->with('success', 'Escala atualizada!');
    }

    public function destroy(Escala $escala)
    {
        $user = auth()->user();
        if ($user->isLider() && ! in_array($escala->grupo_id, $user->grupoIds())) {
            abort(403);
        }

        $escala->delete();

        return redirect()->route('admin.escalas.index')
            ->with('success', 'Escala excluída!');
    }

    private function cultosOpcoes()
    {
        return Culto::where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome', 'dia_semana', 'horario'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'nome' => $c->nome,
                'label' => $c->nome.' · '.$c->dia_semana.' '.$c->horario,
            ]);
    }

    private function eventosOpcoes()
    {
        return Evento::where('ativo', true)
            ->whereDate('data_evento', '>=', Carbon::today())
            ->orderBy('data_evento')
            ->get(['id', 'nome', 'data_evento', 'horario'])
            ->map(fn ($e) => [
                'id' => $e->id,
                'nome' => $e->nome,
                'label' => $e->nome.' · '.$e->data_evento->format('d/m/Y')
                    .($e->horario ? ' '.$e->horario : ''),
            ]);
    }

    private function vinculoLabel(Escala $e): ?array
    {
        if ($e->culto) {
            return ['tipo' => 'culto', 'nome' => $e->culto->nome];
        }
        if ($e->evento) {
            return ['tipo' => 'evento', 'nome' => $e->evento->nome];
        }

        return null;
    }
}
