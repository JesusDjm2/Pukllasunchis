# Nuevo requerimiento general

Deseo agregar un **sistema de Cursos Especiales Asincrónicos**, que inicialmente albergará un curso de **Idioma Quechua**, pero que debe estar diseñado para que **el super administrador pueda crear nuevos cursos** en el futuro (ej. "Uso de Herramientas Digitales", "Educación Emocional", etc.).

## Objetivo arquitectónico

Crear un **subsistema genérico y extensible** de cursos asincrónicos dentro de la Intranet, donde:

- El **super admin** puede crear, editar y eliminar cursos.
- Cada curso tiene su propia estructura (niveles, unidades, lecciones, ejercicios, evaluaciones).
- Los **estudiantes** se inscriben voluntariamente (o por asignación del admin) y avanzan asincrónicamente.
- El **Super Admin** ve el progreso de los estudiantes.
- El sistema registra progreso individual por estudiante, por curso y por período académico.

## Primer curso concreto: Quechua

- Niveles: Básico, Intermedio, Avanzado.
- Unidades temáticas por nivel (saludos, familia, números, etc.).
- Lecciones con texto, audio (y opcionalmente video).
- Ejercicios interactivos (opción múltiple, completar, emparejar).
- Evaluación por unidad + examen final por nivel.

## Segundo curso futuro (ejemplo)

- Curso: "Uso de Herramientas Digitales para Estudiantes"
- Módulos: Drive, Calendar, Presentaciones, IA básica, etc.
- Misma estructura: Unidades → lecciones → ejercicios → evaluación.

## Integración con el sistema existente

- Solo estudiantes activos (matriculados en período actual) pueden acceder.
- Progreso registrado por: `estudiante_id`, `curso_especial_id`, `periodo_id`.
- Profesores asignados pueden ver progreso (sin editar notas automáticamente, salvo excepción).
- Super admin puede:
  - Crear/editar/eliminar cursos especiales.
  - Asignar profesores a cursos especiales.
  - Inscribir estudiantes manualmente o permitir autoinscripción.
  - Gestionar contenidos (niveles, unidades, lecciones, ejercicios, audios).

## Opcional (deseable)
- Generar constancia o certificado por curso completado.


## Lo que necesito de ti como Claude

Ayúdame a:

1. **Diseñar el modelo de datos genérico** para este subsistema (tablas/colecciones) que permita:
   - Cursos especiales.
   - Estructura jerárquica (niveles/módulos → unidades → lecciones → ejercicios).
   - Tipos de ejercicios (definidos por tipo + JSON de configuración).
   - Progreso de estudiantes.
   - Asignación de profesores a cursos.

2. **Mostrar relaciones con tablas existentes** (alumnos, profesores, periodos, usuarios).

3. **Proponer endpoints o funciones backend** para:
   - CRUD de cursos especiales (solo super admin).
   - Obtener cursos disponibles para un estudiante.
   - Marcar lección completada / ejercicio resuelto.
   - Obtener progreso por curso.

4. **Ejemplos de código clave** (según tu stack o pseudocódigo) para:
   - Registro de progreso (evitar duplicados, respetar secuencia).
   - Validación de requisitos (ej. no pasar a unidad 2 sin aprobar unidad 1).

5. **Sugerir cambios en la Intranet**:
   - Nuevo menú: "Cursos Especiales" → lista de cursos disponibles.
   - Vista interna de un curso (tree de navegación + contenido + ejercicios).
   - Panel de progreso para estudiantes.
   - Panel simple para profesores (ver avance de sus estudiantes).

6. **Recomendar estrategia de extensibilidad**:
   - ¿Cómo agregar un nuevo curso (Herramientas Digitales) sin modificar código central?
   - ¿Cómo manejar distintos tipos de ejercicios en el futuro?

## Restricciones importantes

- No debe romper la lógica de períodos y calificaciones existente.
- Los cursos especiales son **paralelos** a los cursos regulares (no mezclar).
- El sistema debe funcionar **asincrónicamente** (sin sesiones en vivo obligatorias).
- Priorizar un MVP funcional, pero con base extensible.

## Formato de respuesta deseado

1. **Resumen ejecutivo** de la solución propuesta.
2. **Modelo de datos** (diagrama o SQL/JSON Schema).
3. **Lista de tareas para implementar** (orden sugerido).
4. **Código de ejemplo** para la parte más crítica (progreso + estructura de curso).
5. **Guía de integración en Intranet actual**.
6. **Preguntas abiertas** (si falta información).

---

**Por favor, actúa como un ingeniero de software con experiencia en LMS (Learning Management Systems) y sistemas extensibles. Si mi stack no está claro, pídeme esa información antes de detallar código.**