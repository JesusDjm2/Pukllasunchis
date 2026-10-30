# PUKLLA EN RED — Sistema de Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados
## 06. Reporte Oficial de Pruebas Automatizadas y Verificación Funcional — Fase 1B

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis  
**Proyecto:** Puklla en Red — Intranet Institucional  
**Fecha de Ejecución:** 30 de Octubre de 2026, 11:34:53 -05:00  
**Entorno de Pruebas:** Local Ensayo (PHP 8.2.32, PHPUnit 10.5.45, MySQL 26.10, Laravel 10.50.2)  
**Rama:** `sistemadeseguimientodeegresados`  
**Commit:** `59e57e5d6246d9e105b6203232e934dff6a841c2`  

---

### 1. Resumen Consolidado de Ejecución

| Métrica | Suite Específica Fase 1B | Suite General del Proyecto |
|---|---|---|
| **Pruebas Totales** | 12 | 19 |
| **Pruebas Aprobadas (Passed)** | 12 (100%) | 19 (100%) |
| **Pruebas Fallidas (Failed)** | 0 (0%) | 0 (0%) |
| **Pruebas Omitidas (Skipped / Incomplete)** | 0 (0%) | 0 (0%) |
| **Aserciones Verificadas** | 57 | 69 |
| **Tiempo de Ejecución** | 0.93 segundos | 1.19 segundos |
| **Consumo de Memoria** | ~32 MB | ~36 MB |
| **Estado Final** | **APROBADO SIN RESERVAS** | **APROBADO SIN RESERVAS** |

---

### 2. Comandos Ejecutados

#### Comando 1: Ejecución de la Suite de Fase 1B
```bash
php artisan test --filter=SeguimientoEgresadosFase1BTest
```

#### Salida Real del Comando 1:
```text
   PASS  Tests\Feature\SeguimientoEgresadosFase1BTest
  ✓ 01 primer episodio laboral                                           0.10s  
  ✓ 02 cambio de empleo con conservacion de historial                    0.04s  
  ✓ 03 finalizacion de empleo sin crear empleador ficticio               0.03s  
  ✓ 04 confirmacion sin cambios no duplica episodios                     0.04s  
  ✓ 05 empleos simultaneos                                               0.04s  
  ✓ 06 fecha de finalizacion desconocida                                 0.03s  
  ✓ 07 reenvio identico idempotente no duplica episodios                 0.04s  
  ✓ 08 rechazo de identificador de usuario invalido                      0.03s  
  ✓ 09 rechazo acceso egresado a otra ficha                              0.03s  
  ✓ 10 rechazo http 403 encuestador caso no asignado                     0.04s  
  ✓ 11 rollback ante error transaccional                                 0.03s  
  ✓ 12 consistencia entre episodios y estado laboral                     0.04s  

  Tests:    12 passed (57 assertions)
  Duration: 0.93s
```

#### Comando 2: Verificación de Regresión Global del Proyecto
```bash
php artisan test
```

#### Salida Real del Comando 2:
```text
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true                                                    0.01s  

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response                        0.41s  

   PASS  Tests\Feature\PukllaBotTest
  ✓ pukllabot chat route exists                                          0.03s  
  ✓ chat validates message required                                      0.05s  
  ✓ chat returns 503 when openrouter key empty                           0.03s  
  ✓ pukllabot partial renders                                            0.02s  
  ✓ chat rejects invalid scope                                           0.02s  

   PASS  Tests\Feature\SeguimientoEgresadosFase1BTest
  ✓ 01 primer episodio laboral                                           0.10s  
  ✓ 02 cambio de empleo con conservacion de historial                    0.04s  
  ✓ 03 finalizacion de empleo sin crear empleador ficticio               0.03s  
  ✓ 04 confirmacion sin cambios no duplica episodios                     0.04s  
  ✓ 05 empleos simultaneos                                               0.04s  
  ✓ 06 fecha de finalizacion desconocida                                 0.03s  
  ✓ 07 reenvio identico idempotente no duplica episodios                 0.04s  
  ✓ 08 rechazo de identificador de usuario invalido                      0.03s  
  ✓ 09 rechazo acceso egresado a otra ficha                              0.03s  
  ✓ 10 rechazo http 403 encuestador caso no asignado                     0.04s  
  ✓ 11 rollback ante error transaccional                                 0.03s  
  ✓ 12 consistencia entre episodios y estado laboral                     0.04s  

  Tests:    19 passed (69 assertions)
  Duration: 1.19s
```

---

### 3. Desglose Detallado de Pruebas de Fase 1B

