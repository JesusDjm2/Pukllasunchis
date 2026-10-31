# PUKLLA EN RED — GUÍA DE AUDITORÍA MANUAL Y RECORRIDO EN NAVEGADOR
## Circuito Operativo: Selección de Docentes, Asignación Equilibrada y Seguimiento de Egresados Reales

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis — Cusco, Perú  
**Repositorio:** `JesusDjm2/Pukllasunchis`  
**Rama:** `sistemadeseguimientodeegresados`  
**Servidor Local Activo:** `http://127.0.0.1:8000`  
**Campaña de Ensayo:** `ENSAYO INSTITUCIONAL — NO CONTACTAR` (ID: 10)  

---

## 1. ENLACES DE ACCESO DIRECTO EN 1 CLIC (SIN ESCRIBIR CLAVES)

Haz clic en los siguientes enlaces directos en tu navegador para ingresar inmediatamente con los roles correspondientes:

| Perfil Institucional | Enlace Directo (1 Clic) | Destino Inmediato |
|---|---|---|
| **Coordinación de Empleabilidad** | [http://127.0.0.1:8000/auditoria/login/coordinacion](http://127.0.0.1:8000/auditoria/login/coordinacion) | Padrón y gestión de campañas |
| **Docente Entrevistador (Marcel Bosch)** | [http://127.0.0.1:8000/auditoria/login/docente-entrevistador](http://127.0.0.1:8000/auditoria/login/docente-entrevistador) | Dashboard con 81 asignaciones reales |
| **Superadministrador** | [http://127.0.0.1:8000/auditoria/login/superadmin](http://127.0.0.1:8000/auditoria/login/superadmin) | Panel administrativo de usuarios y roles |
| **Dirección General** | [http://127.0.0.1:8000/auditoria/login/direccion](http://127.0.0.1:8000/auditoria/login/direccion) | Laboratorio de Inteligencia Institucional |
| **Cerrar Sesión** | [http://127.0.0.1:8000/auditoria/logout](http://127.0.0.1:8000/auditoria/logout) | Cierre seguro para cambiar de rol |

*(Si prefieres entrar manualmente desde [http://127.0.0.1:8000/login](http://127.0.0.1:8000/login), la contraseña de todas las cuentas es `12345678`)*.

---

## 2. RECORRIDO MANUAL PASO A PASO (CIRCUITO COMPLETO)

### Paso 1: Coordinación organiza la campaña y selecciona docentes reales
1. Ingresa como **Coordinación** desde el enlace de 1 clic: [http://127.0.0.1:8000/auditoria/login/coordinacion](http://127.0.0.1:8000/auditoria/login/coordinacion).
2. Dirígete a la sección de campañas: [http://127.0.0.1:8000/seguimiento-egresados/campanas/gestion](http://127.0.0.1:8000/seguimiento-egresados/campanas/gestion).
3. Observa las tarjetas del **Diagnóstico del Padrón Real**:
   - Total egresados reales: **401**
   - Con teléfono: **174** | Sin teléfono: **227** | Con correo válido: **401**
4. En la tabla de **Docentes Entrevistadores Reales** (51 docentes disponibles):
   - Haz clic en **«Seleccionar 5»**.
   - Haz clic en el botón **«Vista Previa de Distribución Equilibrada»**.
   - Se abrirá un modal mostrando el reparto matemático: 1 docente con 81 egresados y 4 docentes con 80 egresados (**Diferencia máxima = 1**).
5. Haz clic en **«Proceder con la Distribución»** para ejecutar la transacción ACID.
6. El sistema te redirige automáticamente al **Monitoreo en Tiempo Real**: [http://127.0.0.1:8000/seguimiento-egresados/campanas/10/monitoreo](http://127.0.0.1:8000/seguimiento-egresados/campanas/10/monitoreo).

---

### Paso 2: El docente entrevistador trabaja con sus egresados asignados
1. Cambia de sesión al docente entrevistador haciendo clic en: [http://127.0.0.1:8000/auditoria/login/docente-entrevistador](http://127.0.0.1:8000/auditoria/login/docente-entrevistador).
2. En su Dashboard Docente institucional verás el card destacado:
   `Seguimiento de Egresados — Puklla en Red` con la insignia verde `81 egresados asignados`.
3. Haz clic en el botón verde **«Mis Egresados Asignados»** o visita [http://127.0.0.1:8000/seguimiento-egresados/mis-asignaciones](http://127.0.0.1:8000/seguimiento-egresados/mis-asignaciones).
4. Verás sus **9 indicadores personales** con la explicación de qué cuenta cada uno.
5. En la lista de trabajo:
   - Observa que el teléfono es un enlace `tel:` que abre el marcador pero no registra automáticamente la llamada.
   - Haz clic en **«Registrar Llamada»**: selecciona uno de los **11 estados oficiales** (ej. *Contacto efectivo* o *Entrevista programada*), anota observaciones y guarda.
   - Haz clic en **«Actualizar Ficha»**: completa los datos de empleo o marca *«Confirmación rápida sin cambios»* y guarda.

---

### Paso 3: Coordinación comprueba el avance en vivo y la actualización territorial
1. Vuelve a iniciar sesión como Coordinación: [http://127.0.0.1:8000/auditoria/login/coordinacion](http://127.0.0.1:8000/auditoria/login/coordinacion).
2. Abre el **Monitoreo de Campaña**: [http://127.0.0.1:8000/seguimiento-egresados/campanas/10/monitoreo](http://127.0.0.1:8000/seguimiento-egresados/campanas/10/monitoreo).
   - Observa que los indicadores de intentos de llamada, contactos efectivos únicos y fichas actualizadas se han incrementado.
   - En la fila del Prof. Marcel Bosch verás su porcentaje de cobertura actualizado y la fecha de última actividad.
3. Abre el **Observatorio Territorial**: [http://127.0.0.1:8000/seguimiento-egresados/campanas/10/territorio](http://127.0.0.1:8000/seguimiento-egresados/campanas/10/territorio).
   - Observa la comparación antes vs después: los datos modificados o registrados por primera vez se reflejan en las estadísticas agregadas por provincia (Cusco, Anta, Calca, Urubamba) sin exponer direcciones privadas.

---

## 3. GARANTÍAS DEL ENTORNO DE ENSAYO
- **Aislamiento Total:** Todos los eventos generados están marcados con `es_ensayo = true`.
- **Cero Notificaciones Externas:** Los servicios de correo electrónico y mensajería externa están enrutados a mocks locales. Ningún egresado ni docente real recibe llamadas ni mensajes automáticos.
- **Integridad de Base de Datos:** La base de datos original de Pukllasunchis permanece intacta y no se han alterado contraseñas productivas.
