import { Fancybox } from '@fancyapps/ui'
import '@fancyapps/ui/dist/fancybox/fancybox.css'

export function esArchivoPdf(archivo) {
  if (!archivo) return false
  const path = String(archivo).split('?')[0].toLowerCase()
  return path.endsWith('.pdf')
}

export function urlStorage(archivo) {
  if (!archivo) return '#'
  const path = String(archivo).replace(/^\/+/, '')
  return `/storage/${path}`
}

export function captionUnidad(unidad) {
  const partes = []
  if (unidad?.tipo) partes.push(unidad.tipo)
  if (unidad?.nombre) partes.push(unidad.nombre)
  let caption = partes.join(': ')
  if (unidad?.descripcion) {
    caption = caption ? `${caption} — ${unidad.descripcion}` : unidad.descripcion
  }
  return caption || 'Unidad documental'
}

/**
 * Abre Fancybox con los PDFs del expediente y navega al índice de la unidad clicada.
 * @param {Array} unidades
 * @param {object|null} unidadClicada
 * @returns {boolean} true si se abrió el visor
 */
export function abrirVisorPdf(unidades, unidadClicada = null) {
  const lista = Array.isArray(unidades) ? unidades : []
  const pdfs = lista.filter((u) => u?.archivo && esArchivoPdf(u.archivo))

  if (!pdfs.length) {
    return false
  }

  let startIndex = 0
  if (unidadClicada?.id != null) {
    const idx = pdfs.findIndex((u) => u.id === unidadClicada.id)
    if (idx >= 0) {
      startIndex = idx
    }
  }

  const items = pdfs.map((unidad) => ({
    src: urlStorage(unidad.archivo),
    type: 'pdf',
    caption: captionUnidad(unidad),
  }))

  Fancybox.show(items, { startIndex })
  return true
}
