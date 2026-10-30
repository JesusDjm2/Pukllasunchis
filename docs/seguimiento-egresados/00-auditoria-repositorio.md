# 00 — Auditoría del Repositorio y Entorno Técnico
## PUKLLA EN RED · EESP Pukllasunchis

**Fecha de auditoría:** 30 de octubre de 2026  
**Auditor:** Antigravity (Pair Programming / Arquitectura de Software)  
**Rama activa:** `sistemadeseguimientodeegresados`  
**Repositorio:** `JesusDjm2/Pukllasunchis`  
**Estado del árbol de trabajo:** No destructivo, entorno local operativo con MySQL y Laravel activos.

---

### 1. Estado del Repositorio Git y Ramas

| Atributo | Valor confirmado | Detalle técnico |
|---|---|---|
| **Rama actual** | `sistemadeseguimientodeegresados` | Rama activa de desarrollo consolidada para Pull Request. |
| **HEAD commit** | `455d693` | `docs(seguimiento-egresados): especificacion maestra v3.0 y entregables de Fase 0` |
| **Historial reciente** | `f51f5fa` | `feat(seguimiento-egresados): incorporar visualizador interactivo y ruta de acceso rápido` |
| | `cac0bcc` | `feat(seguimiento-egresados): integracion en panel docente, gestion de grupos de entrevistadores y generador whatsapp` |
| | `b1abe43` | `Implementar módulo ejecutable de Seguimiento de Egresados V8 con persistencia en BD institucional` |
| | `bd0f46f` | `Propuesta y sistematización de requerimientos: Seguimiento de Egresados` |
| | `93aa96e` | `(origin/main, origin/HEAD, main) veirnes a github` |
| **Remoto configurado** | `origin` | `https://github.com/JesusDjm2/Pukllasunchis.git` (fetch & push) |
| **Diagnóstico de Push** | **Bloqueado (403)** | Token actual (`github_pat_11BKSS46...`) tiene alcance `metadata=read`; carece de `Contents: Read and write`. Requiere ajuste en GitHub. |
| **Archivos sin confirmar** | Limpios | Archivos del módulo `asistencias` se mantienen intactos en el working tree sin interferir. |

---

### 2. Pila Tecnológica Real (Stack Confirmado)

Contrario a suposiciones de versiones obsoletas en borradores previos, el entorno real ejecuta:

- **PHP:** `8.2.32 (cli)` (Zend Engine v4.2.32, OPcache habilitado, arquitectura ARM64 macOS).
- **Framework:** `Laravel 10.50.2` (enrutamiento robusto, Eloquent ORM, Blade, autenticación nativa).
- **Base de datos:** `MySQL Community Server 26.10.0` (GPL, charset utf8mb4, socket `/tmp/mysql.sock`, puerto 3306).
- **Servidor web de desarrollo:** `php artisan serve` activo en `http://127.0.0.1:8000`.
- **Librería de Permisos:** `spatie/laravel-permission` integrado.
- **Frontend Intranet:** Blade + Bootstrap 4 (SB Admin 2) + FontAwesome 5 + Tailwind CSS para componentes autónomos.

---

### 3. Módulos Existentes e Inspección de Componentes Egresados

#### 3.1. Modelo `GrupoEntrevistador.php`
- **Ubicación:** `app/Models/SeguimientoEgresado/GrupoEntrevistador.php`
- **Tabla subyacente:** `egresado_grupos_entrevistadores`
- **Casting de atributos:**
  - `entrevistadores` => `array` (JSON en MySQL)
  - `egresados_ids` => `array` (JSON en MySQL)
- **Estado:** Totalmente funcional, probado con operaciones CRUD vía API y UI.

#### 3.2. Generador de Enlaces para WhatsApp
- **Ubicación:** `app/Http/Controllers/SeguimientoEgresadoController.php::generarEnlaceWhatsapp()`
- **Comportamiento:** Preformatea el mensaje oficial para la campaña activa (`2026-II`), genera la URL codificada hacia `https://api.whatsapp.com/send?text=...` y apunta al landing móvil `route('seguimiento-egresados.campana-publica')`.
- **Estado:** Probado con respuesta JSON HTTP 200 y renderizado interactivo en la pestaña de Campañas.

#### 3.3. Rutas y Controladores Registrados
En `routes/web.php`:
- `GET /seguimiento-egresados`: Vista principal del módulo (Panel, Padrón, Grupos, Campañas, Llamadas, Territorio, Inteligencia).
- `POST /seguimiento-egresados/actualizar`: Endpoint de guardado de ficha de actualización.
- `POST /seguimiento-egresados/accion`: Creación de acciones/casos de acompañamiento.
- `POST /seguimiento-egresados/avanzar-accion/{id}`: Transición de estados de casos.
- `POST /seguimiento-egresados/llamada`: Registro de llamadas por encuestador.
- `POST /seguimiento-egresados/auditar-contacto`: Bitácora de acceso a teléfono/correo de egresados.
- `POST /seguimiento-egresados/grupos`: Creación de equipos de entrevistadores.
- `DELETE /seguimiento-egresados/grupos/{id}`: Eliminación de equipos de entrevistadores.
- `GET /seguimiento-egresados/campana-publica`: Landing autónomo para WhatsApp.
- `GET /seguimiento-egresados/visualizador`: Vista del visualizador de diseño interactivo.
- `GET /dev/login-docente`: Acceso rápido para pruebas de integración en entorno local.

#### 3.4. Integración en el Panel Docente
- `resources/views/layouts/docente.blade.php`: Se añadió el botón **«Sistema de seguimiento de egresados»** en el menú **«Mis funciones»** con icono `fas fa-user-graduate`.
- `resources/views/docentes/show.blade.php`: Se integró la tarjeta destacada de Coordinación de Egresados en el dashboard principal.
- `resources/views/egresado/intranet.blade.php`: Existe una vista previa institucional de egresado vinculada a su avance en titulación.

---

### 4. Matriz de Roles y Permisos (Spatie)

Actualmente en la base de datos `puklla`:

| Rol registrado | Usuarios asociados | Propósito actual en intranet |
|---|---|---|
| `super-admin` | 1 | Control total de sistema y configuración |
| `admin` | 2 | Administración académica y gestión general |
| `adminB` | 2 | Administración Bolsa de Trabajo y PPD |
| `docente` | 50 | Gestión de cursos FID, sílabos, calificaciones |
| `tutor` | 4 | Acompañamiento tutorial por ciclo |
| `investigador` | 2 | Seguimiento proyectos integradores Taq'e |
| `gestor-titulacion`| 1 | Módulo de titulación (planes, jurados, actas) |
| `alumno` | 240+ | Portal del estudiante regular |
| `alumnoB` | 130+ | Portal del estudiante PPD |
| `inhabilitado` | 1 | Cuentas suspendidas |

**Brecha identificada:** Aún no están creados formalmente los roles específicos `encuestador-seguimiento`, `coordinacion-empleabilidad`, `formacion-continua` y `bienestar` solicitados en el documento maestro. Actualmente el módulo utiliza verificaciones provisionales en controlador y vista.
