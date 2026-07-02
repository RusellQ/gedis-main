export function formatearValorSimple(valor) {
  return valor === null || valor === undefined || valor === '' ? 'Sin información' : valor
}

export function formatearValorDocumental(serie) {
  if (!serie) return 'Sin información'
  const valores = []
  if (serie.vigencia_ad) valores.push('Administrativo')
  if (serie.vigencia_le) valores.push('Legal')
  if (serie.vigencia_confi) valores.push('Contable')
  return valores.length ? valores.join(', ') : 'Sin información'
}

export function formatearVigenciaDocumental(serie) {
  if (!serie) return 'Sin información'
  return formatearValorSimple(serie.plazo_ta != null ? `${serie.plazo_ta} años` : null)
}

export function clasificacionDesdeSerie(serie) {
  if (!serie) return 'Sin información'
  if (serie.clasificacion_c) return 'Confidencial'
  if (serie.clasificacion_r) return 'Reservada'
  return 'Pública'
}

export function caracterDesdeSerie(serie) {
  if (!serie) return 'Sin información'
  if (serie.vigencia_ad) return 'Administrativo'
  if (serie.vigencia_le) return 'Legal'
  if (serie.vigencia_confi) return 'Contable'
  return 'Sin información'
}

export function valorDocumentalDesdeReglas(reglas) {
  if (!reglas?.valor_documental) return 'Sin información'
  const vd = reglas.valor_documental
  const valores = []
  if (vd.administrativo) valores.push('Administrativo')
  if (vd.legal) valores.push('Legal')
  if (vd.contable) valores.push('Contable')
  return valores.length ? valores.join(', ') : 'Sin información'
}

export function reglasDesdeSerieYInstrumento(serie, instrumento = null) {
  if (!serie) return null

  return {
    valor_documental: {
      administrativo: !!serie.vigencia_ad,
      legal: !!serie.vigencia_le,
      contable: !!serie.vigencia_confi,
    },
    vigencia_documental: serie.plazo_ta != null ? String(serie.plazo_ta) : '',
    anios_archivo_tramite: serie.plazo_at ?? '',
    anios_archivo_concentracion: serie.plazo_ac ?? '',
    clasificacion: clasificacionDesdeSerie(serie),
    caracter: caracterDesdeSerie(serie),
    fundamento_legal: instrumento?.fundamento_legal ?? '',
    periodo_reserva: instrumento?.periodo_reserva != null ? String(instrumento.periodo_reserva) : '',
    serie_nombre: serie.nombre ?? '',
    serie_codigo: serie.codigo ?? '',
    instrumento_nombre: instrumento?.nombre_elemento ?? '',
    instrumento_codigo: instrumento?.codigo_clasificacion ?? '',
  }
}
