import Image from '@tiptap/extension-image'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import ImagemNodeView from '@/Components/ImagemNodeView.vue'

// Imagem do TipTap com largura ajustável (alça de arrastar).
// A largura é persistida como style inline no <img>, então vale na leitura e no PDF.
export const ImagemRedimensionavel = Image.extend({
  addAttributes() {
    return {
      ...this.parent?.(),
      width: {
        default: null,
        parseHTML: (el) => el.style.width || el.getAttribute('width') || null,
        renderHTML: (attrs) => (attrs.width ? { style: `width: ${attrs.width}` } : {}),
      },
      align: {
        default: null,
        parseHTML: (el) => {
          if (el.style.float === 'left' || el.style.float === 'right') return el.style.float
          if (el.style.marginLeft === 'auto' && el.style.marginRight === 'auto') return 'center'
          return null
        },
        renderHTML: (attrs) => {
          if (attrs.align === 'left') return { style: 'float: left; margin: 0 1rem 0.5rem 0;' }
          if (attrs.align === 'right') return { style: 'float: right; margin: 0 0 0.5rem 1rem;' }
          if (attrs.align === 'center') return { style: 'display: block; margin: 0.75rem auto;' }
          return {}
        },
      },
    }
  },

  addNodeView() {
    return VueNodeViewRenderer(ImagemNodeView)
  },
})
