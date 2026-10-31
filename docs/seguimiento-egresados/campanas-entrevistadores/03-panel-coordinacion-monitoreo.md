# PUKLLA EN RED — PANEL DE COORDINACIÓN Y MONITOREO DE CAMPAÑA
## Supervisión en Tiempo Real, Métricas con Denominadores Explícitos y Reasignación Autorizada

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis — Cusco, Perú  
**Módulo:** Puklla en Red — Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados  
**Ruta:** `/seguimiento-egresados/campanas/{campanaId}/monitoreo`  
**Controlador:** `App\Http\Controllers\SeguimientoCampanaController@monitoreo`  

---

## 1. TABLERO DE CONTROL OPERATIVO (12 INDICADORES CON DENOMINADORES)

El panel de monitoreo consolida en tiempo real la actividad de los docentes entrevistadores y la respuesta de los egresados reales.

Cada indicador define contractualmente su numerador y su denominador:

| Indicador | Cómputo / Denominador | Significado Institucional |
|---|---|---|
| **1. Padrón Institucional Elegible** | Total en base de datos ($N_{total} = 401$) | Universo base de egresados de Pukllasunchis |
| **2. Egresados Asignados** | $\sum \text{Asignaciones Activas}$ ($N_{asig} / N_{total}$) | Cobertura de distribución institucional |
| **3. Egresados Sin Asignar** | $N_{total} - N_{asig}$ | Casos pendientes de incorporar a campaña |
| **4. Docentes Participantes** | $\text{Count(Distinct Docente)}$ | Docentes reales de la intranet en la campaña |
| **5. Intentos de Llamada** | Total de filas en `egresado_llamadas` | Marcaciones brutas realizadas |
| **6. Contactos Efectivos Únicos** | $\text{Distinct(Egresados con Contacto)}$ | Personas únicas con comunicación lograda ($C / N_{asig}$) |
| **7. Actualizaciones Completas** | Formularios con cambio o nuevos datos laborales | Fichas enriquecidas con empleo o titulación |
| **8. Confirmaciones Sin Cambios** | Actualizaciones ratificando situación previa | Mantenimiento de información sin duplicidad |
| **9. Actualizaciones Parciales** | Entrevistas donde faltó completar datos | Requieren llamada de refuerzo |
| **10. No Localizados / Equivocados** | Marcaciones con resultado `numero_equivocado` | Requieren activar búsqueda vía correo o redes |
| **11. Casos Pendientes de Atención** | Casos en `egresado_casos` sin cerrar | Derivaciones a Bienestar o Coordinación |
| **12. Información Laboral Validada** | Total de episodios en `episodios_laborales` | Empleos formalmente contrastados |

---

## 2. TABLA DE AVANCE INDIVIDUAL POR DOCENTE ENTREVISTADOR

La interfaz presenta el desglose por educador participante:

```
+--------------------------+-----------+-------------+--------------+------------+-------------------+-----------------------+------------------+-------+
| Docente                  | Asignados | Contactados | Actualizados | Pendientes | Cobertura Contacto| Cobertura Fichas      | Última Actividad | Casos |
+--------------------------+-----------+-------------+--------------+------------+-------------------+-----------------------+------------------+-------+
| Prof. Marcel Bosch       |    81     |      1      |      1       |     80     |       1.2%        |         1.2%          | 31/10/2026 12:18 |   0   |
| Prof. Brigitte Aubert    |    80     |      0      |      0       |     80     |       0.0%        |         0.0%          | Sin registros    |   0   |
| Prof. Walter Aroni       |    80     |      0      |      0       |     80     |       0.0%        |         0.0%          | Sin registros    |   0   |
| Prof. Viany Aviles       |    80     |      0      |      0       |     80     |       0.0%        |         0.0%          | Sin registros    |   0   |
| Prof. María Blanco       |    80     |      0      |      0       |     80     |       0.0%        |         0.0%          | Sin registros    |   0   |
+--------------------------+-----------+-------------+--------------+------------+-------------------+-----------------------+------------------+-------+
```

---

## 3. GESTIÓN DE CASOS Y REASIGNACIÓN AUTORIZADA

El panel permite a Coordinación:
1. **Identificar docentes con asignaciones estancadas** o dificultades de comunicación.
2. **Reasignar egresados específicos** a otro docente mediante el modal auditado.
3. **Revisar alertas institucionales** cuando un docente registra una necesidad de derivación (ej: solicitud de bolsa laboral, problemas socioemocionales o dudas de titulación).
