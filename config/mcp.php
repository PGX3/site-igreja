<?php

return [
    /*
    |---------------------------------------------------------------------------
    | Servidor MCP (Model Context Protocol)
    |---------------------------------------------------------------------------
    |
    | Expõe ferramentas de LEITURA sobre dados que já são públicos no site
    | (cultos, eventos, pregações e informações de contato) para agentes de IA
    | como Claude e ChatGPT. Desligado por padrão.
    |
    | Nenhuma ferramenta desta fase toca em dados de membros, escalas ou
    | pedidos de oração.
    |
    */

    'enabled' => env('MCP_ENABLED', false),

    // Token estático enviado pelo agente como `Authorization: Bearer <token>`.
    'token' => env('MCP_TOKEN'),

    'servidor' => [
        'nome' => env('MCP_SERVER_NAME', 'igreja-em-charqueadas'),
        'versao' => '1.0.0',
    ],

    // Revisões da spec MCP aceitas, mais recente primeiro.
    'protocolos' => ['2025-06-18', '2025-03-26'],

    // Paginação das ferramentas de listagem.
    'limite_padrao' => 10,
    'limite_maximo' => 50,
];
