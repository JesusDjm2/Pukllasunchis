# 01 — Mapa de Datos, Conciliación y Calidad de Fuentes
## PUKLLA EN RED · EESP Pukllasunchis

**Fecha de análisis:** 30 de octubre de 2026  
**Confidencialidad:** Sin divulgación de datos personales identificables (PII). Análisis estructural y estadístico.

---

### 1. Inventario de Fuentes de Datos Existentes

| Fuente | Entidad / Tabla | Registros | Cobertura / Calidad | Rol en Puklla en Red |
|---|---|---|---|---|
| **Padrón Institucional Intranet** | `users` (`condicion = 'Egresado'`) | **401** | Clave primaria inmutable (`id`), DNI, correo institucional, celular. Programa y ciclo vinculados. | **Fuente maestra de identidad** y elegibilidad activa. |
| **Línea de Base Socioeconómica** | `alumnos` | **539** | Datos familiares, lengua materna, residencia, tipo de vivienda, trabajo previo. Vinculado a `users.id`. | Contexto de partida y trayectoria formativa. |
| **Respaldos Históricos EIB** | `respaldo_egresados_eib_20260904` | **61** | Egresados histórica de Primaria EIB (septiembre 2026). | Verificación de elegibilidad cohorte EIB. |
| **Respaldos Históricos FID** | `respaldo_egresados_fid_20260903` | **115** | Egresados histórica de Inicial y Primaria (septiembre 2026). | Verificación de elegibilidad cohorte FID. |
| **Titulación y Grados** | `titulacion_tesis` / `seguimiento_titulacion` | **456** | Estados de plan, asesor, jurado, sustentación y actas. | Fuente oficial de hitos de titulación. |
| **Catálogo Territorial Oficial** | `departamentos` / `provincias` / `distritos` | **25 / 193 / 1,832** | Códigos UBIGEO oficiales INEI. | Geocodificación estandarizada (residencia vs trabajo). |
| **Módulo Seguimiento (MySQL)** | `egresado_actualizaciones` | **1 (activa)** | Fichas de 4 pasos persistidas con canal, fechas y detalle. | Registro longitudinal de actualizaciones. |
| **Episodios Laborales** | `egresado_episodios_laborales` | **0** | Empleos con fecha inicio/fin, sector, tipo contrato, docencia directa. | Histórico laboral sin sobrescritura. |
| **Casos y Acompañamiento** | `egresado_casos` | **2 (activos)** | Solicitudes de titulación y empleo con responsable asignado y estado. | Flujo operativo "Del hallazgo a la acción". |
| **Grupos de Entrevistadores** | `egresado_grupos_entrevistadores` | **0 (probado)** | Comisiones docentes por cohorte y programa con egresados a cargo. | Gestión de llamadas y entrevistadores en campaña. |
| **Bitácora de Llamadas** | `egresado_llamadas` | **1** | Registro de intentos de llamada y resultados de contacto. | Gestión de llamadas semestrales por encuestador. |
| **Auditoría de Contactos** | `egresado_accesos_contacto` | **1** | Quién consultó celular/correo de un egresado y con qué finalidad. | Protección de datos y trazabilidad interna. |

---

### 2. Conciliación de Poblaciones y Denominadores

El documento maestro previene explícitamente sobre el riesgo de **mezclar denominadores** entre diferentes cortes y archivos de origen. La auditoría establece la siguiente distinción formal:

```
[ Universo Total Users Egresados: 401 ]
   │
   ├─ FID Educación Inicial (aprox. 186 en base)
   ├─ FID Educación Primaria EIB (aprox. 168 en base)
   └─ FID Educación Primaria Regular y Otros (aprox. 47 en base)

[ Corte Histórico "Siete Listas" (2021-II a 2025-II) ]
   ├─ Filas en planillas de origen: 266 filas nominales
   ├─ Personas únicas por DNI: 258 personas
   ├─ Condición de egreso/título explícita: 94 confirmados
   └─ Pendiente de verificación académica: 164 casos

[ Archivo Analítico Encuesta 2026 ]
   ├─ Respuestas brutas recogidas: 119
   ├─ Respuestas únicas tras duplicados exactos: 115
   └─ Registros depurados conciliados con padrón: 113 registros
```

#### Regla Institucional de Métricas:
Toda tasa de empleabilidad o cobertura debe informar de manera transparente:
1. **Denominador A (Respondientes válidos):** Porcentaje sobre las personas que completaron la ficha en la campaña activa.
2. **Denominador B (Población total de la cohorte):** Porcentaje sobre el universo institucional, clasificando a quienes no respondieron como **«Sin contacto / Cobertura pendiente»** y **nunca** como desempleados presuntos.

---

### 3. Modelo Relacional y Llaves de Integración

```
                     ┌───────────────────────┐
                     │         users         │ (Identidad inmutable)
                     │ ───────────────────── │
                     │ id (PK)               │
                     │ dni, name, apellidos  │
                     │ email, telefono       │
                     │ condicion, beca       │
                     └──────────┬────────────┘
                                │ 1
                                │
        ┌───────────────────────┼───────────────────────┐
        │ 1:N                   │ 1:N                   │ 1:N
        ▼                       ▼                       ▼
┌──────────────────┐    ┌───────────────┐    ┌─────────────────────┐
│ egresado_        │    │ egresado_     │    │ egresado_           │
│ actualizaciones  │    │ casos (SST)   │    │ accesos_contacto    │
│ ──────────────── │    │ ───────────── │    │ ─────────────────── │
│ id (PK)          │    │ id (PK)       │    │ id (PK)             │
│ user_id (FK)     │    │ user_id (FK)  │    │ user_id_consultado  │
│ canal, campana   │    │ responsable   │    │ rol_consultante     │
│ estado_academico │    │ estado        │    │ finalidad, fecha    │
│ situacion_laboral│    │ fecha_gestion │    └─────────────────────┘
└───────┬──────────┘    └───────┬───────┘
        │ 1:N                   │ 1:N
        ▼                       ▼
┌──────────────────┐    ┌───────────────┐
│ egresado_        │    │ egresado_     │
│ episodios_       │    │ caso_         │
│ laborales        │    │ acciones      │
│ ──────────────── │    │ ───────────── │
│ fecha_inicio/fin │    │ seguimiento   │
│ docencia_directa │    │ evaluacion    │
│ sector, contrato │    └───────────────┘
└──────────────────┘
```

#### Reglas Clave de Persistencia Longitudinal:
1. **Sin sobrescritura destructiva:** Cuando un egresado reporta un cambio laboral, el registro anterior no se modifica; se registra la fecha de término del episodio previo y se crea el nuevo episodio.
2. **Confirmación "Sin Cambios":** Si el egresado declara que su situación laboral y académica no cambió, el sistema registra un evento de confirmación de campaña con la fecha observada, evitando duplicar episodios de trabajo.
3. **Segregación de Notas Sensibles:** Los textos de solicitudes personales de bienestar (`necesidades_apoyo`, socioemocional) no se almacenan en texto plano en listados abiertos; se gestionan exclusivamente en `egresado_casos` accesible por personal autorizado.
