# PUKLLA EN RED — Arquitectura de Roles y Permisos Granulares
## Documento 02: Diseño del Rol `seguimiento-egresados` y Política de Mínimo Privilegio

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis (Cusco, Perú)  
**Proyecto:** Puklla en Red — Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados  
**Repositorio:** `JesusDjm2/Pukllasunchis`  
**Rama:** `sistemadeseguimientodeegresados`  

---

### 1. PRINCIPIO DE DISEÑO Y COMPATIBILIDAD COEXISTENTE

El rol `seguimiento-egresados` ha sido concebido bajo la arquitectura de Spatie Laravel-Permission con las siguientes premisas obligatorias:

1. **Guard Nativo:** Registrado exclusivamente bajo el guard `web`.
2. **Carácter Complementario y No Destructivo:** Coexiste de manera armónica con el rol `docente` (o roles administrativos/tutoriales). Cuando un usuario recibe `seguimiento-egresados`, conserva intacta su condición docente, sus cursos asignados, sus calificaciones y su carga horaria.
3. **Mínimo Privilegio Inicial:** El rol no otorga permisos indiscriminados a toda la base de datos institucional. Separa estrictamente la labor de entrevista y contacto de campo, de las funciones directivas de configuración, analítica estratégica y acceso a expedientes confidenciales.

---

### 2. MATRIZ DE PERMISOS FUNCIONALES GRANULARES

Se definieron e implementaron 10 permisos granulares registrados en la tabla `permissions`:

| # | Permiso Granular | Alcance Funcional | Rol Base `seguimiento-egresados` | Coordinación / Admin |
|:---:|:---|:---|:---:|:---:|
| 1 | `seguimiento-egresados.ver-dashboard` | Acceso a la entrada del módulo en la intranet y menú docente | **Asignado** | **Asignado** |
| 2 | `seguimiento-egresados.ver-perfiles-autorizados` | Consulta de fichas de egresados **únicamente asignados** en grupos activos | **Asignado** | **Asignado** |
| 3 | `seguimiento-egresados.actualizar-perfiles-autorizados` | Modificación de situación laboral y episodios de egresados asignados | **Asignado** | **Asignado** |
| 4 | `seguimiento-egresados.registrar-entrevistas` | Registro de llamadas telefónicas y bitácora de contacto en el sistema | **Asignado** | **Asignado** |
| 5 | `seguimiento-egresados.ver-contactos-autorizados` | Visualización auditada de teléfonos y correos de egresados asignados | **Asignado** | **Asignado** |
| 6 | `seguimiento-egresados.gestionar-campanas` | Creación, apertura y cierre de periodos de campaña institucional | *Denegado* | **Asignado** |
| 7 | `seguimiento-egresados.gestionar-grupos` | Creación de grupos y asignación de egresados a entrevistadores | *Denegado* | **Asignado** |
| 8 | `seguimiento-egresados.gestionar-casos` | Creación de casos de acompañamiento y derivación institucional | *Denegado* | **Asignado** |
| 9 | `seguimiento-egresados.ver-analitica` | Acceso al Laboratorio de Inteligencia y Observatorio Territorial | *Denegado* | **Asignado** |
| 10 | `seguimiento-egresados.exportar-analitica` | Descarga de matrices cruzadas en formato CSV con secreto estadístico | *Denegado* | **Asignado** |

---

### 3. SEPARACIÓN DE RESPONSABILIDADES Y BARRERAS DE SEGURIDAD

#### 3.1. Restricción de Expedientes No Asignados:
Un docente que cuenta con el rol `seguimiento-egresados` **no tiene permiso para ver el padrón general ni fichas de egresados ajenos**. El controlador `SeguimientoEgresadoController` y el servicio `SeguimientoAutorizacionService` aplican la verificación:

```php
if ($this->esDocenteOEntrevistador($user)) {
    return GrupoEntrevistador::esEntrevistadorAsignado($user->id, $targetUserId);
}
```
Si el docente intenta forzar la URL `GET /seguimiento-egresados/episodios/{userId}` para un egresado que no tiene asignado en un grupo activo, el servidor responde inmediatamente con **HTTP 403 Forbidden**.

#### 3.2. Protección de Notas Confidenciales de Bienestar:
Las solicitudes de apoyo psicosocial o casos marcados como confidenciales en la tabla `egresado_casos` (`es_confidencial_bienestar = true`) están protegidos:
- Solo personal de Bienestar Social (`hasRole('bienestar')`) o Coordinación/Dirección pueden ver el propósito y detalle confidencial.
- Un docente o entrevistador de seguimiento solo ve la etiqueta general `Caso bajo atención de Bienestar Institucional`, impidiendo la exposición de datos personales sensibles.

#### 3.3. Acceso a Analítica Estratégica:
El Laboratorio de Inteligencia Institucional (`GET /seguimiento-egresados/laboratorio`) requiere explícitamente el permiso `seguimiento-egresados.ver-analitica` o un rol de gestión académica/investigación. El rol operativo no puede consultar agregaciones demográficas ni exportar datos salvo que se le otorgue el permiso individualmente.

---

### 4. IMPLEMENTACIÓN DE LA MIGRACIÓN IDEMPOTENTE

La migración `database/migrations/2026_10_31_100000_create_seguimiento_egresados_role_and_permissions.php` fue diseñada con garantías de idempotencia absoluta:
1. Utiliza `Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web'])`.
2. Utiliza `Role::firstOrCreate(['name' => 'seguimiento-egresados', 'guard_name' => 'web'])`.
3. Sincroniza permisos mediante `$rolSeguimiento->syncPermissions($permisosOperativos)`, evitando duplicados en ejecuciones repetidas.
4. Vincula permisos directivos a `super-admin`, `admin` y `coordinacion-empleabilidad`.
5. Limpia la caché de Spatie (`app()[PermissionRegistrar::class]->forgetCachedPermissions()`) antes y después de la operación.

---

### 5. MODELO DE TRAZABILIDAD Y AUDITORÍA: `egresado_auditoria_roles`

Para responder al requerimiento de auditoría institucional, se creó la tabla relacional `egresado_auditoria_roles` gestionada por el modelo Eloquent `App\Models\SeguimientoEgresado\AuditoriaRolSeguimiento`:

```
+--------------------+---------------------+------+-----+---------+----------------+
| Field              | Type                | Null | Key | Default | Extra          |
+--------------------+---------------------+------+-----+---------+----------------+
| id                 | bigint unsigned     | NO   | PRI | NULL    | auto_increment |
| user_id            | bigint unsigned     | NO   | MUL | NULL    | Usuario        |
| admin_id           | bigint unsigned     | YES  | MUL | NULL    | Administrador  |
| rol                | varchar(50)         | NO   |     | NULL    | Rol modificado |
| accion             | varchar(20)         | NO   |     | NULL    | asignado/revoc |
| motivo             | varchar(255)        | YES  |     | NULL    | Justificación  |
| ip                 | varchar(45)         | YES  |     | NULL    | Dirección IP   |
| roles_anteriores   | json                | YES  |     | NULL    | Snapshot antes |
| roles_posteriores  | json                | YES  |     | NULL    | Snapshot post  |
| created_at         | timestamp           | YES  |     | NULL    |                |
| updated_at         | timestamp           | YES  |     | NULL    |                |
+--------------------+---------------------+------+-----+---------+----------------+
```

Cada vez que desde el panel administrativo se añade o retira el rol `seguimiento-egresados`, se crea un registro inmutable con el ID del administrador actuante, la IP de conexión y el histórico exacto de roles.
