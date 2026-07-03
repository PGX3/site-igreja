<?php

namespace App\Models;

use App\Support\Cpf;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    // role  = nível de acesso administrativo (pastor/lider/membro)
    // tipo  = classificação pastoral (membro/visitante)
    protected $fillable = [
        'name', 'email', 'password',
        'role_id', 'telefone', 'data_nascimento', 'disponibilidade', 'callmebot_apikey', 'is_superadmin',
        'tipo', 'batizado_aguas',
        'sexo', 'estado_civil', 'cpf',
        'como_conheceu', 'convidado_por_id', 'primeira_visita', 'observacoes_pastorais',
        'familia_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'data_nascimento' => 'date',
        'primeira_visita' => 'date',
        'batizado_aguas' => 'boolean',
        'password' => 'hashed',
    ];

    public function setCpfAttribute($value): void
    {
        $this->attributes['cpf'] = Cpf::normalize($value);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class);
    }

    public function grupoIds(): array
    {
        return $this->grupos()->pluck('grupos.id')->map(fn ($id) => (int) $id)->toArray();
    }

    public function escalas()
    {
        return $this->belongsToMany(Escala::class, 'escala_membros')
            ->withPivot('status', 'funcao', 'confirmado_em')
            ->withTimestamps();
    }

    public function escalasLideradas()
    {
        return $this->hasMany(Escala::class, 'created_by');
    }

    public function convidadoPor()
    {
        return $this->belongsTo(self::class, 'convidado_por_id');
    }

    public function convidados()
    {
        return $this->hasMany(self::class, 'convidado_por_id');
    }

    public function familia()
    {
        return $this->belongsTo(Familia::class);
    }

    public function scopeMembros($q)
    {
        return $q->where('tipo', 'membro');
    }

    public function scopeVisitantes($q)
    {
        return $q->where('tipo', 'visitante');
    }

    public function isPastor()
    {
        return $this->role?->name === 'pastor';
    }

    public function isLider()
    {
        return $this->role?->name === 'lider';
    }

    public function isMembro()
    {
        return $this->role?->name === 'membro';
    }

    public function isVisitante(): bool
    {
        return $this->tipo === 'visitante';
    }

    public function isResponsavelFamilia(): bool
    {
        return $this->familia?->responsavel_id === $this->id;
    }

    public function canManageGrupo(int $grupoId): bool
    {
        return $this->isPastor() || ($this->isLider() && in_array($grupoId, $this->grupoIds()));
    }
}
