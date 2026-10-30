# PUKLLA EN RED — Sistema de Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados
## 07. Validación de Persistencia Longitudinal — Ejemplos Sintéticos Antes / Después

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis  
**Proyecto:** Puklla en Red — Intranet Institucional  
**Documento de Auditoría:** Validación de Esquemas y Trazabilidad Cronológica (Fase 1B)  
**Cláusula de Confidencialidad:** Todos los datos personales presentados a continuación son 100% sintéticos y ficticios. No se expone información privada de ningún estudiante, docente o egresado real.

---

### 1. Cuadro Comparativo de Arquitectura de Datos

| Dimensión | Enfoque Plano Anterior (Riesgo Mitigado) | Enfoque Longitudinal Puklla en Red (Fase 1B) |
|---|---|---|
| **Estructura de empleo** | Un solo empleo vigente en la tabla de egresados; el empleo anterior se sobreescribía. | Tabla relacional `egresado_episodios_laborales` vinculada a `users.id` con cardinalidad 1 a N. |
| **Confirmación "Sin cambios"** | Generaba una nueva fila idéntica o reiniciaba `fecha_inicio` a la fecha de hoy. | Actualiza `egresado_actualizaciones.fecha_ultima_confirmacion` y marca `es_confirmacion_sin_cambios = true`. **Cero inserciones en episodios.** |
| **Fecha de inicio real** | Se perdía la antigüedad laboral del egresado ante cada encuesta semestral. | `fecha_inicio` permanece fija en el momento de contratación original (`2024-03-01`). |
| **Transición de empleo** | Desaparecía la institución previa; no se podía calcular tiempo de permanencia ni movilidad. | El empleo anterior pasa a `es_actual = false`, guarda `fecha_fin` y `motivo_cambio`. Se crea el nuevo empleo con `es_actual = true`. |
| **Periodo de desempleo** | Se registraban empleadores artificiales ("Desempleado", "Sin trabajo") distorsionando el directorio de instituciones. | Se marcan los empleos activos como concluidos. **Cero filas ficticias**. La condición se deduce de la ausencia de episodios activos. |
| **Empleos simultáneos** | Imposible de modelar sin borrar uno de los trabajos. | Coexistencia nativa: múltiples registros con `es_actual = true` bajo el mismo `user_id`. |

---

### 2. Ejemplos Sintéticos Detallados: Casos de Uso Reales

#### Caso Sintético 1: Registro de Primer Empleo Docente

- **Perfil:** Egresada ficticia: *Elena Mamani Quispe* (`user_id: 101`, Programa: Educación Inicial, Egreso: 2024-II).
- **Acción:** Reporta que inició labores el 1 de marzo de 2025 en la I.E. Inicial N° 450 de San Jerónimo como docente de aula con contrato anual.

##### Estado en Base de Datos:
```sql
-- Tabla: egresado_actualizaciones
[
  "id": 12,
  "user_id": 101,
  "nombre_completo": "Elena Mamani Quispe",
  "trabaja": "Trabaja",
  "cambio_empleo": "Primer empleo",
  "fecha_ultima_confirmacion": "2025-04-10 10:15:00",
  "periodo_campana": "2025-I",
  "es_confirmacion_sin_cambios": 0
]

-- Tabla: egresado_episodios_laborales (1 registro)
[
  "id": 1,
  "user_id": 101,
  "institucion": "I.E. Inicial N° 450 San Jerónimo",
  "cargo": "Docente de Aula",
  "campo": "En educación",
  "tipo_practica": "Docencia directa",
  "sector": "Público/estatal",
  "contrato": "Contrato docente MINEDU",
  "fecha_inicio": "2025-03-01",
  "fecha_fin": NULL,
  "es_actual": 1,
  "es_empleo_educativo": 1,
  "es_docencia_directa": 1,
  "trab_departamento": "Cusco",
  "trab_distrito": "San Jerónimo"
]
```

---

#### Caso Sintético 2: Confirmación Semestral «Sin Cambios» (R02)

- **Perfil:** Egresada *Elena Mamani Quispe* (`user_id: 101`).
- **Acción:** Al llegar la campaña semestral 2025-II (octubre 2025), Elena abre el enlace en WhatsApp y confirma que sigue en la misma I.E. Completa el microchequeo: satisfacción 5/5, demanda formativa en "Didáctica en lengua quechua" y no solicita llamada prioritaria.

##### ANTES de Puklla en Red Fase 1B (Comportamiento erróneo tradicional):
```sql
-- Se insertaba una segunda fila duplicada en episodios:
-- ID 1: I.E. Inicial N° 450 (Inicio: 2025-03-01)
-- ID 2: I.E. Inicial N° 450 (Inicio: 2025-10-15) <-- ¡Duplicado! Se borraba la antigüedad
```

