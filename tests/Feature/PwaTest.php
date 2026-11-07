<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaTest extends TestCase
{
    public function test_manifest_tem_o_necessario_para_instalar(): void
    {
        $manifest = json_decode(file_get_contents(public_path('site.webmanifest')), true);

        $this->assertSame('/admin', $manifest['start_url']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertSame('standalone', $manifest['display']);

        $maskable = collect($manifest['icons'])->firstWhere('purpose', 'maskable');
        $this->assertNotNull($maskable, 'Falta ícone maskable no manifest');
        $this->assertFileExists(public_path(ltrim($maskable['src'], '/')));

        foreach ($manifest['icons'] as $icone) {
            $this->assertFileExists(public_path(ltrim($icone['src'], '/')));
        }
    }

    public function test_pagina_offline_existe(): void
    {
        $this->assertFileExists(public_path('offline.html'));
    }

    public function test_service_worker_e_servido_da_raiz_como_javascript(): void
    {
        $arquivo = public_path('build/sw.js');
        $criado = false;

        if (! file_exists($arquivo)) {
            @mkdir(dirname($arquivo), 0755, true);
            file_put_contents($arquivo, '// sw de teste');
            $criado = true;
        }

        try {
            $this->get('/sw.js')
                ->assertOk()
                ->assertHeader('Content-Type', 'application/javascript; charset=utf-8');
        } finally {
            if ($criado) {
                unlink($arquivo);
            }
        }
    }
}
