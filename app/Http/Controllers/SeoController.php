<?php

namespace App\Http\Controllers;

use App\Models\Culto;
use App\Models\Evento;
use App\Models\Pregacao;
use Carbon\Carbon;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = [];

        // Páginas fixas
        $urls[] = ['loc' => route('home'), 'changefreq' => 'weekly', 'priority' => '1.0'];
        $urls[] = ['loc' => route('pregacoes.index'), 'changefreq' => 'weekly', 'priority' => '0.8'];
        $urls[] = ['loc' => route('cadastro.create'), 'changefreq' => 'yearly', 'priority' => '0.5'];

        foreach (Culto::orderBy('id')->get() as $culto) {
            $urls[] = ['loc' => route('cultos.show', $culto), 'changefreq' => 'monthly', 'priority' => '0.7'];
        }

        $eventos = Evento::where('ativo', true)
            ->whereDate('data_evento', '>=', Carbon::today())
            ->get();
        foreach ($eventos as $evento) {
            $urls[] = ['loc' => route('eventos.show', $evento), 'changefreq' => 'weekly', 'priority' => '0.7'];
        }

        foreach (Pregacao::where('ativo', true)->get() as $pregacao) {
            $urls[] = ['loc' => route('pregacoes.show', $pregacao), 'changefreq' => 'monthly', 'priority' => '0.6'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $url) {
            $xml .= '  <url>'."\n";
            $xml .= '    <loc>'.e($url['loc']).'</loc>'."\n";
            $xml .= '    <changefreq>'.$url['changefreq'].'</changefreq>'."\n";
            $xml .= '    <priority>'.$url['priority'].'</priority>'."\n";
            $xml .= '  </url>'."\n";
        }
        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /convite',
            'Disallow: /cadastro/obrigado',
            '',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ];

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
    }
}
