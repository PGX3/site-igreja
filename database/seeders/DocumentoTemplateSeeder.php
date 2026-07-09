<?php

namespace Database\Seeders;

use App\Models\DocumentoTemplate;
use Illuminate\Database\Seeder;

class DocumentoTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $variaveis = [
            ['chave' => 'numero_oficio', 'label' => 'Número do ofício', 'tipo' => 'texto'],
            ['chave' => 'ano', 'label' => 'Ano', 'tipo' => 'texto'],
            ['chave' => 'cidade', 'label' => 'Cidade', 'tipo' => 'texto'],
            ['chave' => 'data', 'label' => 'Data', 'tipo' => 'data'],
            ['chave' => 'instituicao', 'label' => 'Instituição (seminário)', 'tipo' => 'texto'],
            ['chave' => 'destinatario', 'label' => 'Destinatário (A/C)', 'tipo' => 'texto'],
            ['chave' => 'periodo', 'label' => 'Período das férias', 'tipo' => 'texto'],
            ['chave' => 'responsavel', 'label' => 'Responsável (assinatura)', 'tipo' => 'texto'],
            ['chave' => 'cargo', 'label' => 'Cargo do responsável', 'tipo' => 'texto'],
        ];

        $corpo = <<<'HTML'
<p style="text-align: center"><strong>OFÍCIO Nº {{numero_oficio}}/{{ano}}</strong></p>
<p></p>
<p style="text-align: right">{{cidade}}, {{data}}.</p>
<p></p>
<p>À <strong>{{instituicao}}</strong></p>
<p>A/C {{destinatario}}</p>
<p></p>
<p><strong>Assunto: Convite para período de férias e vivência ministerial</strong></p>
<p></p>
<p>Prezados irmãos,</p>
<p></p>
<p>A Igreja em Charqueadas vem, por meio deste, convidar cordialmente os seminaristas dessa instituição para passarem o período de férias de {{periodo}} conosco, em comunhão e serviço ao Reino.</p>
<p></p>
<p>Durante a estadia, oferecemos hospedagem, alimentação e acompanhamento pastoral, e desejamos proporcionar aos seminaristas a oportunidade de vivenciar de perto a rotina da igreja local, participando dos cultos, das visitas, dos grupos e das demais atividades da nossa comunidade.</p>
<p></p>
<p>Acreditamos que esse tempo será de mútua edificação, somando à formação teológica dos seminaristas a experiência prática do cuidado com o rebanho.</p>
<p></p>
<p>Colocamo-nos à disposição para alinhar datas, número de participantes e demais detalhes pelos nossos canais de contato.</p>
<p></p>
<p>Certos de contarmos com a atenção de Vossas Senhorias, subscrevemo-nos.</p>
<p></p>
<p>Em Cristo,</p>
<p></p>
<p></p>
<p><strong>{{responsavel}}</strong></p>
<p>{{cargo}}</p>
<p>Igreja em Charqueadas</p>
HTML;

        DocumentoTemplate::updateOrCreate(
            ['nome' => 'Carta-convite seminário (férias)'],
            [
                'descricao' => 'Ofício convidando seminaristas para passar as férias na igreja.',
                'corpo' => $corpo,
                'variaveis' => $variaveis,
                'ativo' => true,
            ],
        );
    }
}