#### Prueba 1: `test_01_primer_episodio_laboral`
- **Objetivo:** Verificar que un egresado que registra su primer trabajo genera un registro en `egresado_episodios_laborales` con `es_actual = true`, tipología pedagógica y sin fecha de finalización.
- **Aserciones Ejecutadas:**
  - `assertStatus(200)`
  - `assertJson(['success' => true, 'tipo' => 'actualizacion_completa'])`
  - `assertEquals(1, EpisodioLaboral::where('user_id', $egresado->id)->count())`
  - `assertEquals('I.E. 50012 San Jerónimo', $episodio->institucion)`
  - `assertTrue($episodio->es_actual)`
  - `assertTrue($episodio->es_empleo_educativo)`
  - `assertTrue($episodio->es_docencia_directa)`
  - `assertNull($episodio->fecha_fin)`
- **Resultado:** APROBADA (0.10s).

#### Prueba 2: `test_02_cambio_de_empleo_con_conservacion_de_historial`
- **Objetivo:** Verificar que al reportar una transición de empleo cerrando el anterior, se conserva el historial con su `fecha_fin` efectiva y se crea el nuevo episodio activo.
- **Aserciones Ejecutadas:**
  - `assertStatus(200)`
  - `assertEquals(2, EpisodioLaboral::where('user_id', $egresado->id)->count())`
  - `assertFalse((bool) $ep1->es_actual)`
  - `assertEquals('2024-12-31', $ep1->fecha_fin->format('Y-m-d'))`
  - `assertEquals('Promoción a docencia de aula', $ep1->motivo_cambio)`
  - `assertNotNull($ep2)`
  - `assertTrue((bool) $ep2->es_actual)`
  - `assertEquals('2025-03-01', $ep2->fecha_inicio->format('Y-m-d'))`
- **Resultado:** APROBADA (0.04s).

#### Prueba 3: `test_03_finalizacion_de_empleo_sin_crear_empleador_ficticio`
- **Objetivo:** Certificar que al finalizar un empleo y reportar `trabaja = 'No trabaja'`, el episodio activo se cierra con su fecha final y **no se crea ninguna fila inventada con `institucion = 'Desempleado'`**.
- **Aserciones Ejecutadas:**
  - `assertStatus(200)`
  - `assertFalse((bool) $ep->es_actual)`
  - `assertEquals('2025-11-30', $ep->fecha_fin->format('Y-m-d'))`
  - `assertEquals(1, EpisodioLaboral::where('user_id', $egresado->id)->count())`
  - `assertDatabaseMissing('egresado_episodios_laborales', ['institucion' => 'Desempleado'])`
- **Resultado:** APROBADA (0.03s).

#### Prueba 4: `test_04_confirmacion_sin_cambios_no_duplica_episodios`
- **Objetivo:** Certificar que la confirmación rápida «Sin cambios» (R02) actualiza la verificación semestral en `egresado_actualizaciones` sin insertar filas duplicadas en `egresado_episodios_laborales` y manteniendo la fecha de inicio intacta.
- **Aserciones Ejecutadas:**
  - `assertStatus(200)`
  - `assertJson(['tipo' => 'confirmacion_sin_cambios', 'episodios_activos' => 1])`
  - `assertEquals(1, EpisodioLaboral::where('user_id', $egresado->id)->count())`
  - `assertTrue((bool) $ep->es_actual)`
  - `assertEquals('2023-03-01', $ep->fecha_inicio->format('Y-m-d'))`
  - `assertTrue((bool) $actualizacion->es_confirmacion_sin_cambios)`
  - `assertEquals('2026-II', $actualizacion->periodo_campana)`
  - `assertEquals(now()->toDateString(), $actualizacion->fecha_ultima_confirmacion->toDateString())`
- **Resultado:** APROBADA (0.04s).

#### Prueba 5: `test_05_empleos_simultaneos`
- **Objetivo:** Validar que un docente con dos empleos activos en paralelo mantiene ambos registros con `es_actual = true`.
- **Aserciones Ejecutadas:**
  - `assertStatus(200)` en empleo 1
  - `assertStatus(200)` en empleo 2
  - `assertCount(2, $activos)`
  - `assertEquals(['Centro de Apoyo Pedagógico', 'I.E. Pública Matutina'], $activos->pluck('institucion')->sort()->values()->all())`
- **Resultado:** APROBADA (0.04s).

#### Prueba 6: `test_06_fecha_de_finalizacion_desconocida`
- **Objetivo:** Asegurar que si se cierra un empleo sin fecha exacta conocida, el sistema almacena `fecha_fin = NULL` y no forza la fecha actual.
- **Aserciones Ejecutadas:**
  - `assertStatus(200)`
  - `assertFalse((bool) $ep->es_actual)`
  - `assertNull($ep->fecha_fin)`
  - `assertEquals('Finalizó reemplazo temporal', $ep->motivo_cambio)`
- **Resultado:** APROBADA (0.03s).

