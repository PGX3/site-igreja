<?php

namespace App\Http\Controllers;

use App\Models\Culto;
use App\Models\Evento;
use App\Models\Pregacao;
use App\Models\Texto;
use App\Services\CultoProximaDataResolver;
use Carbon\Carbon;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $cultos = Culto::orderBy('id')->get();

        $eventos = Evento::where('ativo', true)
            ->whereDate('data_evento', '>=', Carbon::today())
            ->orderBy('data_evento')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'nome' => $e->nome,
                'data_evento' => $e->data_evento->format('Y-m-d'),
                'horario' => $e->horario,
                'local' => $e->local,
                'descricao' => $e->descricao,
                'dia' => $e->data_evento->day,
                'mes_curto' => mb_strtoupper(self::mesesCurtos()[$e->data_evento->month]),
            ]);

        $pregacoes = Pregacao::where('ativo', true)
            ->orderByDesc('data_pregacao')
            ->limit(3)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'titulo' => $p->titulo,
                'pregador' => $p->pregador,
                'youtube_url' => $p->youtube_url,
                'data_pregacao' => $p->data_pregacao->format('Y-m-d'),
            ]);

        $textos = Texto::pluck('conteudo', 'chave');

        return Inertia::render('Home', [
            'cultos' => $cultos,
            'eventos' => $eventos,
            'pregacoes' => $pregacoes,
            'textos' => $textos,
            'meta' => [
                'description' => 'Igreja em Charqueadas, RS: comunidade de fé enraizada na cidade. Veja nossos cultos semanais, eventos e venha nos visitar.',
                'og_title' => 'Igreja em Charqueadas',
                'og_description' => 'Comunidade de fé enraizada em Charqueadas, no Rio Grande do Sul. Cultos semanais, eventos e uma casa aberta pra quem busca.',
            ],
        ]);
    }

    public function pregacoes()
    {
        $pregacoes = Pregacao::where('ativo', true)
            ->orderByDesc('data_pregacao')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'titulo' => $p->titulo,
                'pregador' => $p->pregador,
                'versiculo' => $p->versiculo,
                'youtube_url' => $p->youtube_url,
                'data_pregacao' => $p->data_pregacao->format('Y-m-d'),
            ]);

        return Inertia::render('Pregacoes', [
            'pregacoes' => $pregacoes,
            'meta' => [
                'title' => 'Pregações · Igreja em Charqueadas',
                'description' => 'Ouça e assista às pregações da Igreja em Charqueadas, RS. Mensagens sobre o evangelho de Jesus Cristo.',
                'og_title' => 'Pregações · Igreja em Charqueadas',
                'og_description' => 'Mensagens e estudos bíblicos da Igreja em Charqueadas, RS.',
            ],
        ]);
    }

    public function showPregacao(Pregacao $pregacao)
    {
        $meses = self::meses();
        $diasNomes = self::diasNomes();

        $data = $pregacao->data_pregacao;
        $nomeMes = $meses[$data->month];
        $nomeDia = $diasNomes[$data->dayOfWeek];

        return Inertia::render('PregacaoDetalhe', [
            'pregacao' => [
                'id' => $pregacao->id,
                'titulo' => $pregacao->titulo,
                'pregador' => $pregacao->pregador,
                'versiculo' => $pregacao->versiculo,
                'youtube_url' => $pregacao->youtube_url,
                'descricao' => $pregacao->descricao,
                'dia' => $data->day,
                'mes' => mb_strtoupper($nomeMes),
                'ano' => $data->year,
                'data_fmt' => ucfirst($nomeDia).', '.$data->day.' de '.$nomeMes.' de '.$data->year,
            ],
            'meta' => [
                'title' => $pregacao->titulo.' · Igreja em Charqueadas',
                'description' => trim("{$pregacao->titulo}".($pregacao->pregador ? ", por {$pregacao->pregador}" : '').'. Pregação da Igreja em Charqueadas, RS.'),
                'og_title' => $pregacao->titulo.' · Igreja em Charqueadas',
                'og_description' => trim(($pregacao->pregador ? "Por {$pregacao->pregador}. " : '').($pregacao->versiculo ?: 'Pregação da Igreja em Charqueadas.')),
            ],
        ]);
    }

    public function showCulto(Culto $culto, CultoProximaDataResolver $resolver)
    {
        $meses = self::meses();
        $diasNomes = self::diasNomes();

        $proxData = $resolver->resolve($culto);
        $nomeMes = $meses[$proxData->month];
        $nomeDia = $diasNomes[$proxData->dayOfWeek];

        $diaMinuscula = mb_strtolower($culto->dia_semana);
        $descricao = $culto->descricao ?: 'Você é bem-vindo.';

        return Inertia::render('CultoDetalhe', [
            'culto' => [
                'id' => $culto->id,
                'nome' => $culto->nome,
                'dia_semana' => $culto->dia_semana,
                'horario' => $culto->horario,
                'descricao' => $culto->descricao,
                'ativo' => $culto->ativo,
                'dia' => $proxData->day,
                'mes' => mb_strtoupper($nomeMes),
                'ano' => $proxData->year,
                'data_fmt' => ucfirst($nomeDia).', '.$proxData->day.' de '.$nomeMes.' de '.$proxData->year,
            ],
            'meta' => [
                'title' => $culto->nome.' · Igreja em Charqueadas',
                'description' => "{$culto->nome}, toda {$diaMinuscula} às {$culto->horario} na Igreja em Charqueadas. {$descricao}",
                'og_title' => $culto->nome.' · Igreja em Charqueadas',
                'og_description' => "Toda {$diaMinuscula} às {$culto->horario}. {$descricao}",
            ],
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'Event',
                'name' => $culto->nome,
                'description' => $descricao,
                'startDate' => $proxData->toDateString(),
                'eventStatus' => 'https://schema.org/EventScheduled',
                'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                'location' => self::localSchema(),
                'organizer' => self::organizadorSchema(),
            ],
        ]);
    }

    public function showEvento(Evento $evento)
    {
        $meses = self::meses();
        $diasNomes = self::diasNomes();

        $dataEvento = $evento->data_evento;
        $nomeMes = $meses[$dataEvento->month];
        $nomeDia = $diasNomes[$dataEvento->dayOfWeek];

        $dataFmt = ucfirst($nomeDia).', '.$dataEvento->day.' de '.$nomeMes.' de '.$dataEvento->year;
        $descricao = $evento->descricao ?: 'Venha participar.';
        $quando = $dataFmt.($evento->horario ? ' às '.$evento->horario : '').($evento->local ? ' em '.$evento->local : '');

        return Inertia::render('EventoDetalhe', [
            'evento' => [
                'id' => $evento->id,
                'nome' => $evento->nome,
                'horario' => $evento->horario,
                'local' => $evento->local,
                'descricao' => $evento->descricao,
                'ativo' => $evento->ativo,
                'dia' => $dataEvento->day,
                'mes' => mb_strtoupper($nomeMes),
                'ano' => $dataEvento->year,
                'data_fmt' => $dataFmt,
                'passado' => $dataEvento->lt(Carbon::today()),
            ],
            'meta' => [
                'title' => $evento->nome.' · Igreja em Charqueadas',
                'description' => "{$evento->nome}, {$quando}. {$descricao}",
                'og_title' => $evento->nome.' · Igreja em Charqueadas',
                'og_description' => "{$quando}. {$descricao}",
            ],
            'jsonLd' => [
                '@context' => 'https://schema.org',
                '@type' => 'Event',
                'name' => $evento->nome,
                'description' => $descricao,
                'startDate' => $dataEvento->toDateString(),
                'eventStatus' => 'https://schema.org/EventScheduled',
                'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                'location' => self::localSchema($evento->local),
                'organizer' => self::organizadorSchema(),
            ],
        ]);
    }

    private static function enderecoSchema(): array
    {
        return [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Rua Rui Barbosa, 1433',
            'addressLocality' => 'Charqueadas',
            'addressRegion' => 'RS',
            'addressCountry' => 'BR',
        ];
    }

    private static function localSchema(?string $nome = null): array
    {
        if ($nome) {
            return [
                '@type' => 'Place',
                'name' => $nome,
                'address' => 'Charqueadas, RS',
            ];
        }

        return [
            '@type' => 'Place',
            'name' => 'Igreja em Charqueadas',
            'address' => self::enderecoSchema(),
        ];
    }

    private static function organizadorSchema(): array
    {
        return [
            '@type' => 'Organization',
            'name' => 'Igreja em Charqueadas',
            'url' => url('/'),
        ];
    }

    private static function meses(): array
    {
        return [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março',    4 => 'abril',
            5 => 'maio',    6 => 'junho',     7 => 'julho',    8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ];
    }

    private static function mesesCurtos(): array
    {
        return [
            1 => 'jan', 2 => 'fev', 3 => 'mar', 4 => 'abr',
            5 => 'mai', 6 => 'jun', 7 => 'jul', 8 => 'ago',
            9 => 'set', 10 => 'out', 11 => 'nov', 12 => 'dez',
        ];
    }

    private static function diasNomes(): array
    {
        return [
            0 => 'domingo',       1 => 'segunda-feira', 2 => 'terça-feira',
            3 => 'quarta-feira',  4 => 'quinta-feira',  5 => 'sexta-feira',
            6 => 'sábado',
        ];
    }
}
