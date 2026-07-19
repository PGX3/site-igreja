<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizer
{
    private static ?HTMLPurifier $purifier = null;

    /**
     * Sanitiza HTML de rich text (editor Tiptap) mantendo só a formatação
     * que o editor realmente produz e removendo qualquer script/atributo perigoso.
     */
    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html === null ? null : '';
        }

        return self::purifier()->purify($html);
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier !== null) {
            return self::$purifier;
        }

        $config = HTMLPurifier_Config::createDefault();

        // Cache de definição em disco: só usa se o diretório existir e for gravável.
        // Em produção o usuário do webserver pode não conseguir criar/escrever a pasta,
        // o que faria o purifier lançar exceção ao montar a definição customizada.
        // Nesse caso, desliga o cache em disco (definição montada em memória a cada request).
        $cacheDir = storage_path('framework/cache/htmlpurifier');
        if (! is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        if (is_dir($cacheDir) && is_writable($cacheDir)) {
            $config->set('Cache.SerializerPath', $cacheDir);
        } else {
            $config->set('Cache.DefinitionImpl', null);
        }

        // Tags que o RichTextEditor gera (StarterKit + underline, cor, alinhamento, imagem).
        $config->set('HTML.Allowed', implode(',', [
            'p[style]', 'br', 'span[style]', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'code', 'pre', 'blockquote',
            'ul', 'ol', 'li',
            'h1[style]', 'h2[style]', 'h3[style]', 'h4[style]',
            'hr',
            'a[href|title|target|rel]',
            'img[src|alt|style]',
        ]));

        // Propriedades CSS inline usadas pelo editor (cor, alinhamento, largura/posição da imagem).
        // 'display' não é preciso aqui: o leitor já aplica display:block nas imagens via CSS.
        $config->set('CSS.AllowedProperties', [
            'color', 'text-align', 'width', 'height', 'float', 'margin',
        ]);

        // Só http/https/mailto: bloqueia javascript:, data: etc.
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);

        // Links externos abrem em nova aba com rel seguro.
        $config->set('HTML.TargetBlank', true);
        $config->set('Attr.AllowedRel', ['noopener', 'noreferrer', 'nofollow']);

        self::$purifier = new HTMLPurifier($config);

        return self::$purifier;
    }
}
