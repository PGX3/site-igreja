<?php

namespace App\Services\Mcp;

use RuntimeException;

/**
 * Falha esperada na execução de uma ferramenta (ex.: pregação inexistente).
 *
 * A mensagem volta para o agente dentro do resultado com `isError: true`,
 * então deve ser segura para exibição e útil para o modelo se corrigir.
 */
class McpFerramentaErro extends RuntimeException {}
