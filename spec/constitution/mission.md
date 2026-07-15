# Misión

> Razón de ser del proyecto. Es la referencia que decide si una feature "encaja" o no.

## Qué construimos

GEDIS es una aplicación web interna de **gestión documental y archivística** para AGQROO. Centraliza el catálogo de clasificación, la captura/consulta de carátulas, el seguimiento del ciclo vital de expedientes y la generación de instrumentos y reportes archivísticos oficiales.

Piezas principales:

1. **Backend (Laravel)** — autenticación, reglas de negocio, persistencia, generación de PDF/Excel y endpoints web/JSON que alimentan las pantallas.
2. **Frontend (Blade + Vue 3)** — panel de administración (SB Admin) con pantallas Vue montadas por módulo: expedientes, carátulas, archivo, catálogo, reportes y usuarios.
3. **Módulos de negocio** — Expedientes (carga/carátula/consulta), Archivo (trámite / concentración / histórico / bajas), Catálogo y estructura (fondos→expedientes, cuadro general, catálogo de disposición, catálogo de secciones), Reportes y Administración (dependencias, departamentos, usuarios).

## Para quién

- **Personal archivístico y operativo de AGQROO** — captura carátulas, gestiona expedientes y consulta el ciclo vital del documento.
- **Administradores de dependencia/departamento** — mantienen estructura organizativa y usuarios (roles: `root`, `administrador`, `editor`, `lector`).
- **Responsables de control archivístico** — generan y consultan instrumentos (cuadro general, catálogos) y reportes.
- **Equipo técnico** — mantiene y extiende la plataforma sin romper la jerarquía ni las reglas documentales.

## Principios

- **La jerarquía archivística manda** — Fondo → (Subfondo) → Sección → Serie → (Subserie) → Expediente. Las features no aplanan ni saltan niveles sin decisión explícita.
- **El expediente tiene ciclo vital** — estados `en_tramite`, `en_concentracion`, `en_historico` y proceso de bajas; las transiciones son acciones de dominio, no simples flags de UI.
- **Las reglas documentales heredan de la serie** — plazos, vigencias, clasificación y técnicas de selección se aplican vía instrumento de control / reglas documentales, no se inventan ad hoc por pantalla.
- **La carátula describe el expediente** — es la ficha descriptiva (clasificación, sujeto obligado, soporte, valores, reserva, etc.), alineada al catálogo.
- **Alcance por dependencia/departamento** — los datos se filtran por la pertenencia del usuario; no es un repositorio global sin límites.
- **Instrumentos oficiales trazables** — cuadro general, catálogos y reportes deben poder generarse de forma consistente (vista previa + PDF).
- **UI en español** — textos de cara al usuario en español.

## Qué NO es

- No es un portal público ni de consulta ciudadana: es herramienta interna con login.
- No es un gestor documental genérico tipo Drive/SharePoint: no pretende colaboración libre, BPM genérico ni firma electrónica como producto núcleo.
- No es un ERP, CRM ni sistema contable.
- No es un producto API-first abierto al exterior: el núcleo es web autenticada (Blade/Vue + rutas web); una API pública requeriría feature dedicada.
- No pretende ser multi-institución SaaS: está orientado a AGQROO y sus dependencias/áreas.
