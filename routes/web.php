<?php

use App\Http\Controllers\Admin\AniversarioController;
use App\Http\Controllers\Admin\AprenderController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\AulaAnexoController;
use App\Http\Controllers\Admin\AulaComentarioController;
use App\Http\Controllers\Admin\AulaController;
use App\Http\Controllers\Admin\CalendarioController;
use App\Http\Controllers\Admin\CultoController;
use App\Http\Controllers\Admin\CursoController;
use App\Http\Controllers\Admin\CursoModuloController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EditorImagemController;
use App\Http\Controllers\Admin\EscalaAssetController;
use App\Http\Controllers\Admin\EscalaController;
use App\Http\Controllers\Admin\EscalaMembroController;
use App\Http\Controllers\Admin\EscalaNotaController;
use App\Http\Controllers\Admin\EscalaSetlistController;
use App\Http\Controllers\Admin\EventoController;
use App\Http\Controllers\Admin\FamiliaController;
use App\Http\Controllers\Admin\GrupoController;
use App\Http\Controllers\Admin\GrupoFuncaoController;
use App\Http\Controllers\Admin\GrupoMuralController;
use App\Http\Controllers\Admin\MembroController;
use App\Http\Controllers\Admin\MinhaSenhaController;
use App\Http\Controllers\Admin\MusicaController;
use App\Http\Controllers\Admin\PedidoOracaoController;
use App\Http\Controllers\Admin\PerfilController;
use App\Http\Controllers\Admin\PlanejadorController;
use App\Http\Controllers\Admin\PregacaoController;
use App\Http\Controllers\Admin\SugestaoController;
use App\Http\Controllers\Admin\TextoController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisitanteController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CadastroController;
use App\Http\Controllers\ConfirmacaoEscalaController;
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cultos/{culto}', [HomeController::class, 'showCulto'])->name('cultos.show');
Route::get('/eventos/{evento}', [HomeController::class, 'showEvento'])->name('eventos.show');
Route::get('/pregacoes', [HomeController::class, 'pregacoes'])->name('pregacoes.index');
Route::get('/pregacoes/{pregacao}', [HomeController::class, 'showPregacao'])->name('pregacoes.show');

// Cursos / Escola bíblica via link compartilhado (sem login)
Route::get('/c/{token}', [HomeController::class, 'cursoPublico'])->name('cursos.publico');
Route::get('/c/{token}/aula/{aula}', [HomeController::class, 'aulaNoCurso'])->name('cursos.publico.aula');
Route::get('/a/{token}', [HomeController::class, 'aulaPublica'])->name('aulas.publica');

Route::post('/sugestao', [ContatoController::class, 'sugestao'])->name('sugestao.store');
Route::post('/pedido-oracao', [ContatoController::class, 'pedidoOracao'])->name('pedido-oracao.store');

