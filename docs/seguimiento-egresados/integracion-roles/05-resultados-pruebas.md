# PUKLLA EN RED — Reporte de Pruebas Automatizadas y Validación Integral
## Documento 05: Resultados de Pruebas Automatizadas de Integración, Roles y Seguridad

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis (Cusco, Perú)  
**Proyecto:** Puklla en Red — Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados  
**Repositorio:** `JesusDjm2/Pukllasunchis`  
**Rama:** `sistemadeseguimientodeegresados`  
**Fecha de ejecución:** 31 de Octubre de 2026  
**Framework de Pruebas:** PHPUnit 10.5.45 sobre Laravel 10.50.2  

---

### 1. RESUMEN EJECUTIVO DE EJECUCIÓN

Se ejecutó la totalidad de las suites de prueba de la rama `sistemadeseguimientodeegresados`, abarcando desde las pruebas de persistencia longitudinal (Fase 1B), seguridad multicampaña y tokens criptográficos (Fase 1C), integración de roles y dashboard docente (Fase Actual), hasta analítica, privacidad diferencial y Observatorio Territorial (Fase 2A).

**Resultado General:**
- **Suites Ejecutadas:** 4
- **Total de Pruebas:** 43 pruebas unitarias y de integración
- **Total de Aserciones:** 225 aserciones aprobadas
- **Pruebas Fallidas:** 0 (0%)
- **Tasa de Aprobación:** 100%
- **Tiempo Total de Ejecución:** 3.88 segundos

---

### 2. RESULTADOS DETALLADOS POR SUITE DE PRUEBA

#### 2.1. Suite de Integración de Roles y Permisos (`SeguimientoIntegracionRolesTest`)
Archivo: `tests/Feature/SeguimientoIntegracionRolesTest.php`  
Estado: **13 aprobadas de 13 (45 aserciones)**

| # | Prueba Automatizada | Aspecto Verificado de la Directiva | Resultado | Tiempo |
|:---:|:---|:---|:---:|:---:|
| 1 | `test_01_super_admin_accede_al_panel_administrativo` | Super-admin ingresa al panel `/Administrador` (HTTP 200) | **APROBADO** | 0.91s |
| 2 | `test_02_usuario_sin_permiso_no_puede_asignar_roles` | Docente o alumno recibe HTTP 403 al intentar mutar roles | **APROBADO** | 0.05s |
| 3 | `test_03_rol_seguimiento_egresados_es_idempotente` | Ejecución repetida de creación no duplica rol ni permisos | **APROBADO** | 0.03s |
| 4 | `test_04_docente_conserva_rol_docente_al_recibir_seguimiento_egresados` | Docente conserva `docente` intacto al recibir `seguimiento-egresados` | **APROBADO** | 0.13s |
| 5 | `test_05_asignacion_genera_trazabilidad_en_base_de_datos` | Persistencia inmutable en tabla `egresado_auditoria_roles` | **APROBADO** | 0.07s |
| 6 | `test_06_docente_autorizado_ve_puklla_en_red_en_su_dashboard` | Visualización en menú lateral y tarjeta en `vistaDocente` | **APROBADO** | 0.05s |
| 7 | `test_07_docente_sin_rol_no_ve_opcion_en_dashboard` | Docente no asignado no ve la entrada en su interfaz | **APROBADO** | 0.05s |
| 8 | `test_08_escribir_url_directa_sin_autorizacion_es_rechazada` | Peticiones a URLs directas son rechazadas en servidor (HTTP 403) | **APROBADO** | 0.04s |
| 9 | `test_09_revocar_rol_elimina_acceso_y_visibilidad` | Revocación retira inmediatamente accesos y menús | **APROBADO** | 0.07s |
| 10 | `test_10_revocacion_no_afecta_otras_funciones_docentes` | Cursos, notas y modelo `Docente` intactos tras revocación | **APROBADO** | 0.05s |
| 11 | `test_11_entrevistadores_no_pueden_consultar_egresados_no_asignados` | HTTP 403 al consultar expediente fuera de sus grupos activos | **APROBADO** | 0.05s |
| 12 | `test_12_separacion_estricta_de_permisos_operativos_y_analitica` | Rol operativo no accede a analítica ni a bienestar confidencial | **APROBADO** | 0.21s |
| 13 | `test_13_flujo_completo_extremo_a_extremo` | Ciclo completo: login superadmin → asigna → docente entra → revoca | **APROBADO** | 0.12s |

