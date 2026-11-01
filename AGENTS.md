# AGENTS.md — Guía Rectora para Agentes y Arquitectos de Software
## EESP Pukllasunchis (Cusco) · Intranet Institucional & **Ayllu Puklla**

> **Propósito:** Este documento establece las reglas arquitectónicas, pedagógicas y de seguridad innegociables para cualquier intervención en el repositorio `JesusDjm2/Pukllasunchis`. Todo agente o desarrollador debe leer este archivo y el directorio `docs/` antes de planificar o modificar código.

---

## 1. Identidad del Proyecto y Marca
* **Nombre oficial de la red social académica:** **Ayllu Puklla — Un espacio para aprender y construir comunidad**.
* **Restricción de marca:** **PROHIBIDO** utilizar el nombre «Plaza Puklla». El ítem de entrada en el menú principal lleva el nombre provisional **«Inicio»** (identificado con la marca **Ayllu Puklla**).
* **Sentido pedagógico:** Inspirado en la crianza mutua (*Uywanakuy*), el diálogo de saberes, la reciprocidad (*Ayni*), el bilingüismo quechua–castellano y la Formación Inicial Docente (FID) / Profesionalización Docente (PPD) con enfoque de Educación Intercultural Bilingüe (EIB).

---

## 2. Stack Confirmado y Fuentes de Verdad
* **Framework y Lenguaje:** Laravel 10.50.2 · PHP 8.2 · MySQL 8+ / InnoDB (`utf8mb4`).
* **Frontend:** Blade + Bootstrap 4 (SB Admin 2) + Tokens CSS institucionales (`public/css/pk-tokens.css`, `public/css/pk-sidebar.css`, `public/css/ayllu-puklla.css`) con soporte para modo claro, tenue (`dim-mode`) y oscuro (`dark-mode`).
* **Autorización:** `spatie/laravel-permission` sobre el guard `web` y modelo único `App\Models\User` (`users`).
* **No duplicar fuentes de verdad:**
  * Identidad única: `users` (`id`, `dni`, `name`, `apellidos`, `email`, `condicion`, `programa_id`, `ciclo_id`).
  * Perfiles académicos: `alumnos` (FID, rol `alumno`), `ppds` (PPD, rol `alumnoB`), `docentes` (rol `docente`), tutores (`tutor_ciclos`, rol `tutor`), egresados (`users.condicion = 'Egresado'`).
  * Estructura curricular: `programas` → `ciclos` → `cursos` → `silabos` (`unidades`, `rubricas`, `enfoque_silabos`).
  * Proyectos Integradores: `proyectos` (`ciclos.proyecto_id`, `proyecto_docente`) y módulo **Taq'e** (`taqe_grupos`, `taqe_grupo_alumno`, `taqe_trabajos`, `taqe_solicitudes_ayuda`).
  * Anuncios y eventos: `comunicados` y `minkarikuys`.
  * Asistencia: `asistencias` y `asistencia_detalles`.

---

## 3. Separación Estricta de Tres Planos (Innegociable)
1. **Plano 1 — Social y Comunitario (`ayllu_publicaciones`, `ayllu_comentarios`, `ayllu_reacciones`, `ayllu_grupos`):**
   * Espacio de diálogo de saberes, anuncios (`Comunicado`, `Minkarikuy`), recursos y vida comunitaria.
   * Los estudiantes publican libremente en sus Comunidades de Curso, Ciclo y Grupos; el muro general institucional prioriza comunicados oficiales, docentes, tutores y publicaciones destacadas/moderadas.
   * **Nunca otorga calificación oficial.**
2. **Plano 2 — Evaluación y Retroalimentación Formativa (`ayllu_entregas_formativas`, `ayllu_retroalimentaciones`):**
   * Acompañamiento continuo sobre las unidades del **Sílabo Vivo** (`silabos`, `unidades`, `rubricas`) y los productos que tributan al **Proyecto Anual Integrador** (`proyectos`, `taqe_trabajos`).
   * Privado entre el estudiante (o su equipo `TaqeGrupo`) y el docente del curso.
   * Usa niveles formativos descriptivos (*En inicio, En proceso, Logrado, Destacado*) y diálogo pedagógico; **no altera las actas oficiales**.
3. **Plano 3 — Notas, Asistencia y Trámites Oficiales (`calificacions`, `periodouno`, `periodo_dos`, `periodo_tres`, `periodos`, `calificacionesppds`, `periodo_ppds`, `asistencias`, `matriculas`):**
   * **Estrictamente privado e individual.** Gobernado por las ventanas oficiales de `PeriodoActual` / `PeriodoActualPpd`.

---

## 4. Reglas de Base de Datos, Privacidad y Git
1. **Migraciones 100% aditivas y reversibles:** Prohibido ejecutar `migrate:fresh`, `migrate:reset`, `DROP TABLE` sobre tablas existentes o eliminar columnas en uso. Toda migración nueva debe verificar `Schema::hasTable` / `Schema::hasColumn` y contar con `down()` seguro.
2. **Protección de datos y menores:** Prohibido exponer datos reales sensibles (PII), secretos de `.env` o imágenes de menores de edad.
3. **Google Classroom:** No asumir conexión OAuth preexistente. En `cursos` existen `classroom` (enlace) y `clave` (código); la integración API se despliega de forma gradual sin bloquear el funcionamiento local.
4. **Rama de trabajo:** Desarrollar **Ayllu Puklla** en la rama `feature/ayllu-puklla` verificando siempre `git status` para no perder trabajo en progreso de otros módulos.