#### Prueba 7: `test_07_reenvio_identico_idempotente_no_duplica_episodios`
- **Objetivo:** Garantizar la idempotencia ante reintentos de conexión o doble envío por parte del usuario.
- **Aserciones Ejecutadas:**
  - `assertStatus(200)` en primer envío
  - `assertStatus(200)` en segundo envío idéntico
  - `assertEquals(1, EpisodioLaboral::where('user_id', $egresado->id)->count())`
- **Resultado:** APROBADA (0.04s).

#### Prueba 8: `test_08_rechazo_de_identificador_de_usuario_invalido`
- **Objetivo:** Validar que una petición anónima que intente asociar una actualización a un `user_id` inexistente en la BD institucional es rechazada con código HTTP 422.
- **Aserciones Ejecutadas:**
  - `assertStatus(422)`
  - `assertJson(['success' => false])`
- **Resultado:** APROBADA (0.03s).

#### Prueba 9: `test_09_rechazo_acceso_egresado_a_otra_ficha`
- **Objetivo:** Certificar el control Anti-IDOR en servidor: un egresado autenticado no puede modificar la ficha de otro egresado.
- **Aserciones Ejecutadas:**
  - `assertStatus(403)`
  - `assertJson(['success' => false, 'error' => 'No tienes autorización para modificar la ficha de otro egresado.'])`
- **Resultado:** APROBADA (0.03s).

#### Prueba 10: `test_10_rechazo_http_403_encuestador_caso_no_asignado`
- **Objetivo:** Verificar que un encuestador telefónico no asignado en `GrupoEntrevistador` es rechazado con HTTP 403 al intentar editar a un egresado fuera de su grupo.
- **Aserciones Ejecutadas:**
  - `assertStatus(403)`
  - `assertJson(['success' => false])`
- **Resultado:** APROBADA (0.04s).

#### Prueba 11: `test_11_rollback_ante_error_transaccional`
- **Objetivo:** Comprobar que ante una excepción no controlada dentro del bloque transaccional, ninguna fila parcial queda guardada en la base de datos (Garantía ACID).
- **Aserciones Ejecutadas:**
  - `assertEquals(0, EpisodioLaboral::where('user_id', $egresado->id)->count())`
- **Resultado:** APROBADA (0.03s).

#### Prueba 12: `test_12_consistencia_entre_episodios_y_estado_laboral`
- **Objetivo:** Verificar la consistencia de los scopes de consulta (`activos()`, `historicos()`, `educativos()`, `docenciaDirecta()`) y la apertura automática de casos de acompañamiento institucional.
- **Aserciones Ejecutadas:**
  - `assertEquals(1, $egresado->episodiosLaborales()->activos()->count())`
  - `assertEquals(1, $egresado->episodiosLaborales()->historicos()->count())`
  - `assertEquals(2, $egresado->episodiosLaborales()->educativos()->count())`
  - `assertEquals(2, $egresado->episodiosLaborales()->docenciaDirecta()->count())`
  - `assertNotNull($caso)`
  - `assertEquals('titulación', $caso->tema)`
  - `assertEquals('Por revisar', $caso->estado)`
- **Resultado:** APROBADA (0.04s).

---

### 4. Prueba Funcional End-to-End en Servidor Local

Se realizó una prueba funcional simulando el ciclo completo en el navegador mediante peticiones HTTP autenticadas con sesión de cookies y cabecera `X-CSRF-TOKEN`:

```python
# Secuencia de prueba funcional ejecutada
1. GET /seguimiento-egresados/campana-publica -> Token CSRF obtenido y sesión iniciada (HTTP 200)
2. POST /seguimiento-egresados/actualizar (DNI sintético 40002381, Primer empleo) -> HTTP 200
   -> Respuesta: {"success":true,"tipo":"actualizacion_completa","actualizacion_id":73,"episodios_activos":1}
3. POST /seguimiento-egresados/actualizar (DNI sintético 40002381, sin_cambios: true) -> HTTP 200
   -> Respuesta: {"success":true,"tipo":"confirmacion_sin_cambios","message":"Situación actual confirmada exitosamente. No se han duplicado registros laborales.","actualizacion_id":73,"fecha_confirmacion":"2026-10-30","episodios_activos":1}
```

#### Verificación en Base de Datos:
```sql
SELECT count(*) FROM egresado_episodios_laborales WHERE user_id = 94; -- Resultado: 1
SELECT fecha_inicio, es_actual FROM egresado_episodios_laborales WHERE user_id = 94; -- 2024-03-01, 1
SELECT es_confirmacion_sin_cambios, periodo_campana, fecha_ultima_confirmacion FROM egresado_actualizaciones WHERE user_id = 94; -- 1, '2026-II', '2026-10-30 11:34:30'
```
*Los registros sintéticos de prueba fueron limpiados al concluir la sesión.*
