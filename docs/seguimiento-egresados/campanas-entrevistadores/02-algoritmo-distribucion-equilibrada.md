# PUKLLA EN RED — ALGORITMO DE DISTRIBUCIÓN ALEATORIA Y EQUILIBRADA
## Reparto Equitativo de Egresados Reales con Idempotencia y Transaccionalidad ACID

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis — Cusco, Perú  
**Módulo:** Puklla en Red — Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados  
**Servicio Implementado:** `App\Services\SeguimientoEgresado\CampanaAsignacionService`  
**Entorno:** Laravel 10, MySQL 8 / MariaDB, Eloquent ORM  

---

## 1. PRINCIPIO Y DEMOSTRACIÓN MATEMÁTICA DE EQUIDAD

El algoritmo garantiza que ningún docente tenga una sobrecarga de trabajo injusta en comparación con sus colegas, manteniendo al mismo tiempo una asignación aleatoria que evita sesgos de afinidad o selección subjetiva.

### Teorema del Reparto Óptimo:
Dados $N$ egresados elegibles y $K$ docentes entrevistadores seleccionados ($K \ge 1$):

$$N = q \cdot K + r \quad \text{con } 0 \le r < K$$

Donde:
- $q = \lfloor N / K \rfloor$ es la cantidad base que recibe cada docente.
- $r = N \pmod K$ es el residuo de egresados sobrantes.

El algoritmo distribuye el residuo $r$ asignando exactamente 1 egresado adicional a los primeros $r$ docentes de la permutación aleatoria:

- **$r$ docentes** recibirán $q + 1$ egresados.
- **$K - r$ docentes** recibirán $q$ egresados.

### Demostración de Diferencia Máxima:
Para cualesquiera dos docentes $i$ y $j$:

$$\max |Asignados(i) - Asignados(j)| = (q + 1) - q = 1 \quad (\text{si } r > 0)$$
$$\max |Asignados(i) - Asignados(j)| = q - q = 0 \quad (\text{si } r = 0)$$

$$\therefore \Delta_{\max} \le 1 \quad \text{(Q.E.D.)}$$

---

## 2. CASO REAL PUKLLASUNCHIS (401 Egresados entre 5 Docentes)

En la ejecución real sobre la base institucional con 5 docentes seleccionados:

- $N = 401$
- $K = 5$
- $q = \lfloor 401 / 5 \rfloor = 80$
- $r = 401 \pmod 5 = 1$

Resultado verificado en base de datos:

| Docente Entrevistador | Cantidad Asignada | Cálculo |
|---|:---:|:---:|
| **Prof. Marcel Bosch** | **81** | $q + 1$ ($80 + 1$) |
| **Prof. Brigitte Aubert Velasco** | **80** | $q$ |
| **Prof. Walter Aroni Peralta** | **80** | $q$ |
| **Prof. Viany Aviles Mamani** | **80** | $q$ |
| **Prof. María Blanco Berglund** | **80** | $q$ |
| **TOTAL CONSOLIDADO** | **401** | $\Delta_{\max} = 81 - 80 = \mathbf{1}$ |

---

## 3. GARANTÍAS DE SEGURIDAD Y CONTROL DE CONCURRENCIA (ACID)

La operación de distribución se implementó siguiendo los más altos estándares de confiabilidad:

1. **Bloqueo Pesimista (`lockForUpdate`):** Se bloquea la fila de la campaña en `egresado_campanas` para evitar condiciones de carrera si dos coordinadores intentan distribuir simultáneamente.
2. **Idempotencia:** Antes de distribuir, el sistema filtra los egresados que ya cuenten con asignación activa en la campaña (`AsignacionCampana::activos()->where('campana_id', $campanaId)`). Si todos los egresados ya están asignados, la operación aborta de manera segura sin crear duplicados.
3. **Persistencia en Bloques (`chunks`):** La inserción de los 401 registros se realiza en lotes de 100 mediante transacciones seguras de base de datos.
4. **Activación Automática de Roles:** El docente conserva su rol permanente `docente` y recibe automáticamente el rol funcional `seguimiento-egresados` de Spatie si aún no lo tenía.

---

## 4. REASIGNACIÓN INDIVIDUAL CONSERVANDO EL HISTORIAL

Cuando un docente no puede continuar con el seguimiento de un egresado (por licencia, incompatibilidad horaria o solicitud justificada de Coordinación), la reasignación se efectúa mediante `CampanaAsignacionService::reasignarEgresado()`:

- La asignación anterior pasa a estado `reasignado` y almacena el `motivo_reasignacion`.
- Se crea una nueva asignación activa vinculada al nuevo docente, registrando el `docente_anterior_id`.
- Se revoca de inmediato la autorización del docente anterior sobre ese expediente.
- **Se preservan intactas todas las llamadas y actualizaciones históricas previas.**
