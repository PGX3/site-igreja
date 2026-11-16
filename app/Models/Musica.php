<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Musica extends Model
{
    public const CATEGORIAS = [
        'rapida' => 'Rápida',
        'meio_termo' => 'Meio termo',
        'lenta' => 'Lenta',
    ];

    /**
     * Partes de uma música. As chaves em `SPRESENTER` são os tipos de seção que o
     * SPresenter aceita ao criar uma música pela API dele.
     */
    public const TIPOS_SECAO = [
        'verso' => 'Verso',
        'pre_refrao' => 'Pré-refrão',
        'refrao' => 'Refrão',
        'ponte' => 'Ponte',
        'intro' => 'Intro',
        'instrumental' => 'Instrumental',
        'tag' => 'Tag',
        'final' => 'Final',
    ];

    public const SPRESENTER = [
        'verso' => 'verse',
        'pre_refrao' => 'pre-chorus',
        'refrao' => 'chorus',
        'ponte' => 'bridge',
        'intro' => 'intro',
        'instrumental' => 'instrumental',
        'tag' => 'tag',
        'final' => 'ending',
    ];

    /** Cores das tags da letra para quem não tem o CSS do site (app mobile). */
    private const CORES_TAGS = ['refrao' => '#16a34a', 'pre-refrao' => '#0d9488', 'ponte' => '#9333ea', 'instrucao' => '#dc2626'];

    private const NOTAS = ['C' => 'Dó', 'D' => 'Ré', 'E' => 'Mi', 'F' => 'Fá', 'G' => 'Sol', 'A' => 'Lá', 'B' => 'Si'];

    protected $table = 'musicas';

    /**
     * `letra` é a versão dos músicos e a fonte de tudo: HTML com tags de parte
     * (`<p class="refrao">`) e de instrução (`<span class="instrucao">2X</span>`).
     * A versão crua (só o texto) é calculada dela, em `letraCrua()`.
     * `secoes_publico` é a versão do telão: seções na ordem cantada, já com as repetições.
     */
    protected $fillable = ['nome', 'artista', 'tom', 'bpm', 'categoria', 'letra', 'secoes_publico', 'link', 'created_by'];

    protected $casts = ['bpm' => 'integer', 'secoes_publico' => 'array'];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function setlistItens()
    {
        return $this->hasMany(EscalaSetlistItem::class);
    }

    /** "Hosana" e "HOSANA " são a mesma música: compara sem acento, caixa e espaços sobrando. */
    public static function chave(?string $texto): string
    {
        return Str::of(Str::ascii((string) $texto))->lower()->squish()->toString();
    }

    /** Cifra vira o nome usado no painel: "C" => "Dó", "G#m" => "Sol#m". O resto fica como veio. */
    public static function normalizarTom(?string $tom): ?string
    {
        $tom = trim((string) $tom);

        if ($tom === '') {
            return null;
        }

        if (preg_match('/^([A-Ga-g])(#?)(m?)$/', $tom, $p)) {
            return self::NOTAS[strtoupper($p[1])].$p[2].$p[3];
        }

        return $tom;
    }

    /** Só o texto da letra dos músicos: sem formatação e sem as instruções (REFRÃO:, 2X, SOLO). */
    public function letraCrua(): string
    {
        if (trim((string) $this->letra) === '') {
            return '';
        }

        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8"><div id="letra">'.$this->letra.'</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($dom);

        foreach (iterator_to_array($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " instrucao ")]')) as $no) {
            $no->parentNode->removeChild($no);
        }
        foreach (iterator_to_array($xpath->query('//br')) as $br) {
            $br->parentNode->replaceChild($dom->createTextNode("\n"), $br);
        }

        $linhas = [];
        foreach ($xpath->query('//div[@id="letra"]/*') as $bloco) {
            foreach (explode("\n", $bloco->textContent) as $linha) {
                $linhas[] = Str::squish($linha);
            }
        }

        return self::limparTexto(implode("\n", $linhas)) ?? '';
    }

    /** Versão do telão em texto: uma seção por bloco, linha em branco entre elas (formato do Holyrics). */
    public function letraPublicoTexto(): string
    {
        return collect($this->secoes_publico)->pluck('texto')->implode("\n\n");
    }

    /** Letra dos músicos com as tags viradas em cor no próprio HTML, para o app mobile. */
    public function letraComCores(): string
    {
        return preg_replace_callback(
            '/<(p|span) class="([^"]*)"/',
            function ($m) {
                $cor = collect(explode(' ', $m[2]))->map(fn ($c) => self::CORES_TAGS[$c] ?? null)->filter()->first();

                return $cor ? "{$m[0]} style=\"color:{$cor}\"" : $m[0];
            },
            (string) $this->letra,
        );
    }

    /**
     * Normaliza as seções do telão: tipo conhecido (ou verso), texto puro, sem seção vazia.
     *
     * @return array<int, array{tipo: string, texto: string}>|null
     */
    public static function limparSecoes(?array $secoes): ?array
    {
        $limpas = collect($secoes)
            ->map(fn ($s) => [
                'tipo' => isset(self::TIPOS_SECAO[$s['tipo'] ?? '']) ? $s['tipo'] : 'verso',
                'texto' => self::limparTexto($s['texto'] ?? null),
            ])
            ->filter(fn ($s) => $s['texto'] !== null)
            ->map(fn ($s) => ['tipo' => $s['tipo'], 'texto' => preg_replace("/\n{2,}/", "\n", $s['texto'])])
            ->values()
            ->all();

        return $limpas ?: null;
    }

    /** Texto puro: sem HTML, quebras de linha padronizadas, no máximo uma linha em branco seguida. */
    private static function limparTexto(?string $texto): ?string
    {
        $texto = html_entity_decode(strip_tags((string) $texto), ENT_QUOTES | ENT_HTML5);
        $texto = preg_replace("/\r\n?/", "\n", $texto);
        $texto = preg_replace("/[ \t]+\n/", "\n", $texto);
        $texto = trim(preg_replace("/\n{3,}/", "\n\n", $texto));

        return $texto === '' ? null : $texto;
    }
}
