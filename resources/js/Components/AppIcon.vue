<template>
  <svg xmlns="http://www.w3.org/2000/svg"
       viewBox="0 0 24 24"
       fill="none"
       stroke="currentColor"
       stroke-width="1.75"
       stroke-linecap="round"
       stroke-linejoin="round"
       :class="cls"
       v-html="paths[name]" />
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  name: { type: String, required: true },
  size: { type: String, default: 'sm' },
})

const cls = computed(() => ({
  xs: 'w-3.5 h-3.5',
  sm: 'w-4 h-4',
  md: 'w-5 h-5',
  lg: 'w-6 h-6',
}[props.size] ?? 'w-4 h-4'))

const paths = {
  dashboard:
    `<rect x="3" y="3" width="7" height="7" rx="1.2"/>
     <rect x="14" y="3" width="7" height="7" rx="1.2"/>
     <rect x="3" y="14" width="7" height="7" rx="1.2"/>
     <rect x="14" y="14" width="7" height="7" rx="1.2"/>`,

  clock:
    `<circle cx="12" cy="12" r="9"/>
     <polyline points="12 7 12 12 15.5 14.5"/>`,

  calendar:
    `<rect x="3" y="4" width="18" height="17" rx="2"/>
     <line x1="16" y1="2" x2="16" y2="6"/>
     <line x1="8" y1="2" x2="8" y2="6"/>
     <line x1="3" y1="10" x2="21" y2="10"/>`,

  users:
    `<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
     <circle cx="9" cy="7" r="4"/>
     <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
     <path d="M16 3.13a4 4 0 0 1 0 7.75"/>`,

  user:
    `<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
     <circle cx="12" cy="7" r="4"/>`,

  mic:
    `<path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
     <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
     <line x1="12" y1="19" x2="12" y2="23"/>
     <line x1="8" y1="23" x2="16" y2="23"/>`,

  'file-text':
    `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
     <polyline points="14 2 14 8 20 8"/>
     <line x1="16" y1="13" x2="8" y2="13"/>
     <line x1="16" y1="17" x2="8" y2="17"/>
     <line x1="10" y1="9" x2="8" y2="9"/>`,

  lightbulb:
    `<path d="M9 18h6M10 22h4M12 2a7 7 0 0 1 7 7c0 2.8-1.7 5.2-4 6.5V17H9v-1.5C6.7 14.2 5 11.8 5 9a7 7 0 0 1 7-7z"/>`,

  heart:
    `<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>`,

  'external-link':
    `<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
     <polyline points="15 3 21 3 21 9"/>
     <line x1="10" y1="14" x2="21" y2="3"/>`,

  bell:
    `<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
     <path d="M13.73 21a2 2 0 0 1-3.46 0"/>`,

  moon:
    `<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>`,

  search:
    `<circle cx="11" cy="11" r="8"/>
     <line x1="21" y1="21" x2="16.65" y2="16.65"/>`,

  logout:
    `<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
     <polyline points="16 17 21 12 16 7"/>
     <line x1="21" y1="12" x2="9" y2="12"/>`,

  'trending-up':
    `<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
     <polyline points="17 6 23 6 23 12"/>`,

  'chevron-right':
    `<polyline points="9 18 15 12 9 6"/>`,

  gift:
    `<polyline points="20 12 20 22 4 22 4 12"/>
     <rect x="2" y="7" width="20" height="5"/>
     <line x1="12" y1="22" x2="12" y2="7"/>
     <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
     <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>`,

  sun:
    `<circle cx="12" cy="12" r="4"/>
     <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>`,

  lock:
    `<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
     <path d="M7 11V7a5 5 0 0 1 10 0v4"/>`,

  'graduation-cap':
    `<path d="M22 10L12 5 2 10l10 5 10-5z"/>
     <path d="M6 12v5c0 1 2.5 2.5 6 2.5s6-1.5 6-2.5v-5"/>
     <line x1="22" y1="10" x2="22" y2="16"/>`,

  'book-open':
    `<path d="M12 7v14"/>
     <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>`,

  music:
    `<path d="M9 18V5l12-2v13"/>
     <circle cx="6" cy="18" r="3"/>
     <circle cx="18" cy="16" r="3"/>`,

  church:
    `<path d="M12 2v6"/>
     <path d="M9 5h6"/>
     <path d="M10 22v-4a2 2 0 0 1 4 0v4"/>
     <path d="M18 22V10.5L12 7 6 10.5V22"/>
     <path d="M6 12l-4 2.2V22h20v-7.8L18 12"/>`,

  clipboard:
    `<rect x="8" y="2" width="8" height="4" rx="1"/>
     <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
     <line x1="9" y1="12" x2="15" y2="12"/>
     <line x1="9" y1="16" x2="15" y2="16"/>`,

  'list-checks':
    `<path d="m3 7 2 2 4-4"/>
     <path d="m3 17 2 2 4-4"/>
     <line x1="13" y1="6" x2="21" y2="6"/>
     <line x1="13" y1="12" x2="21" y2="12"/>
     <line x1="13" y1="18" x2="21" y2="18"/>`,

  paperclip:
    `<path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>`,

  megaphone:
    `<path d="M3 11l18-5v12L3 14v-3z"/>
     <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>`,

  star:
    `<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>`,
}
</script>