Route::get('/cadastro', [CadastroController::class, 'create'])->name('cadastro.create');
Route::post('/cadastro', [CadastroController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('cadastro.store');
Route::get('/cadastro/obrigado', [CadastroController::class, 'obrigado'])->name('cadastro.obrigado');

Route::get('/convite/{escalaMembro}', [ConfirmacaoEscalaController::class, 'show'])
    ->name('convite.show')->middleware('signed');
Route::post('/convite/{escalaMembro}/{acao}', [ConfirmacaoEscalaController::class, 'responder'])
    ->name('convite.responder')->middleware('signed')->whereIn('acao', ['confirmar', 'recusar']);

Route::get('/login', fn () => inertia('Auth/Login'))->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Aniversários (pastor + líder)
    Route::middleware('role:pastor,lider')->group(function () {
        Route::get('aniversarios', [AniversarioController::class, 'index'])->name('aniversarios.index');
    });

    // Mural do grupo (pastor + lider + membro — autorização no controller)
    Route::get('grupos/{grupo}/mural', [GrupoMuralController::class, 'show'])->name('grupos.mural.show');
    Route::post('grupos/{grupo}/avisos', [GrupoMuralController::class, 'storeAviso'])->name('grupos.avisos.store');
    Route::delete('grupos/{grupo}/avisos/{aviso}', [GrupoMuralController::class, 'destroyAviso'])->name('grupos.avisos.destroy');

    // Escola bíblica: todos os autenticados podem ler os cursos ativos
    Route::get('aprender', [AprenderController::class, 'index'])->name('aprender.index');
    Route::get('aprender/aulas/{aula}', [AprenderController::class, 'aula'])->name('aprender.aula');
    Route::get('aprender/aulas/{aula}/pdf', [AprenderController::class, 'pdfAula'])->name('aprender.aula.pdf');
    Route::get('aprender/modulos/{modulo}/pdf', [AprenderController::class, 'pdfModulo'])->name('aprender.modulo.pdf');
    Route::get('aprender/{curso}/pdf', [AprenderController::class, 'pdfCurso'])->name('aprender.curso.pdf');
    Route::get('aprender/{curso}', [AprenderController::class, 'curso'])->name('aprender.curso');

    // Comentários nas aulas (qualquer autenticado)
    Route::post('aprender/aulas/{aula}/comentarios', [AulaComentarioController::class, 'store'])->name('aprender.comentarios.store');
    Route::delete('aprender/comentarios/{comentario}', [AulaComentarioController::class, 'destroy'])->name('aprender.comentarios.destroy');

    // Todas as roles: ver suas próprias escalas e confirmar/recusar
    Route::get('minhas-escalas', [EscalaMembroController::class, 'index'])->name('minhas-escalas.index');
    Route::get('minha-senha', [MinhaSenhaController::class, 'edit'])->name('minha-senha.edit');
    Route::put('minha-senha', [MinhaSenhaController::class, 'update'])->name('minha-senha.update');
    Route::get('perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::patch('escala-membros/{escalaMembro}/confirmar', [EscalaMembroController::class, 'confirmar'])->name('escala-membros.confirmar');
    Route::patch('escala-membros/{escalaMembro}/recusar', [EscalaMembroController::class, 'recusar'])->name('escala-membros.recusar');

    // Pastor + Líder: gerenciar escalas (líder restrito ao seu grupo via controller)
    Route::middleware('role:pastor,lider')->group(function () {
        Route::get('calendario', [CalendarioController::class, 'index'])->name('calendario.index');
        Route::get('planejador', [PlanejadorController::class, 'index'])->name('planejador.index');
        Route::post('planejador', [PlanejadorController::class, 'salvar'])->name('planejador.salvar');

        Route::resource('escalas', EscalaController::class);
        Route::post('escalas/{escala}/duplicar', [EscalaController::class, 'duplicar'])->name('escalas.duplicar');
        Route::post('escalas/{escala}/whatsapp', [EscalaController::class, 'enviarWhatsapp'])->name('escalas.whatsapp');
        Route::post('escalas/{escala}/convites', [EscalaController::class, 'enviarConvites'])->name('escalas.convites');

        Route::post('escalas/{escala}/assets', [EscalaAssetController::class, 'store'])->name('escalas.assets.store');
        Route::post('escalas/{escala}/assets/attach', [EscalaAssetController::class, 'attach'])->name('escalas.assets.attach');
        Route::delete('escalas/{escala}/assets/{asset}', [EscalaAssetController::class, 'detach'])->name('escalas.assets.detach');

        Route::post('escalas/{escala}/notas', [EscalaNotaController::class, 'store'])->name('escalas.notas.store');
        Route::delete('escalas/{escala}/notas/{nota}', [EscalaNotaController::class, 'destroy'])->name('escalas.notas.destroy');

        Route::post('escalas/{escala}/setlist', [EscalaSetlistController::class, 'store'])->name('escalas.setlist.store');
        Route::patch('escalas/{escala}/setlist/reorder', [EscalaSetlistController::class, 'reorder'])->name('escalas.setlist.reorder');
        Route::patch('escalas/{escala}/setlist/{item}', [EscalaSetlistController::class, 'update'])->name('escalas.setlist.update');
        Route::delete('escalas/{escala}/setlist/{item}', [EscalaSetlistController::class, 'destroy'])->name('escalas.setlist.destroy');
        Route::get('escalas/{escala}/setlist/imprimir', [EscalaSetlistController::class, 'imprimir'])->name('escalas.setlist.imprimir');

        Route::resource('musicas', MusicaController::class)->except(['show']);
        Route::get('musicas/{musica}/imprimir', [MusicaController::class, 'imprimir'])->name('musicas.imprimir');
        Route::get('musicas/{musica}', [MusicaController::class, 'show'])->name('musicas.show');

        Route::resource('assets', AssetController::class)->except(['show']);
        Route::get('assets/{asset}/download', [AssetController::class, 'download'])->name('assets.download');

        // Escola bíblica / Cursos (gestão)
        Route::get('cursos/{curso}/conteudo', [CursoController::class, 'conteudo'])->name('cursos.conteudo');
        Route::post('cursos/{curso}/compartilhar', [CursoController::class, 'compartilhar'])->name('cursos.compartilhar');
        Route::delete('cursos/{curso}/compartilhar', [CursoController::class, 'revogar'])->name('cursos.revogar');
        Route::resource('cursos', CursoController::class)->except(['show']);

        Route::post('cursos/{curso}/modulos', [CursoModuloController::class, 'store'])->name('cursos.modulos.store');
        Route::patch('cursos/{curso}/modulos/reordenar', [CursoModuloController::class, 'reordenar'])->name('cursos.modulos.reordenar');
        Route::put('modulos/{modulo}', [CursoModuloController::class, 'update'])->name('modulos.update');
        Route::delete('modulos/{modulo}', [CursoModuloController::class, 'destroy'])->name('modulos.destroy');

        Route::post('modulos/{modulo}/aulas', [AulaController::class, 'store'])->name('modulos.aulas.store');
        Route::patch('modulos/{modulo}/aulas/reordenar', [AulaController::class, 'reordenar'])->name('modulos.aulas.reordenar');
        Route::get('aulas/{aula}/edit', [AulaController::class, 'edit'])->name('aulas.edit');
        Route::put('aulas/{aula}', [AulaController::class, 'update'])->name('aulas.update');
        Route::delete('aulas/{aula}', [AulaController::class, 'destroy'])->name('aulas.destroy');
        Route::post('aulas/{aula}/compartilhar', [AulaController::class, 'compartilhar'])->name('aulas.compartilhar');
        Route::delete('aulas/{aula}/compartilhar', [AulaController::class, 'revogar'])->name('aulas.revogar');

        Route::post('aulas/{aula}/anexos', [AulaAnexoController::class, 'store'])->name('aulas.anexos.store');
        Route::delete('anexos/{anexo}', [AulaAnexoController::class, 'destroy'])->name('anexos.destroy');

        Route::post('editor/imagem', [EditorImagemController::class, 'store'])->name('editor.imagem.store');

        Route::resource('membros', MembroController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::post('membros/{membro}/gerar-senha', [MembroController::class, 'gerarSenha'])
            ->name('membros.gerar-senha');
        Route::resource('visitantes', VisitanteController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::post('visitantes/{visitante}/promover', [VisitanteController::class, 'promoverParaMembro'])
            ->name('visitantes.promover');
        Route::resource('familias', FamiliaController::class);

        Route::post('grupos/{grupo}/funcoes', [GrupoFuncaoController::class, 'store'])->name('grupos.funcoes.store');
        Route::delete('grupos/{grupo}/funcoes/{funcao}', [GrupoFuncaoController::class, 'destroy'])->name('grupos.funcoes.destroy');
    });

    // Listagem de grupos: todos os autenticados veem seus grupos
    Route::get('grupos', [GrupoController::class, 'index'])->name('grupos.index');

    // Apenas Pastor: grupos, cultos, textos, sugestões, pedidos, usuários
    Route::middleware('role:pastor')->group(function () {
        Route::resource('grupos', GrupoController::class)->except(['index']);
        Route::resource('usuarios', UserController::class)->except(['show']);
        Route::post('usuarios/{usuario}/senha', [UserController::class, 'alterarSenha'])->name('usuarios.senha');
        Route::resource('cultos', CultoController::class);
        Route::resource('eventos', EventoController::class);
        Route::resource('pregacoes', PregacaoController::class)->parameters(['pregacoes' => 'pregacao']);
        Route::resource('textos', TextoController::class);

        Route::get('sugestoes', [SugestaoController::class, 'index'])->name('sugestoes.index');
        Route::patch('sugestoes/{sugestao}/lida', [SugestaoController::class, 'marcarLida'])->name('sugestoes.lida');
        Route::delete('sugestoes/{sugestao}', [SugestaoController::class, 'destroy'])->name('sugestoes.destroy');

        Route::get('pedidos-oracao', [PedidoOracaoController::class, 'index'])->name('pedidos-oracao.index');
        Route::post('pedidos-oracao', [PedidoOracaoController::class, 'store'])->name('pedidos-oracao.store');
        Route::put('pedidos-oracao/{pedido}', [PedidoOracaoController::class, 'update'])->name('pedidos-oracao.update');
        Route::patch('pedidos-oracao/{pedido}/status', [PedidoOracaoController::class, 'atualizarStatus'])->name('pedidos-oracao.status');
        Route::delete('pedidos-oracao/{pedido}', [PedidoOracaoController::class, 'destroy'])->name('pedidos-oracao.destroy');
    });
});
