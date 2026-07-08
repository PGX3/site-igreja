<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Curso extends Model
{
    protected $table = 'cursos';

    protected $fillable = [
        'titulo', 'slug', 'descricao', 'capa_path', 'share_token', 'ativo', 'ordem', 'created_by',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Curso $curso) {
            if (empty($curso->slug) || $curso->isDirty('titulo')) {
                $curso->slug = static::slugUnico($curso->titulo, $curso->id);
            }
        });
    }

    public static function slugUnico(string $titulo, ?int $ignorarId = null): string
    {
        $base = Str::slug($titulo) ?: 'curso';
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    public function gerarShareToken(): string
    {
        if (! $this->share_token) {
            $this->update(['share_token' => Str::random(40)]);
        }

        return $this->share_token;
    }

    public function revogarLink(): void
    {
        $this->update(['share_token' => null]);
    }

    public function modulos()
    {
        return $this->hasMany(CursoModulo::class)->orderBy('ordem');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    public function capaUrl(): ?string
    {
        return $this->capa_path ? Storage::url($this->capa_path) : null;
    }

    /**
     * Estrutura do curso para as telas de leitura (link público e área de membro).
     * $aulaUrl recebe a Aula e devolve a URL de abertura (varia por contexto).
     * $apenasAtivos = false inclui aulas ocultas (usado na pré-visualização do gestor).
     */
    public function paraLeitura(callable $aulaUrl, bool $apenasAtivos = true): array
    {
        $this->loadMissing(['modulos.aulas' => fn ($q) => $apenasAtivos ? $q->where('ativo', true) : $q]);

        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'capa_url' => $this->capaUrl(),
            'modulos' => $this->modulos->map(fn ($m) => [
                'id' => $m->id,
                'titulo' => $m->titulo,
                'descricao' => $m->descricao,
                'aulas' => $m->aulas->map(fn ($a) => [
                    'id' => $a->id,
                    'titulo' => $a->titulo,
                    'tipo' => $a->tipo,
                    'resumo' => $a->resumo(),
                    'tem_video' => (bool) $a->youtube_url,
                    'url' => $aulaUrl($a),
                    'oculta' => ! $a->ativo,
                ])->values(),
            ])->values(),
        ];
    }
}