---

#### 2.2. Suite de Persistencia Longitudinal — Fase 1B (`SeguimientoEgresadosFase1BTest`)
Archivo: `tests/Feature/SeguimientoEgresadosFase1BTest.php`  
Estado: **12 aprobadas de 12 (72 aserciones)**

- `01 primer episodio laboral`: Creación del primer historial de empleo.
- `02 cambio de empleo con conservacion de historial`: Cierre de empleo anterior y nuevo episodio sin pérdida histórica.
- `03 finalizacion de empleo sin crear empleador ficticio`: Registro de desempleo sin entidades ficticias.
- `04 confirmacion sin cambios no duplica episodios`: Microchequeo rápido idempotente (R02).
- `05 empleos simultaneos`: Soporte de doble contratación activa.
- `06 fecha de finalizacion desconocida`: Manejo de cierres con fecha nula explícita.
- `07 reenvio identico idempotente no duplica episodios`: Protección anti-duplicidad por reenvío de formulario.
- `08 rechazo de identificador de usuario invalido`: Integridad referencial con `users.id` (R01).
- `09 rechazo acceso egresado a otra ficha`: Aislamiento estricto de egresados (R01/Anti-IDOR).
- `10 rechazo http 403 encuestador caso no asignado`: Protección de expedientes no asignados.
- `11 rollback ante error transaccional`: Atomicidad ACID pesimista (`lockForUpdate`).
- `12 consistencia entre episodios y estado laboral`: Sincronización entre tabla de episodios y condición laboral.

---

#### 2.3. Suite de Seguridad, Integridad y Tokens Criptográficos — Fase 1C (`SeguimientoEgresadosFase1CTest`)
Archivo: `tests/Feature/SeguimientoEgresadosFase1CTest.php`  
Estado: **8 aprobadas de 8 (42 aserciones)**

- `01 rutas intranet requieren autenticacion`: Rechazo de accesos anónimos.
- `02 egresado no puede consultar ni cerrar episodios de otros`: Prevención de elevación de privilegios horizontal.
- `03 encuestador no asignado recibe 403`: Restricción perimetral de entrevistadores.
- `04 egresados y encuestadores no pueden gestionar grupos ni avanzar casos`: Autorización directiva.
- `05 intento publico sin token no adultera ficha verificada`: Cuarentena de registros públicos sin credencial criptográfica.
- `06 invitacion criptografica whatsapp actualiza con verificacion`: Tokens SHA-256 de un solo uso con desafío de DNI.
- `07 preservacion multicampana sin sobrescritura`: Historial acumulativo semestral.
- `08 no inferencia de fechas now ni atributos ausentes`: Integridad estricta de datos reportados.

---

#### 2.4. Suite de Analítica, Observatorio y Privacidad — Fase 2A (`SeguimientoLaboratorioFase2ATest`)
Archivo: `tests/Feature/SeguimientoLaboratorioFase2ATest.php`  
Estado: **10 aprobadas de 10 (66 aserciones)**

- `01 rutas laboratorio requieren autenticacion`: Protección perimetral de endpoints analíticos.
- `02 egresados y alumnos no tienen acceso al laboratorio`: Rechazo 403 a perfiles no autorizados.
- `03 personal autorizado accede al laboratorio con exito`: Vista ejecutiva y datos longitudinales.
- `04 universos analiticos distinguen padron elegibles y no respondientes`: Rigor metodológico (R07/R10).
- `05 matriz cruzada cuenta personas unicas sin duplicar por empleos`: Conteo de `users.id` únicos.
- `06 privacidad estadistica aplica supresion primaria a celdas menores a 5`: Enmascaramiento `< 5` (R08).
- `07 privacidad estadistica aplica supresion secundaria para evitar deduccion`: Protección de márgenes.
- `08 filtros combinables en endpoint ajax`: Filtros simultáneos (programa, cohorte, docencia directa).
- `09 observatorio territorial agrupa provincias y agenda institucional`: Distribución Cusco y agenda de acción.
- `10 exportacion csv contiene metadatos y cero fuga pii`: Descarga CSV sin datos personales individuales.

