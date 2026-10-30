# PUKLLA EN RED — FASE 1C
## Reporte de Ejecución de Pruebas Automatizadas (PHPUnit)

**Fecha:** 30 de Octubre de 2026  
**Rama:** `sistemadeseguimientodeegresados`  
**Entorno:** PHPUnit 10.5.45 sobre PHP 8.2.28 (cli) en macOS Darwin  
**Base de Datos:** MySQL 8.0 (InnoDB)  
**Total de Pruebas Ejecutadas:** 20 pruebas  
**Total de Aserciones:** 114 aserciones  
**Tasa de Aprobación:** 100% (20 aprobadas, 0 fallidas, 0 omitidas)  
**Tiempo Total de Ejecución:** 1.18 segundos  

---

### Comando Oficial de Ejecución

```bash
cd /Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis
export PATH="$HOME/.local/bin:$HOME/.local/mysql/bin:$PATH"
php artisan test --filter=SeguimientoEgresados
```

---

### Resumen de la Ejecución

```
   PASS  Tests\Feature\SeguimientoEgresadosFase1BTest
  ✓ 01 primer episodio laboral                                           0.33s  
  ✓ 02 cambio de empleo con conservacion de historial                    0.05s  
  ✓ 03 finalizacion de empleo sin crear empleador ficticio               0.05s  
  ✓ 04 confirmacion sin cambios no duplica episodios                     0.03s  
  ✓ 05 empleos simultaneos                                               0.04s  
  ✓ 06 fecha de finalizacion desconocida                                 0.04s  
  ✓ 07 reenvio identico idempotente no duplica episodios                 0.04s  
  ✓ 08 rechazo de identificador de usuario invalido                      0.03s  
  ✓ 09 rechazo acceso egresado a otra ficha                              0.03s  
  ✓ 10 rechazo http 403 encuestador caso no asignado                     0.04s  
  ✓ 11 rollback ante error transaccional                                 0.03s  
  ✓ 12 consistencia entre episodios y estado laboral                     0.04s  

   PASS  Tests\Feature\SeguimientoEgresadosFase1CTest
  ✓ 01 rutas intranet requieren autenticacion                            0.06s  
  ✓ 02 egresado no puede consultar ni cerrar episodios de otros          0.04s  
  ✓ 03 encuestador no asignado recibe 403                                0.04s  
  ✓ 04 egresados y encuestadores no pueden gestionar grupos ni avanzar…  0.04s  
  ✓ 05 intento publico sin token no adultera ficha verificada            0.04s  
  ✓ 06 invitacion criptografica whatsapp actualiza con verificacion      0.05s  
  ✓ 07 preservacion multicampana sin sobrescritura                       0.05s  
  ✓ 08 no inferencia de fechas now ni atributos ausentes                 0.04s  

  Tests:    20 passed (114 assertions)
  Duration: 1.18s
```

---

### Detalle de Pruebas de la Suite Fase 1C (`SeguimientoEgresadosFase1CTest`)

#### 1. `test_01_rutas_intranet_requieren_autenticacion`
- **Objetivo:** Verificar que todas las rutas sensibles de intranet rechacen peticiones anónimas sin sesión activa.
- **Rutas evaluadas:** `/episodios/{userId}`, `/episodios/{id}/cerrar`, `/accion`, `/grupos`, `/llamada`, `/enlace-whatsapp`.
- **Resultado:** HTTP 401 Unauthorized en todas las rutas probadas.
- **Aserciones:** 6 aserciones.

#### 2. `test_02_egresado_no_puede_consultar_ni_cerrar_episodios_de_otros`
- **Objetivo:** Validar controles estrictos Anti-IDOR para perfiles de egresados/alumnos.
- **Comportamiento:**
  - El egresado A intenta consultar los episodios laborales del egresado B -> HTTP 403 Forbidden.
  - El egresado A intenta cerrar un episodio laboral perteneciente al egresado B -> HTTP 403 Forbidden.
  - El egresado A consulta sus propios episodios -> HTTP 200 OK.
- **Resultado:** APROBADO (6 aserciones).

