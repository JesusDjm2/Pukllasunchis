# 03 — Plan Incremental de Implementación y Definición de Terminado
## PUKLLA EN RED · EESP Pukllasunchis

**Versión:** 1.0 (Derivada de la Especificación Maestra v3.0)  
**Fecha:** 30 de octubre de 2026  
**Condición:** Plan de ingeniería incremental. **No se ejecutarán migraciones ni despliegues en producción sin previa aprobación formal.**

---

### 1. Fases de Desarrollo Incremental

```
  ┌───────────────────────────────────────────────────────────────┐
  │ FASE 0: Diagnóstico, Auditoría y Conciliación (EN CURSO)      │
  │ • Inspección de repo, datos reales, roles y matriz de brechas │
  └───────────────────────────────┬───────────────────────────────┘
                                  ▼
  ┌───────────────────────────────────────────────────────────────┐
  │ FASE 1: Núcleo Confiable y Actualización Longitudinal         │
  │ • Identidad única vinculada a users.id                        │
  │ • Roles Spatie institucionales (coordinacion, encuestador,...)│
  │ • Formulario adaptativo de 4 pasos (camino ágil sin cambios)  │
  │ • Historial laboral longitudinal sin sobrescritura destructiva│
  │ • Pruebas automatizadas R01, R02, R03                         │
  └───────────────────────────────┬───────────────────────────────┘
                                  ▼
  ┌───────────────────────────────────────────────────────────────┐
  │ FASE 2: Campañas Seguras, Acompañamiento y Analítica          │
  │ • Token criptográfico con hash y expiración para WhatsApp     │
  │ • Panel de encuestador con autorización estricta (403)        │
  │ • Módulo de Casos (SST) con segregación de notas sensibles    │
  │ • Consola analítica libre (filas × columnas × filtros)        │
  │ • Supresión de celdas pequeñas (<5) en exportaciones          │
  │ • Pruebas automatizadas R04, R05, R06, R07, R08               │
  └───────────────────────────────┬───────────────────────────────┘
                                  ▼
  ┌───────────────────────────────────────────────────────────────┐
  │ FASE 3: Formación, Empleabilidad, Visitas y Comunidad         │
  │ • Formación Continua (SFC): detección de demandas y talleres │
  │ • Bolsa Laboral (SIL): vacantes verificadas y CV pedagógico   │
  │ • Planificador de visitas territoriales y reencuentros        │
  │ • Módulo informativo PRONABEC (becas, plazos, sin sanciones)  │
  │ • Pruebas automatizadas R09                                   │
  └───────────────────────────────┬───────────────────────────────┘
                                  ▼
  ┌───────────────────────────────────────────────────────────────┐
  │ FASE 4: Evaluación de Impacto, QA E2E y Despliegue            │
  │ • Medición de utilidad y efectividad de intervenciones        │
  │ • Suite QA E2E en navegador real (móvil y escritorio)         │
  │ • Auditoría WCAG AA de accesibilidad (360px, teclado)         │
  │ • Pruebas de estrés y seguridad (anti-IDOR, anti-XSS, CSRF)   │
  │ • Piloto controlado con egresados y aprobación de Dirección   │
  └───────────────────────────────────────────────────────────────┘
```

---

### 2. Definición de Terminado por Historia (DoD)

Para que cualquier historia de usuario o módulo se considere **`HECHO` (Done)**, debe satisfacer obligatoriamente los siguientes 8 criterios:

1. **Integración con patrones existentes:** El código utiliza controladores, modelos Eloquent, migraciones y vistas Blade respetando la convención de la intranet de Pukllasunchis.
2. **Pruebas automatizadas aprobadas:** Tests unitarios y de integración (Feature Tests en PHPUnit) pasando en verde en el entorno local.
3. **Pruebas de autorización negativa:** Verificación explícita de que un usuario sin el rol o caso asignado recibe `HTTP 403 Forbidden` desde el servidor (no solo bloqueo visual en interfaz).
4. **Migraciones reversibles y seguras:** Toda migración cuenta con su método `down()` probado para rollback sin pérdida de datos históricos.
5. **Cero fugas de información personal (PII):** Ningún log, URL, pantalla pública ni archivo en Git contiene DNI, teléfonos, correos o notas confidenciales.
6. **Contrato de datos y trazabilidad:** Toda actualización registra fecha efectiva, fecha de captura, canal, autor y fuente.
7. **Diseño y accesibilidad validados:** Responsive sin desbordamiento horizontal en pantallas móviles (360px) y contraste accesible.
8. **Validación funcional y documentación:** Documentación técnica actualizada, changelog documentado y evidencia visual del flujo.

---

### 3. Matriz de Riesgos Técnicos y Mitigación

| Riesgo Técnico Identificado | Nivel | Impacto | Estrategia de Mitigación Obligatoria |
|---|:---:|---|---|
| **Bloqueo en `git push` (Error 403)** | Alto | Impide abrir el Pull Request en GitHub. | El token Fine-grained actual carece de `Contents: Read and write`. Se requiere generar un token Classic con scope `repo` o habilitar escritura en el Fine-grained token. |
| **Reidentificación en celdas pequeñas** | Alto | Exposición indirecta de datos de egresados al cruzar pocos casos. | Implementar algoritmo de supresión primaria y complementaria para cualquier celda con valor $1 \le n < 5$ antes de emitir JSON o XLSX. |
| **Vulnerabilidad en enlaces de WhatsApp** | Alto | Acceso a datos personales si el enlace se comparte en grupos. | No precargar datos personales identificables al abrir el enlace; requerir confirmación de identidad (OTP al celular registrado o PIN) antes de mostrar la ficha. |
| **Duplicación o conflicto de identidad** | Medio | Ruptura de historial si se crea una persona nueva en lugar de reutilizar `users.id`. | Prohibir inserciones en tablas de personas sin pasar por el motor de conciliación que valida DNI y código institucional. |
| **Sobrescritura destructiva de empleos** | Medio | Pérdida de la serie histórica laboral al actualizar trabajo. | Desacoplar la tabla de actualizaciones de la tabla de episodios laborales; cerrar episodios con `fecha_fin` y crear nuevos registros. |

---

### 4. Decisiones que Requieren Aprobación Institucional

Antes de iniciar la Fase 1, se someterán a consideración de la Dirección y Coordinación de Pukllasunchis las siguientes definiciones de negocio:

1. **Aprobación de nuevos roles Spatie:**
   - ¿Se aprueba registrar oficialmente los roles `coordinacion-empleabilidad`, `encuestador-seguimiento`, `bienestar` y `formacion-continua` en la tabla `roles` de la base de datos institucional?
2. **Umbral de supresión estadística:**
   - ¿Se confirma el umbral de supresión de $n < 5$ para la visualización de celdas y exportaciones de inteligencia institucional?
3. **Calendario y periodicidad de campañas:**
   - ¿Se fijan 2 campañas semestrales por año con ventanas de 30 días de duración cada una (fechas configurables desde el panel de coordinación)?
4. **Protocolo PRONABEC (Beca 18 / Beca Continua):**
   - ¿Se ratifica que el módulo de PRONABEC tendrá carácter exclusivamente orientativo y de acompañamiento para el egresado, sin sanciones automáticas ni presunción de incumplimiento?