---

### 3. EVIDENCIA DE EJECUCIÓN CONSOLIDAD

```
   PASS  Tests\Feature\SeguimientoEgresadosFase1BTest
  ✓ 01 primer episodio laboral                                           0.40s  
  ✓ 02 cambio de empleo con conservacion de historial                    0.05s  
  ✓ 03 finalizacion de empleo sin crear empleador ficticio               0.04s  
  ✓ 04 confirmacion sin cambios no duplica episodios                     0.04s  
  ✓ 05 empleos simultaneos                                               0.04s  
  ✓ 06 fecha de finalizacion desconocida                                 0.04s  
  ✓ 07 reenvio identico idempotente no duplica episodios                 0.04s  
  ✓ 08 rechazo de identificador de usuario invalido                      0.03s  
  ✓ 09 rechazo acceso egresado a otra ficha                              0.04s  
  ✓ 10 rechazo http 403 encuestador caso no asignado                     0.04s  
  ✓ 11 rollback ante error transaccional                                 0.03s  
  ✓ 12 consistencia entre episodios y estado laboral                     0.04s  

   PASS  Tests\Feature\SeguimientoEgresadosFase1CTest
  ✓ 01 rutas intranet requieren autenticacion                            0.06s  
  ✓ 02 egresado no puede consultar ni cerrar episodios de otros          0.05s  
  ✓ 03 encuestador no asignado recibe 403                                0.04s  
  ✓ 04 egresados y encuestadores no pueden gestionar grupos ni avanzar…  0.05s  
  ✓ 05 intento publico sin token no adultera ficha verificada            0.04s  
  ✓ 06 invitacion criptografica whatsapp actualiza con verificacion      0.05s  
  ✓ 07 preservacion multicampana sin sobrescritura                       0.06s  
  ✓ 08 no inferencia de fechas now ni atributos ausentes                 0.04s  

   PASS  Tests\Feature\SeguimientoIntegracionRolesTest
  ✓ 01 super admin accede al panel administrativo                        0.91s  
  ✓ 02 usuario sin permiso no puede asignar roles                        0.05s  
  ✓ 03 rol seguimiento egresados es idempotente                          0.03s  
  ✓ 04 docente conserva rol docente al recibir seguimiento egresados     0.13s  
  ✓ 05 asignacion genera trazabilidad en base de datos                   0.07s  
  ✓ 06 docente autorizado ve puklla en red en su dashboard               0.05s  
  ✓ 07 docente sin rol no ve opcion en dashboard                         0.05s  
  ✓ 08 escribir url directa sin autorizacion es rechazada                0.04s  
  ✓ 09 revocar rol elimina acceso y visibilidad                          0.07s  
  ✓ 10 revocacion no afecta otras funciones docentes                     0.05s  
  ✓ 11 entrevistadores no pueden consultar egresados no asignados        0.05s  
  ✓ 12 separacion estricta de permisos operativos y analitica            0.21s  
  ✓ 13 flujo completo extremo a extremo                                  0.12s  

   PASS  Tests\Feature\SeguimientoLaboratorioFase2ATest
  ✓ 01 rutas laboratorio requieren autenticacion                         0.04s  
  ✓ 02 egresados y alumnos no tienen acceso al laboratorio               0.04s  
  ✓ 03 personal autorizado accede al laboratorio con exito               0.18s  
  ✓ 04 universos analiticos distinguen padron elegibles y no respondien… 0.03s  
  ✓ 05 matriz cruzada cuenta personas unicas sin duplicar por empleos    0.07s  
  ✓ 06 privacidad estadistica aplica supresion primaria a celdas menore… 0.03s  
  ✓ 07 privacidad estadistica aplica supresion secundaria para evitar d… 0.03s  
  ✓ 08 filtros combinables en endpoint ajax                              0.11s  
  ✓ 09 observatorio territorial agrupa provincias y agenda instituciona… 0.08s  
  ✓ 10 exportacion csv contiene metadatos y cero fuga pii                0.11s  

  Tests:    43 passed (225 assertions)
  Duration: 3.88s
```