#### 3. `test_03_encuestador_no_asignado_recibe_403`
- **Objetivo:** Validar que un encuestador temporal solo acceda a egresados asignados en sus grupos activos.
- **Comportamiento:**
  - Encuestador intenta consultar episodios de un egresado no asignado -> HTTP 403 Forbidden.
  - Encuestador intenta registrar llamadas sobre un egresado no asignado -> HTTP 403 Forbidden.
- **Resultado:** APROBADO (2 aserciones).

#### 4. `test_04_egresados_y_encuestadores_no_pueden_gestionar_grupos_ni_avanzar_casos`
- **Objetivo:** Segregación de funciones directivas y de coordinación.
- **Comportamiento:**
  - Egresado intenta crear un grupo de entrevistadores -> HTTP 403 Forbidden.
  - Encuestador intenta avanzar el estado de un caso de acompañamiento institucional -> HTTP 403 Forbidden.
  - Coordinador de empleabilidad ejecuta la misma acción -> HTTP 200 OK y estado avanza a 'En atención'.
- **Resultado:** APROBADO (4 aserciones).

#### 5. `test_05_intento_publico_sin_token_no_adultera_ficha_verificada`
- **Objetivo:** Validar el blindaje anti-suplantación en accesos públicos abiertos por DNI.
- **Comportamiento:**
  - Un usuario anónimo envía el formulario público usando el DNI de un egresado real con empleo activo, solicitando marcarlo como desempleado y cerrar activos.
  - El sistema aísla el registro en `pendiente_verificacion` con procedencia `publico_whatsapp_no_verificado`.
  - El empleo activo original del egresado permanece `es_actual = true` e intacto.
  - Cero actualizaciones vinculadas al `user_id` del egresado.
  - Se genera automáticamente un caso institucional `VERIF-*` para validación telefónica por coordinación.
- **Resultado:** APROBADO (6 aserciones).

#### 6. `test_06_invitacion_criptografica_whatsapp_actualiza_con_verificacion`
- **Objetivo:** Comprobar el ciclo de vida seguro de invitaciones para WhatsApp sin PII.
- **Comportamiento:**
  - Se genera una invitación criptográfica para un egresado (`INV-` + 48 caracteres).
  - Se comprueba que la URL generada no contiene el DNI ni el ID del egresado.
  - El egresado abre el link sin sesión y confirma sus datos con el token.
  - La actualización se consolida como `estado_verificacion = 'verificado'` y `procedencia = 'publico_whatsapp_invitacion'`.
  - El token queda marcado como usado (`usado = true`, fecha e IP registrada).
  - Un intento posterior de reutilizar el mismo token ya consumido es rechazado con HTTP 422.
- **Resultado:** APROBADO (9 aserciones).

#### 7. `test_07_preservacion_multicampana_sin_sobrescritura`
- **Objetivo:** Comprobar que las respuestas sucesivas en semestres distintos coexisten en la base de datos sin destruirse.
- **Comportamiento:**
  - Egresado responde en la campaña 2025-II (se almacena snapshot 2025-II).
  - Egresado confirma en la campaña 2026-I (se almacena snapshot 2026-I).
  - Egresado actualiza en la campaña 2026-II (se almacena snapshot 2026-II).
  - Se comprueban 3 registros históricos independientes para el egresado.
  - Un reenvío en la misma campaña (2026-II) actualiza la fila correspondiente sin crear duplicados (count = 3).
- **Resultado:** APROBADO (6 aserciones).

#### 8. `test_08_no_inferencia_de_fechas_now_ni_atributos_ausentes`
- **Objetivo:** Integridad epistémica del dato laboral.
- **Comportamiento:**
  - Se registra un empleo con fecha de inicio omitida/nula y rubro de consultoría no educativa.
  - `fecha_inicio` se persiste estrictamente como `NULL`, demostrando la eliminación de la fecha `now()` inventada.
  - `es_docencia_directa` y `es_empleo_educativo` se persisten como `false` (0).
  - `contrato` se mantiene `NULL`. Cero inferencias infundadas.
- **Resultado:** APROBADO (6 aserciones).

---

### Conclusión de Pruebas

Las 20 pruebas de integración y seguridad demuestran de forma fehaciente que los requisitos R01, R02 y R03, junto con las enmiendas de seguridad de la Fase 1C, operan con total solidez y sin regresiones en la intranet institucional.
