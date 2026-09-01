# Solicitud de cotización — App móvil del Sistema Completo Puklla (Alcance total)

> Documento de alcance funcional para que el proveedor/desarrollador Flutter cotice tiempo y
> costo. No incluye estimaciones de horas ni precios — eso lo define cada proveedor.
>
> Contexto técnico completo del sistema base: ver `DOCUMENTACION-TECNICA-SISTEMA.md` en este
> mismo repositorio.

## 1. Objetivo

Desarrollar una app móvil híbrida (Flutter) que replique, para **todos los roles existentes**
del sistema web Puklla, las funcionalidades que hoy tienen disponibles en la plataforma web,
consumiendo una API REST construida sobre el backend Laravel actual.

## 2. Roles incluidos en este alcance

Todos los roles del sistema: Alumno FID (`alumno`), Alumno PPD (`alumnoB`), Docente (`docente`),
Tutor (`tutor`), Admin (`admin`), Super-admin (`super-admin`), Admin de Bolsa de Trabajo
(`adminB`), y las funcionalidades públicas sin login (formularios QR).

## 3. Funcionalidades incluidas, por rol

### Alumno FID y Alumno PPD
- Login, perfil y ficha de matrícula.
- Matrícula por período (formulario corto + subida de voucher de pago) y estado de verificación.
- Calificaciones en tiempo real (actuales e históricas) — ver Cotización 1 para el detalle.
- Cursos matriculados por ciclo.
- Cursos Especiales asincrónicos (LMS): catálogo, inscripción, lecciones (texto/audio/video),
  ejercicios interactivos, seguimiento de progreso.
- Comunicados institucionales.
- Chatbot institucional (PukllaBot).

### Docente
- Perfil y cursos que dicta (FID y/o PPD).
- Listado de alumnos por curso.
- Registro de calificaciones (FID y PPD).
- Consulta y gestión de sílabos (currículo: competencias, capacidades, enfoques, estándares,
  proyectos, unidades, rúbricas) con exportación PDF.
- Reporte de incidencias de alumnos.
- Gestión de contenido de Cursos Especiales, si dicta el LMS (niveles, unidades, lecciones,
  ejercicios, audio).

### Tutor
- Dashboard con ciclos asignados.
- Atención de incidencias, solicitudes de tutoría individual y sugerencias de sus ciclos.
- Generación/visualización de los QR de tutoría y sugerencias.

### Admin / Super-admin
- Gestión de usuarios y asignación de roles.
- Gestión de programas, ciclos y cursos.
- Gestión de períodos académicos (apertura/cierre, generación de snapshots de calificaciones).
- Verificación manual de vouchers de matrícula (FID y PPD).
- Gestión de postulantes (admisión), incluida conversión de postulante a alumno.
- Exportación de reportes (Excel), generación de carnet.
- Gestión de comunicados y de Minkarikuy (eventos destacados).
- Moderación de ofertas de la bolsa de trabajo.
- Bitácora de accesos y sesiones activas (solo super-admin).

### Público, sin login (equivalente a los formularios QR actuales)
- Postulación/inscripción a FID y PPD.
- Bolsa de trabajo: publicar oferta y postularse.
- Libro de Reclamaciones.
- Reporte de incidencias.
- Solicitud de tutoría individual y buzón de sugerencias (con opción anónima).

## 4. Requerimientos técnicos que implica este alcance

- Construcción completa de la capa de API REST (Laravel Sanctum) para **todos** los módulos
  listados — ver la propuesta de endpoints en la sección 9 de
  `DOCUMENTACION-TECNICA-SISTEMA.md` como punto de partida, ampliándola para el bloque de
  administración (que hoy solo está esbozado a alto nivel).
- Manejo de múltiples roles por sesión de usuario, con navegación/menú adaptado según el rol
  (un docente que también es tutor debe poder alternar entre ambas vistas, por ejemplo).
- Subida de archivos desde la app (voucher de matrícula, evidencias de incidencias, contenido de
  sílabos) — definir estrategia de almacenamiento (igual que backend actual o alternativa).
- Todo lo señalado en las advertencias técnicas de `DOCUMENTACION-TECNICA-SISTEMA.md`
  (duplicación FID/PPD, dependencia de worker de colas para notificaciones, rol tutor sin
  perfil docente obligatorio, ruta rota detectada, campos de calificación sin documentar).
- Dado el tamaño del alcance, se recomienda que el proveedor proponga un desarrollo por fases
  (ej. primero alumno/docente, luego admin, luego módulos públicos) en lugar de un solo entregable
  monolítico.

## 5. Entregables esperados

- App Flutter funcional para Android (y iOS si aplica) cubriendo todos los roles y módulos
  descritos, con navegación adaptada a cada rol.
- API REST completa y documentada (ej. colección Postman/OpenAPI) para todos los módulos.
- Manejo de sesión/token seguro (Sanctum), incluyendo el caso de cuentas `inhabilitado`.
- Plan de fases propuesto por el proveedor, con entregables intermedios verificables.
- Documentación de despliegue e integración con el backend Laravel existente.

## 6. Preguntas para el proveedor

Se solicita que la cotización especifique:
- Plataformas objetivo (Android / iOS / ambas) y versiones mínimas soportadas.
- Propuesta de fases/orden de desarrollo dado el tamaño del alcance.
- Si el proveedor construye también los endpoints de API en Laravel, o solo el frontend Flutter
  asumiendo que la API la entrega el equipo actual.
- Enfoque técnico para el "tiempo real" en calificaciones y notificaciones (push, polling,
  websockets).
- Cómo manejaría la subida de archivos (vouchers, evidencias, sílabos) desde la app.
- Tiempo estimado y forma de pago propuesta por el proveedor.
