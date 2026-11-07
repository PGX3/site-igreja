import { createHash } from 'node:crypto'
import { readFileSync } from 'node:fs'
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

// A página offline fica em public/ (fora do build), então entra no precache
// com um hash do conteúdo para o service worker renovar quando ela mudar.
const offlineRevision = createHash('md5')
  .update(readFileSync('public/offline.html'))
  .digest('hex')

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    // Gera public/build/sw.js a partir de resources/js/sw.js com a lista de
    // assets do build injetada. O manifest (site.webmanifest) e o registro
    // do SW são feitos à mão: o Blade não é um index.html que o plugin injete.
    VitePWA({
      strategies: 'injectManifest',
      srcDir: 'resources/js',
      filename: 'sw.js',
      manifest: false,
      injectRegister: null,
      injectManifest: {
        globPatterns: ['**/*.{js,css,woff,woff2}'],
        globIgnores: ['**/sw.js'],
        // O laravel-vite-plugin publica em /build, mas o plugin lista os
        // arquivos relativos à pasta. O SW mora em /sw.js, então as URLs
        // precisam ser absolutas.
        modifyURLPrefix: { '': '/build/' },
        additionalManifestEntries: [{ url: '/offline.html', revision: offlineRevision }],
        maximumFileSizeToCacheInBytes: 4 * 1024 * 1024,
      },
    }),
  ],
  build: {
    cssMinify: 'esbuild',
  },
})
