# Mapa de arquitectura — Pukllasunchis

Mapa escrito a mano y verificado contra el código (15/11/2026). Sirve para ubicarse rápido; ante la duda manda el código. Si se mueve algo grande (servicio nuevo, módulo nuevo, cambio de rol), actualizar este archivo.

Stack: Laravel 10 (PHP) + Blade, MySQL (XAMPP), Spatie Permission (roles), Sanctum (API móvil), DomPDF, Google Drive. App móvil en `FlutterPuklla/` (Flutter + Riverpod + go_router + dio).

> La base real tiene cambios manuales que no están en las migraciones: antes de confiar en una migración nueva, revisar la tabla real.

## 1. Rutas: 4 archivos, 4 públicos

| Archivo | Para quién | Qué tiene |
|---|---|---|
| `routes/web.php` | Público | Sitio institucional (programas, admisión, trámites, líneas, novedades), informes, sitemap. |
| `routes/admin.php` | Usuarios con sesión (web) | Todo lo interno: paneles por rol, calificaciones, TAQE, tutoría, titulación (gestión e intranet del estudiante), cursos especiales, Ricardo Palma, bitácora. |
| `routes/api.php` | App móvil (`/api/v1`, Sanctum) | API **aditiva**: no modifica la web. Detalle en `_notes/api-v1-endpoints.md`. |
| `routes/pukllabot.php` | Público / super-admin | Chat del asistente PukllaBot y reindexado. |

## 2. Usuarios, roles y a dónde entra cada uno

La cuenta es `users`. Los datos de matrícula están en una **ficha** aparte que repite algunos campos; `App\Services\SincronizarFicha` los mantiene iguales.

- **FID** (formación inicial docente): rol `alumno`, ficha `alumnos` (modelo `Alumno`).
- **PPD** (profesionalización docente): rol `alumnoB`, ficha `ppds` (modelo `ppd`).
- **Egresado**: `users.condicion = 'Egresado'`. No es un rol: tiene su propia intranet, sin matrícula, cursos ni tutoría.

Destino al iniciar sesión (`Auth\LoginController::authenticated`; el mismo criterio para «Volver a mi panel» está en `App\Support\PanelDeUsuario`, y **hay que cambiar los dos juntos**):

| Rol / condición | Panel |
|---|---|
| `super-admin`, `admin` | ruta `admin` |
| `docente` | `vistaDocente` |
| `tutor` | `tutor.dashboard` |
| `adminB` | Bolsa de trabajo (`trabajo.index`) |
| `gestor-titulacion`, `secretaria` | Titulación (`titulacion.index`) |
| Egresado | `egresado.intranet` (`EgresadoIntranetController`) |
| `alumnoB` | `ppd.index` |
| `alumno` | `alumnos.index` |
| `inhabilitado` | `inhabilitado` |
| Sin rol reconocido | `/` |

Si alguien entra desde un enlace a `/mi-titulacion/...`, vuelve a ese enlace en lugar de ir a su panel.

Otros roles: `investigador` (TAQE). Los permisos finos se arman con middleware `role:` en `admin.php` y servicios propios (ver sección 4).

## 3. Módulo Titulación (el más grande)

Proceso: **PTI inscrito → PTI aprobado (RD) → Carta de aceptación → TI → Tesis → grado/título**. Todo se apoya en `seguimiento_titulacion` (uno por estudiante, con `grupo_id` para los grupos).

### Tablas
`seguimiento_titulacion`, `estados_titulacion`, `titulacion_ptis`, `titulacion_resoluciones` (RD), `titulacion_cartas_aceptacion`, `titulacion_solicitudes` (bandeja de trámites: PTI, originalidad, etc.), `titulacion_tesis`, `titulacion_actas`, `titulacion_participantes`, `titulacion_asesores`, `titulacion_jurados`, `titulacion_documentos`, `titulacion_avisos_pti`, `titulacion_importaciones`, `titulacion_auditorias`. Modelos en `app/Models/Titulacion/`.

