# 002 · Visor de unidades documentales (PDF)

**Estado:** implementado ✅
**Ticket:** —

## Qué hace

En el modal **Ver Unidades Documentales**, el personal puede **previsualizar en la misma pantalla** los archivos PDF asociados a las unidades de un expediente, con navegación tipo carrusel (anterior/siguiente) entre los PDFs del expediente.

Además, **solo en Archivo de Trámite** se puede **añadir** unidades documentales. En Concentración, Histórico y en la pantalla de Expedientes se puede consultar/ver, pero no dar de alta nuevas unidades.

## Por qué

Hoy “Ver archivo” abre `/storage/...` en otra pestaña y no ofrece una experiencia de consulta integrada. El personal necesita revisar el contenido digital del expediente sin salir del flujo de Archivo. Restringir el alta a Trámite alinea la captura digital con la etapa del ciclo vital donde el expediente aún está en gestión activa.

## Para quién

- **Personal operativo AGQROO** (roles `root`, `administrador`, `editor`, `lector`) que ya pueden ver el modal de unidades documentales.
- Quien añade unidades: el mismo personal, **solo** desde la pantalla de **Trámite**.

## Criterios de aceptación

_Condiciones verificables que deben cumplirse para dar la feature por terminada. Redacta cada una de forma que se pueda comprobar con un sí/no. Marca `[x]` al cumplirse._

- [x] Desde **Ver Unidades Documentales** (Trámite, Concentración, Histórico y Expedientes), al elegir un PDF se abre un visor overlay (lightbox) sin ir a otra pestaña como único camino.
- [x] Si el expediente tiene varios PDFs, el visor permite navegar **anterior/siguiente** entre ellos (efecto carrusel) y muestra caption con tipo/nombre (y descripción si existe).
- [x] Unidades **sin archivo** se listan con sus metadatos pero no abren el visor.
- [x] El alta de unidades documentales (**Añadir Unidad Documental**) está disponible **solo en Trámite**; no aparece (o no funciona) en Concentración, Histórico ni Expedientes.
- [x] En el alta desde Trámite, el input de archivo acepta **solo PDF**; si se intenta otro tipo, la UI/API lo rechaza con mensaje claro en español.
- [x] Cualquier rol que ya puede abrir el modal puede previsualizar (sin rol extra).
- [x] Textos de UI y mensajes en **español**.

## Fuera de alcance

- Preview de imágenes u otros formatos (Word, Excel, zip, etc.): en v1 solo PDF.
- Editor/anotaciones sobre el PDF, OCR, o descarga masiva.
- Cambiar el modelo de almacenamiento (sigue en `storage` público / disco `public` de Laravel).
- Reactivar o mejorar pantallas legacy (`/buscar`, etc.) salvo reutilizar el patrón Fancybox como referencia.
- Cambiar jerarquía archivística, carátulas, reglas documentales o estados del ciclo vital (salvo la regla de negocio: alta de unidades solo en trámite).
- API pública nueva.
