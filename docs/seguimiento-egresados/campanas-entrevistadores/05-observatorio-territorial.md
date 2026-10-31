# PUKLLA EN RED — OBSERVATORIO TERRITORIAL Y ACTUALIZACIÓN GEOGRÁFICA
## Comparación Antes vs Después, UBIGEO y Preservación de la Privacidad Estadística

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis — Cusco, Perú  
**Módulo:** Puklla en Red — Observatorio Territorial de Egresados  
**Ruta:** `/seguimiento-egresados/campanas/{campanaId}/territorio`  
**Controlador:** `App\Http\Controllers\SeguimientoCampanaController@territorio`  

---

## 1. COMPARACIÓN DEL ESTADO BASAL Y POSTERIOR DE LA INFORMACIÓN

El observatorio territorial compara el grado de conocimiento geográfico institucional antes y después de la ejecución de una campaña:

### Estado Basal (Previo a la Campaña):
- **Residencia Conocida:** 38 egresados (9.5% del padrón total).
- **Residencia Desconocida:** 363 egresados (90.5% sin registro en ficha histórica).
- **Lugar de Trabajo Conocido:** 0 egresados con institución empleadora previa verificada.
- **Lugar de Trabajo Desconocido:** 401 egresados.

### Actualización Posterior (Lograda en Campaña):
- **Residencia Confirmada Sin Cambios:** Egresados que ratificaron residir en la misma provincia/distrito.
- **Residencia Registrada Por Primera Vez:** Egresados que carecían de datos geográficos y cuyo domicilio fue completado durante la entrevista telefónica.
- **Residencia Modificada (Mudanzas):** Distingue formalmente un cambio real de domicilio respecto a llenar un campo que antes estaba vacío.
- **Lugar de Trabajo Actualizado:** Nuevos episodios laborales con departamento, provincia y distrito laboral formalmente contrastados.

---

## 2. DISTRIBUCIÓN TERRITORIAL POR PROVINCIAS DE CUSCO Y DEPARTAMENTOS

La interfaz gráfica presenta barras agregadas de concentración geográfica:

### Provincias de la Región Cusco:
1. **Cusco** (Provincia capital / ámbito de práctica docente)
2. **Urubamba** (Valle Sagrado de los Incas)
3. **Calca** (Comunidades y escuelas rurales EIB)
4. **Quispicanchi** (Comunidades quechuas y escuelas de alternancia)
5. **Anta** (Zona rural agropecuaria EIB)
6. **Canchis**, **La Convención**, **Espinar**, **Chumbivilcas**

### Departamentos del Perú:
1. **Cusco** (Sede principal y mayoritaria)
2. **Apurímac** (Docentes bilingües en provincias limítrofes)
3. **Puno** (Ámbito altiplánico y quechua-aymara)
4. **Lima** (Docencia en instituciones privadas o posgrados)
5. **Arequipa**, **Madre de Dios**

---

## 3. PRINCIPIO DE PRIVACIDAD ESTADÍSTICA

Conforme a las normativas de protección de datos personales y las directivas de Pukllasunchis:
- **No se publican coordenadas geográficas individuales** ni domicilios exactos en los mapas de libre consulta.
- Toda visualización territorial se presenta a nivel de agregación departamental, provincial o distrital.
- Las celdas y agrupaciones territoriales menores a 5 registros en reportes públicos se protegen bajo supresión primaria para evitar la reidentificación de egresados en parajes rurales pequeños.
