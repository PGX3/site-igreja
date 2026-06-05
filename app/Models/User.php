<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'role_id', 'telefone', 'disponibilidade', 'callmebot_apikey', 'is_superadmin',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

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
        return $this->grupos()->pluck('grupos.id')->map(fn($id) => (int) $id)->toArray();
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

    public function isPastor()
    {
        return $this->role->name === 'pastor';
    }

    public function isLider()
    {
        return $this->role->name === 'lider';
    }

    public function isMembro()
    {
        return $this->role->name === 'membro';
    }

    public function canManageGrupo(int $grupoId): bool
    {
        return $this->isPastor() || ($this->isLider() && in_array($grupoId, $this->grupoIds()));
    }
};