##### DESPUÉS de Puklla en Red Fase 1B (Comportamiento correcto implementado):
```sql
-- Tabla: egresado_episodios_laborales (SIGUE HABIENDO EXACTAMENTE 1 REGISTRO)
[
  "id": 1,
  "user_id": 101,
  "institucion": "I.E. Inicial N° 450 San Jerónimo",
  "cargo": "Docente de Aula",
  "fecha_inicio": "2025-03-01", -- PRESERVADA: NO se alteró
  "fecha_fin": NULL,
  "es_actual": 1
]

-- Tabla: egresado_actualizaciones (Actualizada in-place)
[
  "id": 12,
  "user_id": 101,
  "fecha_ultima_confirmacion": "2025-10-15 16:42:11", -- Nueva fecha de verificación
  "periodo_campana": "2025-II",
  "es_confirmacion_sin_cambios": 1, -- Flag R02
  "satisfaccion": "5 - Muy satisfecho/a",
  "temas_interes": "Lengua Quechua y Didáctica EIB",
  "necesidades_apoyo": "No por ahora"
]
```
*Garantía institucional:* La tasa de retención docente es calculable con exactitud; no se infló la base con 2 empleos inexistentes.

---

#### Caso Sintético 3: Transición Laboral con Cierre de Empleo Previo (R03)

- **Perfil:** Egresado ficticio: *Carlos Huamán Condori* (`user_id: 102`, Primaria EIB, Egreso: 2023).
- **Situación Previa:** Laboró como "Auxiliar de Educación" en la I.E. 50100 desde el `2024-03-01`.
- **Acción:** En marzo de 2025 reporta que aprobó el concurso público y asumió como "Docente de Aula EIB" en el Colegio San Agustín desde el `2025-03-01`, concluyendo su etapa previa el `2024-12-31` por "Ascenso y adjudicación docente".

##### Estado en Base de Datos:
```sql
-- Tabla: egresado_episodios_laborales (2 registros ordenados cronológicamente)
[
  {
    "id": 10,
    "user_id": 102,
    "institucion": "I.E. 50100",
    "cargo": "Auxiliar de Educación",
    "fecha_inicio": "2024-03-01",
    "fecha_fin": "2024-12-31", -- Fecha final efectiva registrada
    "motivo_cambio": "Ascenso y adjudicación docente",
    "es_actual": 0 -- Marcado como histórico
  },
  {
    "id": 11,
    "user_id": 102,
    "institucion": "Colegio San Agustín",
    "cargo": "Docente de Aula EIB",
    "fecha_inicio": "2025-03-01",
    "fecha_fin": NULL,
    "es_actual": 1 -- Marcado como vigente
  }
]
```
*Trazabilidad resultante:* El egresado cuenta con 10 meses como auxiliar y trayectoria activa como docente de aula. La historia es continua y auditable.

---

#### Caso Sintético 4: Dos Empleos Simultáneos Activos (R03)

- **Perfil:** Egresada ficticia: *Rosa Cárdenas Silva* (`user_id: 103`).
- **Situación:** Docente nombrada en colegio público por las mañanas (`I.E. Mixta Cusco`) y docente de taller lúdico por horas en las tardes en una ONG educativa (`Asociación Educativa Kusi`).

##### Estado en Base de Datos:
```sql
-- Tabla: egresado_episodios_laborales (Ambos es_actual = 1)
[
  {
    "id": 20,
    "user_id": 103,
    "institucion": "I.E. Mixta Cusco",
    "cargo": "Docente de Aula",
    "dedicacion": "Tiempo parcial (mañana)",
    "contrato": "Nombrado",
    "fecha_inicio": "2024-03-01",
    "fecha_fin": NULL,
    "es_actual": 1
  },
  {
    "id": 21,
    "user_id": 103,
    "institucion": "Asociación Educativa Kusi",
    "cargo": "Facilitadora Pedagógica",
    "dedicacion": "Por horas (tarde)",
    "contrato": "Servicios / Honorarios",
    "fecha_inicio": "2024-06-01",
    "fecha_fin": NULL,
    "es_actual": 1
  }
]
```
*Consulta institucional:*
`EpisodioLaboral::where('user_id', 103)->activos()->count() === 2`
El sistema refleja fielmente la pluriactividad del egresado sin generar conflictos de concurrencia.

---

#### Caso Sintético 5: Término de Empleo con Fecha Desconocida y Cero Empleadores Ficticios

- **Perfil:** Egresado ficticio: *Mateo Vargas Paucar* (`user_id: 104`).
- **Situación Previa:** Laboró en un colegio privado. En la actualización reporta que culminó su contrato al promediar mediados de año y actualmente no se encuentra trabajando; desconoce el día exacto de cese.

##### ANTES (Comportamiento erróneo tradicional):
```sql
-- Se insertaba una fila:
-- institucion: 'Desempleado', cargo: 'Ninguno', fecha_inicio: '2025-10-30'
```

##### DESPUÉS (Fase 1B implementada):
```sql
-- Tabla: egresado_episodios_laborales:
[
  "id": 35,
  "user_id": 104,
  "institucion": "Colegio Andino",
  "cargo": "Docente de Reemplazo",
  "fecha_inicio": "2024-03-01",
  "fecha_fin": NULL, -- Se conserva NULL, NO se inventa la fecha de hoy
  "es_actual": 0, -- Empleo cerrado
  "motivo_cambio": "Culminación de contrato temporal"
]

-- CERO FILAS con institucion = 'Desempleado'
```
*Consulta de empleabilidad institucional:*
- Egresados trabajando actualmente: `User::whereHas('episodiosActivos')->count()`
- Egresados en búsqueda de empleo: `User::whereDoesntHave('episodiosActivos')->whereHas('actualizaciones', fn($q) => $q->where('trabaja', 'No trabaja'))->count()`
Los indicadores matemáticos y las infografías reflejan la realidad sin datos corruptos.