### Servicios (`app/Services/Titulacion/`): la lógica vive aquí
Reglas del proceso:
- `AvanceTitulacion`: avance **real**, calculado solo de lo registrado (toda RD = etapa aprobada). Lo usan casi todos.
- `ReglasProceso`: orden obligatorio; no se registra un paso sin el anterior.
- `PasosProceso`: línea de tiempo de la ficha del gestor (hecho / actual / espera).
- `VigenciaPti`: el PTI vence a los 2 años (o la fecha «FV» de Secretaría).
- `GuiaTramites`: qué trámites le tocan **ahora** al estudiante (lee `config/titulacion.php › tramites`).
- `TramitesExtraordinarios`: catálogo de trámites con código y monto de Caja Cusco.
- `DiasHabiles`: plazos en días hábiles (sin feriados de Perú).

Quién puede qué:
- `AreasTitulacion`: **la única lista** de qué área (Secretaría / Investigación / super-admin) modifica qué. La aplica el middleware `AreaTitulacion` (alias `titulacion.area`) sobre todo el grupo `titulacion.*`. Una ruta de escritura sin área queda solo para super-admin.

Personas y grupos:
- `GrupoTitulacion`: compañeros de grupo (`grupo_id`; para datos antiguos, por expediente).
- `CambioGrupo`: corrección manual del grupo por Secretaría.
- `ReconocerPersonas`: identifica al estudiante desde un formulario (correo, DNI, nombre, celular).
- `ResolverDocente`, `NombresImportados`, `ParticipacionAsesores`: asesores y jurados escritos a mano → docentes reales.

Documentos:
- `PlantillasRd`, `ArchivosRd`, `RdPti`, `NormalizarRdPti`, `MensajesRd`: RD en PDF, su archivo, el correo con la RD.
- `CartaPresentacion`: Word de la carta que envía Secretaría.
- `Expediente`: número de expediente normalizado (`0001-T/PUK`).
- `BandejasSolicitudes`: pestañas de la sección «Solicitudes» del gestor.

Importación desde Excel / formularios de Google: `ImportarFormularioGoogle` (base), `ImportarSolicitudesPti`, `ImportarSolicitudesOriginalidad`, `ImportarCartasFormulario`. Comandos `php artisan titulacion:*` en `app/Console/Commands/Titulacion*`.

Configuración: `config/titulacion.php` (guías, contactos, trámites, PDF de conceptos de pago) y `TITULACION_ORIGINALIDAD_ALUMNOS` en `.env` (apagado = la originalidad va al formulario de Google).

### Controladores
- **Gestión** (`/titulacion`, roles `super-admin|gestor-titulacion|secretaria` + `titulacion.area`): `app/Http/Controllers/Titulacion/` — `TitulacionController` (ficha y listado), `EtapaTiController`, `EtapaTesisController`, `RdPtiController`, `ResolucionDirectoralController`, `CorreoRdController`, `SolicitudesController`, `SolicitudPtiController`, `SolicitudOriginalidadController`, `CambioGrupoController`, `ImportarCartasController`, `InicioProcesoController`, `SincronizacionController`.
- **Estudiante / egresado en la web** (`/mi-titulacion/*`, solo `auth`): `MiSolicitudPtiController`, `CartaAceptacionController`, `MiSolicitudOriginalidadController`; intranet del egresado en `EgresadoIntranetController`; «¿Algún dato no coincide?» (aviso a los gestores) en `MiTitulacionController`.
- **App móvil**: `Api\TitulacionController` (`GET /alumno/titulacion`) y `Api\MiTitulacionSolicitudController` (formularios de PTI, carta y originalidad). Este último **replica** las reglas de los tres controladores web (`shortcut:` marcado en el archivo).

> Hay dos `TitulacionController`: `Titulacion\TitulacionController` (web, gestor) y `Api\TitulacionController` (app). Mirar el namespace.

## 4. Resto de módulos (web)

