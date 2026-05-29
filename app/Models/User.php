<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'role_id', 'grupo_id', 'telefone', 'disponibilidade',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
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

    public function canManageGrupo($grupoId)
    {
        return $this->isPastor() || ($this->isLider() && $this->grupo_id === $grupoId);
    }
};
