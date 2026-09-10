# Solicitud de cotización — App móvil de Calificaciones en Tiempo Real (Alcance acotado)

> Documento de alcance funcional para que el proveedor/desarrollador Flutter cotice tiempo y
> costo. No incluye estimaciones de horas ni precios — eso lo define cada proveedor.
>
> Contexto técnico completo del sistema base: ver `DOCUMENTACION-TECNICA-SISTEMA.md` en este
> mismo repositorio.

## 1. Objetivo

Desarrollar una app móvil híbrida (Flutter) que permita a **alumnos** consultar sus
**calificaciones en tiempo real** (período actual y períodos anteriores), y a **docentes/tutores**
consultar y registrar las notas de sus cursos, replicando el comportamiento que hoy existe en el
sistema web Puklla.

## 2. Roles incluidos en este alcance

| Rol | Incluido |
|---|---|
| Alumno FID (`alumno`) | Sí |
| Alumno PPD (`alumnoB`) | Sí |
| Docente (`docente`) | Sí |
| Tutor (`tutor`) | Sí, solo en lo relacionado a calificaciones de sus cursos como docente — **no** incluye el panel de tutorías/incidencias/sugerencias |
| Admin / Super-admin | No |
| Público sin login | No |

## 3. Funcionalidades incluidas

### Para Alumno (FID y PPD)
- Login con su cuenta existente del sistema Puklla.
- Ver su perfil básico (nombre, programa, ciclo actual).
- Ver sus calificaciones del período académico actual, actualizadas en tiempo real conforme el
  docente las va registrando (o con actualización casi inmediata vía notificación/polling).
- Ver el histórico de calificaciones de períodos académicos anteriores ya cerrados.
- Ver el listado de cursos matriculados en el ciclo actual.
- Recibir una notificación cuando se publique o modifique una calificación suya.

### Para Docente / Tutor
- Login con su cuenta existente.
- Ver el listado de cursos que dicta en el ciclo actual (FID y/o PPD según corresponda).
- Ver el listado de alumnos matriculados en cada curso.
- Consultar las calificaciones ya registradas de cada curso.
- Registrar/actualizar calificaciones de sus alumnos desde la app (equivalente al flujo de
  "calificar curso" que hoy existe en el sistema web).

## 4. Funcionalidades explícitamente fuera de este alcance

- Matrícula (formulario, subida de voucher, verificación de pago).
- Incidencias, tutorías individuales y buzón de sugerencias.
- Sílabos y gestión curricular (competencias, capacidades, rúbricas, etc.).
- Cursos Especiales asincrónicos (LMS Quechua).
- Comunicados, Minkarikuy, bolsa de trabajo, chatbot.
- Cualquier funcionalidad de administración (usuarios, roles, períodos, postulantes).
- Formularios públicos sin login (QR).

## 5. Requerimientos técnicos que implica este alcance

- Construcción de endpoints de API REST (Laravel Sanctum) para: login/logout, perfil, listado
  de cursos, calificaciones vigentes y calificaciones históricas (ver sección 5 y 9 de
  `DOCUMENTACION-TECNICA-SISTEMA.md` para el detalle del flujo de datos, que es distinto para
  FID y PPD).
- Mecanismo de "tiempo real" o casi real: puede resolverse con notificaciones push, polling
  periódico, o WebSockets/broadcasting — a definir con el proveedor según su propuesta técnica.
- Reutilización del sistema de notificaciones ya existente en Laravel (tabla `notifications`)
  como fuente de eventos de "calificación actualizada".
- La app debe distinguir automáticamente si el usuario logueado es alumno FID o PPD (o docente),
  y mostrar la información correspondiente sin que el usuario tenga que elegirlo manualmente.

## 6. Entregables esperados

- App Flutter funcional para Android (y iOS si aplica) cubriendo el alcance descrito.
- Endpoints de API REST correspondientes, documentados (ej. colección Postman/OpenAPI).
- Manejo de sesión/token seguro (Sanctum).
- Documentación breve de despliegue e integración con el backend Laravel existente.

## 7. Preguntas para el proveedor

Se solicita que la cotización especifique:
- Plataformas objetivo (Android / iOS / ambas) y versiones mínimas soportadas.
- Enfoque técnico para el "tiempo real" (push, polling, websockets).
- Si el proveedor construye también los endpoints de API en Laravel, o solo el frontend Flutter
  asumiendo que la API la entrega el equipo actual.
- Tiempo estimado y forma de pago propuesta por el proveedor.
