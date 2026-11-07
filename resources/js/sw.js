/// <reference lib="webworker" />
// Service worker da PWA. É construído pelo vite-plugin-pwa (injectManifest)
// e servido em /sw.js pela rota em routes/web.php, para ter escopo na raiz.
//
// Estratégia:
// - assets do build (js/css/fontes): precache, atualizados a cada deploy
// - navegação (HTML): sempre rede. Sem rede, mostra /offline.html
// - respostas Inertia (XHR com X-Inertia): nunca cacheadas
// - imagens e fontes externas: cache com validade

import { clientsClaim } from 'workbox-core'
import { ExpirationPlugin } from 'workbox-expiration'
import { cleanupOutdatedCaches, matchPrecache, precacheAndRoute } from 'workbox-precaching'
import { registerRoute, setCatchHandler } from 'workbox-routing'
import { CacheFirst, NetworkOnly, StaleWhileRevalidate } from 'workbox-strategies'

precacheAndRoute(self.__WB_MANIFEST)
cleanupOutdatedCaches()
clientsClaim()

// O banner "nova versão disponível" manda esta mensagem ao clicar em Atualizar.
self.addEventListener('message', (event) => {
  if (event.data?.type === 'SKIP_WAITING') self.skipWaiting()
})

// Navegação: rede sempre. O catch handler abaixo entrega a página offline.
registerRoute(({ request }) => request.mode === 'navigate', new NetworkOnly())

// Imagens do próprio site (fotos de membros, imagens do editor, ícones).
registerRoute(
  ({ request, url }) => request.destination === 'image' && url.origin === self.location.origin,
  new CacheFirst({
    cacheName: 'imagens',
    plugins: [new ExpirationPlugin({ maxEntries: 200, maxAgeSeconds: 30 * 24 * 60 * 60, purgeOnQuotaError: true })],
  }),
)

// Google Fonts: o CSS muda pouco, as fontes em si são imutáveis.
registerRoute(
  ({ url }) => url.origin === 'https://fonts.googleapis.com',
  new StaleWhileRevalidate({ cacheName: 'google-fonts-css' }),
)
registerRoute(
  ({ url }) => url.origin === 'https://fonts.gstatic.com',
  new CacheFirst({
    cacheName: 'google-fonts',
    plugins: [new ExpirationPlugin({ maxEntries: 30, maxAgeSeconds: 365 * 24 * 60 * 60 })],
  }),
)

setCatchHandler(async ({ request }) => {
  if (request.mode === 'navigate') {
    const offline = await matchPrecache('/offline.html')
    if (offline) return offline
  }

  return Response.error()
})
