<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Corte (Short do YouTube) de uma pregação. Aparece dentro da página da
 * pregação, nunca como pregação nova. O importador liga cada corte à
 * pregação certa; se o pastor trocar à mão, fica "fixado" e o importador
 * não mexe mais.
 */
class PregacaoCorte extends Model
{
    protected $table = 'pregacao_cortes';

    protected $fillable = ['pregacao_id', 'youtube_id', 'titulo', 'publicado_em', 'fixado', 'ativo'];

    protected $casts = [
        'publicado_em' => 'datetime',
        'fixado' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function pregacao(): BelongsTo
    {
        return $this->belongsTo(Pregacao::class);
    }

    public function scopeVisiveis(Builder $query): Builder
    {
        return $query->where('ativo', true)->orderBy('publicado_em')->orderBy('id');
    }

    public function url(): string
    {
        return "https://www.youtube.com/shorts/{$this->youtube_id}";
    }

    /** Capa vertical do Short (oar2 = 1080×1920). A hqdefault é a reserva (horizontal, com laterais borradas). */
    public function thumb(): string
    {
        return "https://i.ytimg.com/vi/{$this->youtube_id}/oar2.jpg";
    }

    /** Dados do corte para o site, o painel e a API. */
    public function paraExibir(): array
    {
        return [
            'id' => $this->id,
            'youtube_id' => $this->youtube_id,
            'titulo' => $this->titulo,
            'url' => $this->url(),
            'thumb' => $this->thumb(),
            'thumb_reserva' => "https://i.ytimg.com/vi/{$this->youtube_id}/hqdefault.jpg",
            'publicado_em' => $this->publicado_em?->format('Y-m-d'),
        ];
    }

    /** Tira as hashtags do título do Short ("Foi só coincidência? #Shorts" → "Foi só coincidência?"). */
    public static function limparTitulo(string $titulo): string
    {
        $limpo = trim(preg_replace('/(\s*#[\p{L}\p{N}_]+)+\s*$/u', '', $titulo));

        return Str::limit($limpo !== '' ? $limpo : trim($titulo), 200, '');
    }

    /**
     * Pregação a que o corte pertence: se o título traz o tema ("Pergunta | Queremos um rei"),
     * a pregação com esse título; senão, a última pregação publicada até o dia do corte.
     */
    public static function pregacaoSugerida(string $titulo, CarbonInterface $publicadoEm): ?int
    {
        $candidatas = Pregacao::publicadas()
            ->whereDate('data_pregacao', '<=', $publicadoEm->toDateString())
            ->orderByDesc('data_pregacao')
            ->orderByDesc('id');

        if (str_contains($titulo, '|')) {
            $tema = self::normalizar(Str::afterLast($titulo, '|'));
            if (mb_strlen($tema) >= 4) {
                $porTema = (clone $candidatas)->limit(40)->get(['id', 'titulo'])
                    ->first(fn (Pregacao $p) => str_contains(self::normalizar($p->titulo), $tema));
                if ($porTema) {
                    return $porTema->id;
                }
            }
        }

        return $candidatas->value('id');
    }

    /** Religa os cortes automáticos (não fixados): roda depois de cada importação. */
    public static function revincular(): int
    {
        $mudou = 0;
        self::query()->where('fixado', false)->get()->each(function (self $corte) use (&$mudou) {
            $id = self::pregacaoSugerida($corte->titulo, $corte->publicado_em);
            if ($id !== $corte->pregacao_id) {
                $corte->update(['pregacao_id' => $id]);
                $mudou++;
            }
        });

        return $mudou;
    }

    private static function normalizar(string $texto): string
    {
        return trim(preg_replace('/\s+/', ' ', Str::lower(Str::ascii($texto))));
    }
}
