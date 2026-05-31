<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Escala;
use App\Models\EscalaMembro;
use App\Models\Grupo;
use App\Models\User;
use App\Notifications\EscalaConvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class EscalaController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $query = Escala::with(['grupo', 'createdBy'])
            ->withCount('escalaMembros')
            ->withCount(['escalaMembros as confirmados_count' => fn($q) => $q->where('status', 'confirmado')])
            ->latest('data');

        if ($user->isLider()) {
            $query->where('grupo_id', $user->grupo_id);
        }

        $escalas = $query->get()->map(fn($e) => [
            'id'               => $e->id,
            'titulo'           => $e->titulo,
            'data'             => $e->data?->format('Y-m-d'),
            'hora_inicio'      => $e->hora_inicio,
            'hora_fim'         => $e->hora_fim,
            'status'           => $e->status,
            'grupo'            => $e->grupo?->only('id', 'nome'),
            'created_by'       => $e->createdBy?->only('id', 'name'),
            'total_membros'    => $e->escala_membros_count,
            'confirmados'      => $e->confirmados_count,
        ]);

        return Inertia::render('Admin/Escalas/Index', compact('escalas'));
    }

    public function create()
    {
        $user   = auth()->user();
        $grupos = $user->isPastor()
            ? Grupo::with(['membros:id,name,grupo_id'])->get(['id', 'nome'])
            : Grupo::with(['membros:id,name,grupo_id'])->where('id', $user->grupo_id)->get(['id', 'nome']);

        return Inertia::render('Admin/Escalas/Form', compact('grupos'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'titulo'      => 'required|string|max:150',
            'descricao'   => 'nullable|string',
            'data'        => 'required|date',
            'hora_inicio' => 'required',
            'hora_fim'    => 'required',
            'grupo_id'    => 'required|exists:grupos,id',
            'membros'     => 'nullable|array',
            'membros.*.user_id' => 'exists:users,id',
            'membros.*.funcao'  => 'nullable|string|max:100',
        ], [], [
            'titulo'      => 'título',
            'data'        => 'data',
            'hora_inicio' => 'horário de início',
            'hora_fim'    => 'horário de fim',
            'grupo_id'    => 'grupo',
        ]);

        // Líder só pode criar para seu grupo
        if ($user->isLider() && (int) $data['grupo_id'] !== (int) $user->grupo_id) {
            abort(403);
        }

        $escala = Escala::create(array_merge($data, [
            'created_by' => $user->id,
            'status'     => 'pendente',
        ]));

        $membrosRequest = $request->membros ?? [];

        foreach ($membrosRequest as $membro) {
            EscalaMembro::create([
                'escala_id' => $escala->id,
                'user_id'   => $membro['user_id'],
                'funcao'    => $membro['funcao'] ?? null,
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

    public function show(Escala $escala)
    {
        $user = auth()->user();
        if ($user->isLider() && $escala->grupo_id !== $user->grupo_id) {
            abort(403);
        }

        $escala->load([
            'grupo',
            'createdBy',
            'escalaMembros.user',
        ]);

        return Inertia::render('Admin/Escalas/Show', [
            'escala' => [
                'id'          => $escala->id,
                'titulo'      => $escala->titulo,
                'descricao'   => $escala->descricao,
                'data'        => $escala->data?->format('Y-m-d'),
                'hora_inicio' => $escala->hora_inicio,
                'hora_fim'    => $escala->hora_fim,
                'status'      => $escala->status,
                'grupo'       => $escala->grupo?->only('id', 'nome'),
                'created_by'  => $escala->createdBy?->only('id', 'name'),
                'membros'     => $escala->escalaMembros->map(fn($em) => [
                    'id'           => $em->id,
                    'user'         => $em->user?->only('id', 'name', 'email'),
                    'funcao'       => $em->funcao,
                    'status'       => $em->status,
                    'observacao'   => $em->observacao,
                    'confirmado_em' => $em->confirmado_em?->format('d/m/Y H:i'),
                ]),
            ],
        ]);
    }

    public function edit(Escala $escala)
    {
        $user = auth()->user();
        if ($user->isLider() && $escala->grupo_id !== $user->grupo_id) {
            abort(403);
        }

        $grupos = $user->isPastor()
            ? Grupo::with(['membros:id,name,grupo_id'])->get(['id', 'nome'])
            : Grupo::with(['membros:id,name,grupo_id'])->where('id', $user->grupo_id)->get(['id', 'nome']);

        $escala->load('escalaMembros');

        return Inertia::render('Admin/Escalas/Form', [
            'grupos' => $grupos,
            'escala' => [
                'id'          => $escala->id,
                'titulo'      => $escala->titulo,
                'descricao'   => $escala->descricao,
                'data'        => $escala->data?->format('Y-m-d'),
                'hora_inicio' => $escala->hora_inicio ? substr($escala->hora_inicio, 0, 5) : '',
                'hora_fim'    => $escala->hora_fim ? substr($escala->hora_fim, 0, 5) : '',
                'status'      => $escala->status,
                'grupo_id'    => $escala->grupo_id,
                'membros'     => $escala->escalaMembros->map(fn($em) => [
                    'user_id' => $em->user_id,
                    'funcao'  => $em->funcao,
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
            'titulo'      => 'required|string|max:150',
            'descricao'   => 'nullable|string',
            'data'        => 'required|date',
            'hora_inicio' => 'required',
            'hora_fim'    => 'required',
            'grupo_id'    => 'required|exists:grupos,id',
            'status'      => 'required|in:pendente,confirmada,em_andamento,concluida,cancelada',
            'membros'     => 'nullable|array',
            'membros.*.user_id' => 'exists:users,id',
            'membros.*.funcao'  => 'nullable|string|max:100',
        ], [], [
            'titulo'      => 'título',
            'data'        => 'data',
            'hora_inicio' => 'horário de início',
            'hora_fim'    => 'horário de fim',
            'grupo_id'    => 'grupo',
            'status'      => 'status',
        ]);

        if ($user->isLider() && (int) $data['grupo_id'] !== (int) $user->grupo_id) {
            abort(403);
        }

        $escala->update(array_merge($data, ['updated_by' => $user->id]));

        // IDs existentes antes do sync (para detectar novos)
        $existingIds = $escala->escalaMembros()->pluck('user_id')->map(fn($id) => (int) $id)->toArray();

        // Sync membros preservando status dos já confirmados
        $newIds = collect($request->membros ?? [])->pluck('user_id')->map(fn($id) => (int) $id)->toArray();
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
        if ($user->isLider() && $escala->grupo_id !== $user->grupo_id) {
            abort(403);
        }

        $escala->delete();
        return redirect()->route('admin.escalas.index')
            ->with('success', 'Escala excluída!');
    }
}
