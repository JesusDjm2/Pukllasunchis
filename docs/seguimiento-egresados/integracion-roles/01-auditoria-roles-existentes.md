# PUKLLA EN RED — Auditoría Técnica de Identidades, Roles y Permisos
## Documento 01: Auditoría de la Estructura Actual y Diagnóstico Institucional

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis (Cusco, Perú)  
**Proyecto:** Puklla en Red — Sistema de Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados  
**Repositorio:** `JesusDjm2/Pukllasunchis`  
**Rama:** `sistemadeseguimientodeegresados`  
**Entorno:** Laravel 10.50.2, PHP 8.2, MySQL, Spatie Laravel-Permission, Blade, Bootstrap 4 / SB Admin 2.

---

### 1. OBJETIVO DE LA AUDITORÍA

Examinar la arquitectura real de autenticación, autorización basada en roles (RBAC), modelos de datos, rutas, controladores y vistas del sistema institucional de la EESP Pukllasunchis, a fin de integrar el nuevo rol institucional `seguimiento-egresados` sin duplicar tablas, sin romper la compatibilidad con el dashboard docente ni alterar las cuentas administrativas existentes.

---

### 2. INSPECCIÓN DE LA BASE DE DATOS Y TABLAS SPATIE

Se realizó una inspección directa sobre el esquema relacional de la base de datos `puklla`:

```sql
SHOW TABLES LIKE '%role%';
SHOW TABLES LIKE '%permission%';
```

#### 2.1. Tablas confirmadas en el esquema:

1. **`users`**: Tabla principal de identidades institucionales (ID, `name`, `apellidos`, `dni`, `email`, `password`, `condicion`, `perfil`, `pendiente`, `telefono`, `programa_id`, `ciclo_id`).
2. **`roles`**: Almacena los roles institucionales con soporte multi-guard (`id`, `name`, `guard_name`, `created_at`, `updated_at`). Guard activo verificado: `web`.
3. **`permissions`**: Tabla nativa de Spatie para permisos individuales. Previo a esta intervención se encontraba vacía (0 registros), ya que el sistema dependía fundamentalmente de agrupaciones de rol (`hasRole` / `hasAnyRole`).
4. **`model_has_roles`**: Tabla pivote de asignación de roles a modelos (`role_id`, `model_type = App\Models\User`, `model_id`). Contiene más de 860 asignaciones históricas activas.
5. **`model_has_permissions`**: Tabla pivote para permisos directos a usuarios (disponible).
6. **`role_has_permissions`**: Tabla pivote que vincula roles institucionales con permisos granulares.

#### 2.2. Inventario de Roles Existentes en la Intranet (Guard `web`):

| ID | Nombre de Rol | Finalidad Institucional | Destino / Dashboard |
|:---:|:---|:---|:---|
| 1 | `admin` | Administración general de la intranet y registros académicos | `/Administrador` (`admin`) |
| 8 | `super-admin` | Control privilegiado de seguridad, configuración y roles | `/Administrador` (`admin`) |
| 2 | `docente` | Cuerpo docente institucional, calificación y gestión de cursos | `Dashboard-Docente/{docente}` (`vistaDocente`) |
| 7 | `tutor` | Acompañamiento tutorial de ciclos y registro de incidencias | `/tutor/dashboard` (`tutor.dashboard`) |
| 9 | `investigador` | Seguimiento de proyectos de investigación formativa | `/taqe/investigador` |
| 10 | `gestor-titulacion` | Coordinación de expedientes y grados profesionales | `/titulacion` |
| 3 | `alumno` | Estudiantes regulares del programa de Formación Inicial Docente (FID) | `/alumnos` |
| 5 | `alumnoB` | Estudiantes del Programa de Profesionalización Docente (PPD) | `/ppd` |
| 4 | `adminB` | Administrador de Bolsa de Trabajo institucional | `/bolsa-trabajo` |
| 6 | `inhabilitado` | Estudiantes con suspensión temporal o administrativa | `/inhabilitado` |
| 52 | `egresado` | Graduados y egresados de la institución | `/egresado` |
| 53 | `encuestador-seguimiento` | Entrevistador de campo temporal para campañas | `/seguimiento-egresados` |
| 54 | `coordinacion-empleabilidad` | Coordinación institucional de seguimiento y egresados | `/seguimiento-egresados` |
| 55 | `bienestar` | Atención de bienestar social y salud estudiantil | Gestión de casos |
| 56 | `formacion-continua` | Programas de especialización y extensión | Gestión académica |

> **Hallazgo Clave:** No existía en la tabla `roles` el rol complementario `seguimiento-egresados`, lo que provocaba que para asignar funciones de seguimiento a un docente se dependiera de roles ad-hoc o asignaciones externas sin permisos desacoplados.

---

### 3. AUDITORÍA DEL PANEL ADMINISTRATIVO DE USUARIOS

#### 3.1. Rutas y Controladores:
- **Ruta de listado:** `GET /Administrador` (`admin`), mapeada a `AdminController@index`.
- **Ruta de edición:** `GET /admin/edit/{id}` (`adminEdit`), mapeada a `AdminController@edit`.
- **Ruta de actualización:** `PUT /admin/update/{id}` (`adminUpdate`), mapeada a `AdminController@update`.
- **Middleware:** `middleware('role:admin|super-admin')`.

