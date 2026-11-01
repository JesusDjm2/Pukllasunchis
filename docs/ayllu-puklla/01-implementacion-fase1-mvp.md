# Implementación Fase 0.5 + Fase 1 (MVP) — Ayllu Puklla

**Lema:** *«Ayllu Puklla — Un espacio para aprender y construir comunidad»*  
**Etiqueta en menú:** **«Inicio»**  
**Rama Git:** `feature/ayllu-puklla`

---

## 1. Respaldo Previo y Gobernanza

- **Gobernanza de Agentes:** Se creó `AGENTS.md` en la raíz del repositorio documentando las restricciones innegociables (nunca usar el nombre descartado, separación estricta de los 3 planos de privacidad, prohibición de comandos destructivos, preservación de cambios en curso de `Asistencias` y `Tutor`).
- **Backup completo de Base de Datos previo a migraciones:**  
  `storage/backups/pre_ayllu_puklla_20261031_191647.sql` (24 MB, ignorado en Git mediante `.gitignore`).

---

## 2. Migración Aditiva y Reversible

Archivo: `database/migrations/2026_10_31_193000_create_ayllu_puklla_mvp_tables.php`

Tablas creadas (100 % aditivas, sin modificar ni eliminar columnas existentes):
1. `ayllu_perfiles`: Perfil intercultural bilingüe (`saludo_intercultural`, `lengua_preferida`, `biografia`, `saberes_intereses`, `mostrar_portafolio_comunidad`).
2. `ayllu_grupos`: Comunidades y grupos interculturales (con semilla inicial de 4 comunidades oficiales: *Ayllu Qhichwa Simi*, *Tinkuy de Práctica Preprofesional e Investigación EIB*, *Kuska Yachasunchis — Expresión, Arte y Saberes Andinos*, y *Comunidad de Tutoría y Buen Vivir*).
3. `ayllu_grupo_miembros`: Membresías y roles por grupo (`miembro`, `moderador`, `docente_acompanante`).
4. `ayllu_publicaciones`: Publicaciones comunitarias por ámbito (`institucional`, `programa`, `ciclo`, `curso`, `grupo`).
5. `ayllu_comentarios`: Hilos de diálogo comunitario y aportes docentes.
6. `ayllu_reacciones`: Reacciones con identidad quechua-castellano (`allinmi`, `yupaychani`, `kusikuy`, `yachay`).
7. `ayllu_entregas_formativas`: Entregas de evaluación formativa por curso/unidad con autoevaluación, curaduría en el Portafolio Docente EIB y vínculo directo hacia `taqe_grupos` y `taqe_trabajos`.
8. `ayllu_retroalimentaciones`: Retroalimentación formativa dialogada entre docente y estudiante.
9. `ayllu_eventos_calendario`: Hitos complementarios del Calendario Vivo.
10. `ayllu_mensajes_chat`: Mensajes del Chat Académico (*Rimanakuy*) por curso o comunidad Ayllu.

Permisos Spatie registrados e integrados con roles existentes:
- `ayllu.ver-inicio`
- `ayllu.publicar-muro-general`
- `ayllu.publicar-comunidad`
- `ayllu.moderar`
- `ayllu.retroalimentar-formativo`

---

## 3. Modelos, Servicios y Controladores

- **Modelos Eloquent (`app/Models/Ayllu/`):**
  - `AylluPerfil`
  - `AylluGrupo`
  - `AylluPublicacion`
  - `AylluComentario`
  - `AylluReaccion`
  - `AylluEntregaFormativa`
  - `AylluRetroalimentacion`
  - `AylluEventoCalendario`
  - `AylluMensajeChat`
- **Servicio de Contexto Multi-Rol (`app/Services/AylluContextService.php`):**
  - Unifica el contexto para Estudiantes FID (`Alumno`), Estudiantes PPD (`ppd`), Docentes (`Docente`), Tutores, Administradores y Egresados.
- **Controlador Principal (`app/Http/Controllers/Ayllu/AylluController.php`):**
  - Implementa todas las vistas y acciones del MVP respetando las decisiones A1–A4.

---

## 4. Vistas Blade y Navegación Responsiva (Móvil + Escritorio)

- **Hoja de estilos dedicada:** `public/css/ayllu-puklla.css` (basada en los tokens institucionales `pk-tokens.css`, diseño de 3 columnas en escritorio y barra inferior fija de 5 accesos en móvil `< 992px`).
- **Vistas en `resources/views/ayllu/`:**
  - `layout.blade.php`
  - `inicio.blade.php`
  - `curso.blade.php`
  - `proyecto_portafolio.blade.php`
  - `grupos.blade.php`
  - `calendario.blade.php`
  - `chat.blade.php`
  - `privado.blade.php`
  - `perfil.blade.php`
  - `partials/publicacion_card.blade.php`
- **Integración del botón «Inicio» (Ayllu Puklla) en los layouts existentes:**
  - `resources/views/layouts/alumno.blade.php`
  - `resources/views/layouts/profesionalizacion.blade.php`
  - `resources/views/layouts/docente.blade.php`
  - `resources/views/layouts/partials/menu-lateral-gestion.blade.php`
  - `resources/views/egresado/intranet.blade.php`
