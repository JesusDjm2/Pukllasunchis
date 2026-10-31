# PUKLLA EN RED — DIAGNÓSTICO INSTITUCIONAL DEL PADRÓN Y DOCENTES
## Auditoría de Fuentes Reales, Conciliación de Egresados y Cuerpo Docente Elegible

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis — Cusco, Perú  
**Módulo:** Puklla en Red — Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados  
**Rama Git:** `sistemadeseguimientodeegresados`  
**Fecha de Auditoría:** 31 de octubre de 2026  
**Identificador Canónico:** `users.id` / prefijo institucional `REAL-{users.id}`

---

## 1. IDENTIFICACIÓN Y AUDITORÍA DE DOCENTES REALES

Se inspeccionó la tabla `users`, la tabla institucional `docentes` y el subsistema de roles Spatie Laravel-Permission (`model_has_roles`):

| Parámetro | Resultado Auditado | Observación Institucional |
|---|:---:|---|
| **Usuarios con rol Spatie `docente`** | **51** | Usuarios reales en `users` vinculados a la planta docente de Pukllasunchis |
| **Registros en tabla `docentes`** | **52** | Incluye correspondencia biunívoca con `users` |
| **Docentes activos y habilitados** | **51** | Ninguno posee rol de inhabilitación administrativa |
| **Docentes con rol `seguimiento-egresados`** | **1** (Prof. Carlos) | Habilitado previamente en Fase 1D; los demás se activan por campaña |
| **Roles de Coordinación y Dirección** | `coordinacion-empleabilidad`, `direccion`, `super-admin` | Identificados y separados jerárquicamente |

### Separación Estricta de Tres Conceptos:
1. **Rol institucional (`docente`):** Carácter profesional permanente del educador en la EESP Pukllasunchis. Se conserva invariable ante cualquier asignación.
2. **Rol funcional (`seguimiento-egresados`):** Permiso funcional de Spatie que faculta el acceso a las vistas de seguimiento y registro de entrevistas.
3. **Asignación temporal (`egresado_campana_asignaciones`):** Vínculo transaccional y periódico que autoriza al docente a consultar y entrevistar **únicamente** a los egresados asignados en una campaña activa.

---

## 2. DIAGNÓSTICO Y CONCILIACIÓN DEL PADRÓN DE EGRESADOS REALES

Se auditó el universo total de usuarios en la base institucional con `condicion = 'Egresado'`, conciliando con la tabla `alumnos`, registros académicos y expedientes históricos:

```
Total usuarios en base de datos: 848
Usuarios con condicion = 'Egresado': 403
Cuentas de auditoría sintética (test): 2 (auditor.egresada@puklla.test, egresado.noasignado@puklla.test)
───────────────────────────────────────────────────────────────────────────
PADRÓN DE EGRESADOS REALES INSTITUCIONALES: 401
```

### Tabla de Diagnóstico Institucional del Padrón:

| Situación del Padrón | Cantidad Auditada | Porcentaje (%) | Criterio de Atención Institucional |
|---|:---:|:---:|---|
| **Egresados Reales Verificados** | **401** | **100.0%** | Universo canónico de egresados reales |
| • *Programa Primaria EIB* | 150 | 37.4% | Educación Intercultural Bilingüe |
| • *Programa Educación Inicial* | 97 | 24.2% | Formación Inicial Docente |
| • *Sin programa explícito en ficha* | 154 | 38.4% | Cohortes históricas previas a digitalización |
| **Egresados con teléfono registrado** | **174** | **43.4%** | Aptos para llamada telefónica directa inmediata |
| **Egresados sin teléfono registrado** | **227** | **56.6%** | **No se excluyen:** se programan para contacto vía correo o redes |
| **Egresados con correo electrónico válido** | **401** | **100.0%** | Todos poseen correo `@pukllavirtual.edu.pe` o personal verificado |
| **Residencia basal conocida (Departamento/Provincia)** | **38** | **9.5%** | Fichas con UBIGEO previo en `alumnos` |
| **Lugar de trabajo registrado antes de campaña** | **0** | **0.0%** | Brecha que justifica la ejecución de la campaña de seguimiento |
| **Registros duplicados confirmados** | **0** | **0.0%** | Claves primarias e identidades conciliadas |
| **Casos pendientes de conciliación de contacto** | **227** | **56.6%** | Casos para búsqueda activa de número telefónico |

> [!IMPORTANT]
> **Regla de No Exclusión:** El hecho de que 227 egresados no cuenten con teléfono celular en su ficha histórica **no significa que no deban ser asignados**. Se distribuyen equitativamente entre los docentes para que se intente el contacto vía correo institucional o redes pedagógicas de excompañeros.

---

## 3. SEPARACIÓN RIGUROSA DE FUENTES Y DENOMINADORES

Para evitar falacias estadísticas en los reportes de Dirección y Coordinación, el sistema distingue contractualmente ocho universos:

1. **Padrón Institucional Total ($N_{padron} = 401$):** Todos los egresados reales registrados en la base de datos de Pukllasunchis.
2. **Egresados Elegibles de Campaña ($N_{elegibles}$):** Egresados no excluidos por motivos de duplicidad o fallecimiento.
3. **Egresados Asignados ($N_{asignados}$):** Egresados efectivamente repartidos entre los docentes entrevistadores de la campaña.
4. **Intentos de Llamada ($I_{llamadas}$):** Total bruto de marcaciones realizadas (múltiples intentos por persona).
5. **Egresados Contactados Efectivos ($C_{efectivos}$):** Personas físicas únicas con las que se logró entablar comunicación.
6. **Egresados que Respondieron / Actualizados ($A_{completos} + A_{sincambios}$):** Fichas con información actualizada o ratificada.
7. **Egresados con Información Laboral Válida:** Aquellos con episodios laborales verificables (cero empleadores ficticios "Desempleado").
8. **Egresados con Información Territorial Válida:** Registros con departamento, provincia y distrito UBIGEO conciliados.