#### 3.2. Mecanismo de Selección y Validación de Roles:
1. En `resources/views/admin/usuarios/_form_roles.blade.php`, los roles se despliegan en interfaz de "píldoras" (`role-pill`) con casillas de verificación múltiple (`name="roles[]"`).
2. En `app/Http/Controllers/AdminController.php` (métodos `store` y `update`), existía una regla de validación rígida:
   ```php
   'roles.*' => 'string|in:super-admin,admin,docente,alumno,adminB,alumnoB,inhabilitado,tutor,investigador,gestor-titulacion',
   ```
   Cualquier rol no listado en esa regla era rechazado por la validación HTTP de Laravel, impidiendo guardar roles adicionales desde el panel institucional.

---

### 4. AUDITORÍA DEL DASHBOARD DOCENTE

#### 4.1. Construcción y Rutas:
- **Ruta principal:** `GET Dashboard-Docente/{docente}` (`vistaDocente`), controlada por `DocenteCOntroller@vistaDocente`.
- **Vista principal:** `resources/views/docentes/show.blade.php`, que extiende el layout `resources/views/layouts/docente.blade.php`.
- **Lógica de redirección:** Al autenticarse un docente, `Controller@redirectToDashboard` evalúa:
  ```php
  elseif ($user->hasRole('docente')) {
      return redirect()->route('vistaDocente', ['docente' => $user->docente->id]);
  }
  ```

#### 4.2. Menú Lateral ("Mis Funciones"):
- En `resources/views/layouts/docente.blade.php` (líneas 320–460), el sidebar evalúa los roles complementarios que posee el usuario autenticado para desplegar accesos adicionales:
  - Si tiene `tutor` → despliega "Tutoría".
  - Si tiene `investigador` → despliega "Taq'e — Seguimiento".
  - Si tiene `gestor-titulacion` → despliega "Proceso de titulación".
  - Si tiene `adminB` → despliega "Bolsa de trabajo".
- **Brecha encontrada:** El enlace a seguimiento de egresados solo comprobaba si la ruta existía en el archivo de rutas (`Route::has('seguimiento-egresados.index')`), mostrándose u ocultándose sin consultar si el docente tenía asignado el rol o permiso correspondiente.

---

### 5. COMPONENTES YA DESARROLLADOS EN LA RAMA `sistemadeseguimientodeegresados`

La rama contiene implementados y probados:
1. **Fase 1B:** Persistencia longitudinal (`Actualizacion`, `EpisodioLaboral`), camino rápido "Sin cambios", prevención de despidos ficticios y soporte de empleos simultáneos.
2. **Fase 1C:** Seguridad perimetral, tokens criptográficos WhatsApp (`InvitacionCampana` con hash SHA-256), cuarentena anti-suplantación en formularios públicos.
3. **Fase 1D:** Autorización granular en servidor (`SeguimientoAutorizacionService`), control pesimista de transacciones (`lockForUpdate`), protección de casos de bienestar (`Caso`).
4. **Fase 2A:** Laboratorio de Inteligencia Institucional (`SeguimientoLaboratorioController`, `SeguimientoAnaliticaService`), matrices cruzadas sobre personas únicas (`users.id`), Observatorio Territorial (`ObservatorioTerritorialService`), y privacidad diferencial con supresión primaria/secundaria de celdas menores a 5 (`EstadisticaSeguraService`).

---

### 6. DIAGNÓSTICO: QUÉ EXISTE, QUÉ SE REUTILIZA Y QUÉ SE AMPLÍA

| Componente | Estado Actual | Estrategia de Intervención |
|:---|:---|:---|
| **Sistema RBAC Spatie** | Existe y opera en `users` | **Reutilizar:** Usar guard `web`, crear rol `seguimiento-egresados` y los 10 permisos funcionales. |
| **Panel Admin (`adminUpdate`)** | Existe para `admin` y `super-admin` | **Ampliar:** Incorporar `seguimiento-egresados` a `in:...` y registrar trazabilidad en `egresado_auditoria_roles`. |
| **Píldoras de Roles (`_form_roles`)** | Existe con diseño modular | **Ampliar:** Añadir píldora con color institucional granate (`.rp-seguimiento`). |
| **Dashboard Docente (`layouts/docente`)** | Existe con sección "Mis funciones" | **Ampliar:** Condicionar visualización estrictamente a `hasRole('seguimiento-egresados')` o permiso `ver-dashboard`. |
| **Vista Principal Docente (`docentes/show`)** | Muestra cursos y proyectos | **Ampliar:** Añadir tarjeta de acceso directo a "Seguimiento de Egresados — Puklla en Red". |
| **Auditoría de Roles** | Inexistente para cambios de roles | **Crear:** Tabla `egresado_auditoria_roles` y modelo `AuditoriaRolSeguimiento`. |
| **Alta Segura de Super-Admin** | Dependía de seeders o SQL directo | **Crear:** Comando interactivo CLI `puklla:superadmin-crear` con entrada segura (`secret()`) sin credenciales en código. |
