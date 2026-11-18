# Memoria y Recordatorios del Proyecto Puklla2026

## Tareas pendientes con recordatorio periódico

### 1. Volver a subir `public/pdf/Flujograma-de-procesos.pdf` (sin firmas del director)
- **Estado**: Pendiente (retirado el 2026-11-18 en local y en producción).
- **Motivo**: El PDF publicado contenía firmas del director que no deben estar expuestas públicamente.
- **Cambios temporales aplicados (local y producción)**:
  - Se eliminó el archivo `public/pdf/Flujograma-de-procesos.pdf`.
  - Se ocultó (comentó) la tarjeta **Flujograma de Procesos** en `resources/views/tramites/Mesa-de-partes.blade.php`.
- **Qué hacer cuando esté lista la versión corregida**:
  1. Subir el nuevo archivo sin firmas en `public/pdf/Flujograma-de-procesos.pdf` (local y producción por FTP).
  2. Descomentar la tarjeta en `resources/views/tramites/Mesa-de-partes.blade.php` (local y producción).
  3. Eliminar este recordatorio de `GEMINI.md` (y de la memoria del proyecto).
- **Instrucción para el asistente**: En esta conversación y en futuros chats de este proyecto, recuerda brevemente al usuario cada cierto tiempo (por ejemplo, una vez al inicio de una nueva sesión o al cerrar un bloque de trabajo, sin repetirlo en cada turno) que sigue pendiente volver a subir el `Flujograma-de-procesos.pdf` corregido y reactivar su enlace en Mesa de partes.

### 2. Volver a subir `public/pdf/Plan-Anual-de-trabajo-2025.pdf` (si corresponde, sin firmas del director)
- **Estado**: Pendiente (retirado el 2026-11-18 en local y en producción).
- **Motivo**: Retiro preventivo equivalente a `Flujograma-de-procesos.pdf` (firmas expuestas públicamente).
- **Cambios temporales aplicados (local y producción)**:
  - Se eliminó el archivo `public/pdf/Plan-Anual-de-trabajo-2025.pdf` (en producción verificado con `404` tanto en `/pdf/` como en `/public/pdf/`).
  - *Nota:* La tarjeta activa en `resources/views/foot/informacion-institucional.blade.php` ya apuntaba a `public/pdf/PAT-2026.pdf` (ninguna vista activa enlazaba ya al PDF 2025).
- **Qué hacer cuando esté lista la versión corregida (o si se descarta definitivamente)**:
  1. Si se desea volver a publicar, subir el archivo corregido sin firmas en `public/pdf/Plan-Anual-de-trabajo-2025.pdf` (local y producción por FTP).
  2. Eliminar este recordatorio de `GEMINI.md`.
