<style>
    * { box-sizing: border-box; }
    body {
        font-family: Georgia, 'Times New Roman', serif;
        color: #1a1a1a;
        margin: 24mm 18mm;
        line-height: 1.7;
        font-size: 16px;
    }
    h1, h2, h3, .trilha, .tipo, .btn, .capa-titulo, .cover-sub { font-family: Arial, Helvetica, sans-serif; }

    .trilha { font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; color: #666; margin: 0 0 4px; }
    .tipo {
        display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: 0.06em;
        text-transform: uppercase; color: #2563eb; background: #eff6ff;
        padding: 2px 10px; border-radius: 999px; margin: 0 0 10px;
    }
    h1 { font-size: 28px; margin: 0 0 14px; line-height: 1.25; }
    .video { font-size: 13px; color: #444; margin: 0 0 18px; }
    .video a { color: #2563eb; }

    /* Conteúdo rico das aulas */
    .conteudo { font-size: 16px; }
    .conteudo p { margin: 0 0 12px; }
    .conteudo p:empty::before { content: "\00a0"; }
    .conteudo h1 { font-size: 22px; margin: 20px 0 8px; }
    .conteudo h2 { font-size: 19px; margin: 18px 0 8px; }
    .conteudo ul { list-style: disc; padding-left: 22px; margin: 0 0 12px; }
    .conteudo ol { list-style: decimal; padding-left: 22px; margin: 0 0 12px; }
    .conteudo li { margin: 3px 0; }
    .conteudo blockquote { border-left: 3px solid #cbd5e1; padding-left: 14px; margin: 0 0 12px; font-style: italic; color: #444; }
    .conteudo img { max-width: 100%; height: auto; display: block; margin: 14px auto; border-radius: 6px; }
    .conteudo a { color: #2563eb; }
    .conteudo::after { content: ''; display: block; clear: both; }

    /* Atividade: prazo e pontos */
    .atividade-meta {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 13px; font-weight: 700; color: #b45309;
        background: #fffbeb; border: 1px solid #fde68a;
        display: inline-block; padding: 4px 12px; border-radius: 999px; margin: 0 0 14px;
    }

    /* Materiais / anexos */
    .anexos { margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 12px; }
    .anexos-titulo { font-family: Arial, Helvetica, sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #666; margin: 0 0 6px; }
    .anexos ul { list-style: none; padding: 0; margin: 0; }
    .anexos li { font-size: 13px; margin: 3px 0; }
    .anexos .anexo-url { color: #2563eb; word-break: break-all; }

    /* Capa (livro) */
    .capa { text-align: center; padding: 40mm 0 30mm; page-break-after: always; }
    .capa .kicker { font-size: 13px; letter-spacing: 0.28em; text-transform: uppercase; color: #2563eb; margin: 0 0 16px; }
    .capa-titulo { font-size: 44px; font-weight: 800; margin: 0 0 12px; line-height: 1.15; color: #111; }
    .cover-sub { font-size: 14px; color: #666; }
    .capa img { max-width: 70%; max-height: 90mm; margin: 0 auto 24px; border-radius: 10px; display: block; }

    /* Seções do livro */
    .modulo { page-break-before: always; }
    .modulo-titulo { font-size: 24px; font-weight: 800; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; margin: 0 0 6px; color: #111; }
    .modulo-desc { color: #666; font-size: 14px; margin: 0 0 20px; }
    .aula { page-break-before: always; }
    .aula:first-of-type { page-break-before: avoid; }

    /* ─── Layout configurável (controles de impressão) ─── */
    .conteudo { column-gap: 28px; }
    body.cols-2 .conteudo { column-count: 2; }
    body.fs-sm .conteudo { font-size: 14px; }
    body.fs-lg .conteudo { font-size: 18px; }
    body.mar-narrow { margin: 12mm; }        /* margem só na pré-visualização; o PDF usa o @page */

    @media print {
        body { margin: 0; }                  /* a margem impressa vem do @page injetado */
        .noprint { display: none !important; }
        a { color: inherit; text-decoration: none; }
    }
    .noprint { margin-bottom: 24px; }
    .btn { font: inherit; padding: 8px 16px; border: 1px solid #ccc; border-radius: 8px; background: #f5f5f5; cursor: pointer; }
</style>