| Módulo | Controladores | Servicios / apoyo |
|---|---|---|
| Calificaciones FID | `CalificacionController`, `DocenteCOntroller`, `PeriodoActualController` | `AccesoCalificaciones` (un docente solo toca SUS cursos), `ArchivoParciales`, `RendimientoCursos`. Tablas en vivo `periodouno`, `periodo_dos`, `periodo_tres`; notas finales archivadas en `periodos`. |
| PPD | `PpdController`, `AdminPpdController`, `CalificacionesppdController`, `PeriodoActualPpdController` | `PromocionPpd`, `CierreAnioPpd` (al cerrar el año la promoción pasa a Egresada). |
| Seguimiento académico | `ExpedienteController`, `PanelSeguimientoController`, `TutorController` | `HistorialAcademico`, `Trayectoria` (eventos por cambio de perfil, condición, ciclo o programa; lo dispara el observer de `User`), `SeguimientoCiclo`, `PanelInstitucional`. |
| Administración | `AdminController`, `AdminFidController`, `CicloController`, `ProgramaController` | `EstadoMatricula`, `DatosDemograficos`, `SincronizarFicha`. |
| TAQE / Investigaciones | `TaqeController`, `TaqeInvestigadorController`, `TaqeProyectoDocenteController`, `TaqeSuperAdminController` | Traits en `Controllers/Concerns` (`ArchivosTaqe`, `GestionaGruposTaqe`). |
| Tutoría, incidencias, sugerencias | `TutoriaController`, `IncidenciaController`, `SugerenciaController` | `MensajeAlumnos`, `ContactoAlumno`, `Telefono`. |
| Cursos especiales | `Controllers/CursosEspeciales/*` | Modelos `CursosEspeciales/*` (audio por Drive). |
| Ricardo Palma (actas) | `Controllers/RicardoPalma/*` | `RicardoPalma/LectorActaGemini` (lee actas con Gemini). |
| Bolsa de trabajo | `BolsaController`, `BolsaTrabajoOfertaController` | `Support/BolsaTrabajoListado`. |
| Comunicados, notificaciones | `ComunicadoController`, `NotificacionController` | `Support/NotificacionesPorRol`. |
| Minkarikuy | `MinkarikuyController` | QR con `simplesoftwareio/simple-qrcode` (`Minkarikuy::qrSvg`). |
| PukllaBot | `PukllaBotChatController` | `PukllaBot/PukllaBotRagService` (conocimiento en `storage/app/pukllabot`). |
| Seguridad | `Seguridad/BitacoraAccesosController` | Bitácora de sesiones e intentos fallidos. |

Servicios externos compartidos: `GoogleDriveService` (TAQE, cursos especiales, carta de aceptación), `WhatsappService`, `ConsultaDniService`.

## 5. App móvil (`FlutterPuklla/`)

- `lib/core/`: `env` (`API_BASE_URL` por `--dart-define`), `network` (dio + token), `storage`, `theme`, `widgets` (incluye la barra inferior).
- `lib/features/<módulo>/{data, application, presentation}`: `data` = modelos y repositorio (llama a la API), `application` = providers de Riverpod, `presentation` = pantallas.
  - Módulos: `auth`, `home`, `perfil`, `calificaciones`, `tutoria`, `taqe`, `titulacion`, `docente`, `cursos_especiales`, `bolsa_trabajo`, `comunicados`, `notificaciones`, `recursos`.
- `lib/routing/`: `app_router.dart` (rutas y a dónde entra cada usuario: docente/tutor → `/docente`, el resto → `/home`) y `app_drawer.dart`.
- **Egresado** (`AuthUser.esEgresado`, viene de `/me`): portal propio en `home`, sin matrícula ni cursos; la barra inferior muestra Titulación y Guías.

Correr en local: web en `127.0.0.1:8001`; app con `flutter run -d web-server --web-port=5050 --web-hostname=127.0.0.1 --dart-define=API_BASE_URL=http://127.0.0.1:8001/api/v1`.

Tests: `php artisan test tests/Feature/Api` y `flutter test` dentro de `FlutterPuklla/`.
