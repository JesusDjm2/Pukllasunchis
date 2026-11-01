# AYLLU PUKLLA — MESA DOCENTE DE PROYECTOS INTEGRADORES ANUALES
## Diagnóstico Fase 0, Arquitectura Aplicada, Conciliación Documental y Matriz de Permisos

**Proyecto:** Ayllu Puklla — «Un espacio para aprender y construir comunidad»  
**Institución:** EESP Pukllasunchis (Cusco, Perú)  
**Rama de trabajo:** `feature/ayllu-puklla-proyectos-integradores` (creada desde `feature/ayllu-puklla` `c557f95`, basada a su vez en `sistemadeseguimientodeegresados` `ecda55d`)  
**Fecha:** 1 de noviembre de 2026

---

## 1. Fase 0: Auditoría técnica, correspondencia institucional y fuentes

### 1.1 Stack y entorno real verificado
- **Framework y motor:** Laravel `10.50.2` (`composer.json` requiere `php: ^8.1`; entorno CLI local ejecutando `PHP 8.4.12`), MySQL 8, Blade + Bootstrap 4, `spatie/laravel-permission ^6.20` y `ezyang/htmlpurifier ^4.19`.
- **Ramas preservadas sin alteración:** `main`, `sistemadeseguimientodeegresados` (`ecda55d`) y `feature/ayllu-puklla` (`c557f95`). Todo el incremento de la Mesa Docente de Proyectos Integradores se desarrolla en la rama dedicada `feature/ayllu-puklla-proyectos-integradores`.
- **Verificación de URL de referencia externa:** La URL de prototipo `https://ayllu-puklla-mesa-docente.tonedcello1.chatgpt.site` devuelve `HTTP/2 401` (requiere autorización de propietario en `chatgpt.site`). Conforme a la Sección 4 de la especificación, se declara explícitamente esta restricción de acceso y se construye el incremento directamente a partir de los seis archivos fuente oficiales y la especificación funcional completa.

### 1.2 Mapa de reutilización de entidades institucionales (sin duplicar fuentes de verdad)

| Entidad existente | Tabla | Uso en Ayllu Puklla y Mesa Docente de Proyectos Integradores |
| :--- | :--- | :--- |
| [`User`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/User.php) + Roles Spatie | `users`, `roles`, `model_has_roles` | Identidad única institucional, autenticación y roles (`super-admin`, `admin`, `adminB`, `direccion`, `docente`, `tutor`, `alumno`, `alumnoB`). |
| [`Docente`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Docente.php) | `docentes`, `curso_docente`, `proyecto_docente` | Asignación real vigente de docentes a cursos y a la organización de proyectos integradores. |
| [`Alumno`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Alumno.php) / [`ppd`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/ppd.php) | `alumnos`, `alumno_cursos`, `matriculas`, `ppds` | Pertenencia estudiantil real a programa, ciclo y cursos matriculados. |
| [`Programa`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Programa.php) y [`Ciclo`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Ciclo.php) | `programas`, `ciclos`, `tutor_ciclos` |Estructura de programas FID/PPD, ciclos I–X y tutoría por ciclo. |
| [`Curso`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Curso.php) y [`Silabo`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Silabo.php) | `cursos`, `silabos`, `unidades`, `rubricas` | Catálogo institucional de cursos (179 registros) y sílabos vivos vinculados a cada posición documental confirmada. |
| [`Proyecto`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Proyecto.php) y [`TaqeGrupo`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/TaqeGrupo.php) | `proyectos`, `taqe_grupos`, `taqe_grupo_alumno`, `taqe_trabajos` | Proyectos anuales asociados a `ciclos.proyecto_id` (IDs `11` a `14` para ciclos I–VIII) y equipos estudiantiles Taq'e. |
| [`PeriodoActual`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/PeriodoActual.php) | `periodo_actuals` | Semestre lectivo activo (`2026-II`), separado del año formativo curricular (`1..4`). |

---

## 2. Inventario y conciliación de las 6 fuentes documentales

Se procesaron íntegramente los 6 archivos fuente y se generó el manifiesto canónico auditable en [`database/data/fuentes_proyectos_integradores_matriz.json`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/database/data/fuentes_proyectos_integradores_matriz.json):

| # | Archivo fuente | Tipo | Año / Ciclos | Posiciones / Entradas |
| :--- | :--- | :--- | :--- | ---: |
| 1 | `Año 1 Proyectos integradores .xlsx` | Matriz anual Excel | Año 1 (Ciclo I y Ciclo II) | 20 (`10` + `10`) |
| 2 | `Año 2  Proyectos integradores .xlsx` | Matriz anual Excel | Año 2 (Ciclo III y Ciclo IV) | 30 (`16` + `14`) |
| 3 | `Año 3 Proyectos integradores .xlsx` | Matriz anual Excel | Año 3 (Ciclo V y Ciclo VI) | 36 (`18` + `18`) |
| 4 | `Año 4 Proyectos integradores 18_02_2025.xlsx` | Matriz anual Excel | Año 4 (Ciclo VII y Ciclo VIII) | 28 (`16` + `12`) |
| 5 | `2020 - 2024 Proyectos integradores.docx` | Fundamentación Word | Años 1 a 4 (Sección 6.6.2) | 4 tablas de proyecto |
| 6 | `Copia de 03 Proyectos INTEGRADORES.docx` | Fundamentación Word | Años 1 a 4 (Sección 6.6.2) | 4 tablas de proyecto |
| **Total** | **Conciliación exacta** | | **4 años · 8 ciclos (I–VIII)** | **114 posiciones de matriz** |

### 2.1 Las 9 celdas de Competencias con valores numéricos anómalos de 5 dígitos
Todas provienen de celdas con formato numérico de fecha en Excel (`numFmtId=164/165`, `formatCode="d, m, yy"`). Se registran con su valor bruto y formato original, señaladas para revisión sin reconstrucción especulativa:

1. **Año 1 · Ciclo II · `C18`** (`Práctica e investigación II`): valor bruto `41185.0` (`d, m, yy`)
2. **Año 1 · Ciclo II · `E18`** (`Comunicación oral en la Educación Superior`): valor bruto `40424.0` (`d, m, yy`)
3. **Año 1 · Ciclo II · `F18`** (`Resolución de problemas matemáticos II`): valor bruto `40756.0` (`d, m, yy`)
4. **Año 2 · Ciclo III · `D8`** (`Desarrollo Personal II`): valor bruto `40424.0` (`d, m, yy`)
5. **Año 2 · Ciclo III · `E8`** (`Arte, Creatividad y Aprendizaje`): valor bruto `41189.0` (`d, m, yy`)
6. **Año 2 · Ciclo IV · `D18`** (`Deliberación y Participación`): valor bruto `39236.0` (`d, m, yy`)
7. **Año 2 · Ciclo IV · `E18`** (`Ciencia y Epistemologías`): valor bruto `41091.0` (`d, m, yy`)
8. **Año 3 · Ciclo V · `D8`** (`Literatura y Sociedad en Contextos Diversos`): valor bruto `41189.0` (`d, m, yy`)
9. **Año 3 · Ciclo V · `L8`** (`Aprendizaje de la Comunicación I`): valor bruto `40727.0` (`d, m, yy`)

### 2.2 Diferencias documentales preservadas entre los Word y las Matrices
- **Año 1 (Ciclos I–II):** En los Word se titula *«Escuela y Territorio: Diagnóstico Socio-Cultural»*; en la matriz Excel se denomina en Ciclo I *«Escuela y territorio: la escuela situada»* (`C4`) y en Ciclo II *«Territorio»* (`C14`), con producto integrador *«Mapa parlante del territorio de una Institución Educativa»*. `Copia de 03` explicita la sabiduría comunitaria sobre el espacio sagrado (lagunas, puquios, apus protectores).
- **Año 2 (Ciclos III–IV):** En los Word se titula *«Prácticas de crianza y educación intercultural.»*; en la matriz se denomina *«Prácticas de crianza interculturales»* y define como producto integrador el *«Podcast sobre la comparación de prácticas de crianza entre familias con procedencias culturales distintas.»*.
- **Año 3 (Ciclos V–VI):** En el Word `2020 - 2024` se denomina *«Modos de vida, valores y cultura: tecnologías, medios de comunicación y textos escolares..»*; en `Copia de 03` cambia el título a *«Saber y conocer: formas de aprendizaje»* e introduce los aprendizajes de los abuelos, aunque conserva pegado el pasaje previo sobre medios y textos escolares; la matriz de 3er año consolida el propósito en torno a las experiencias lectoescritoras y procesos de aprendizaje de los abuelos y el producto *«Material didáctico concreto: juegos, títeres, cuentos»*.
- **Año 4 (Ciclos VII–VIII):** En los Word se denomina *«Rituales escolares: configuración de la escuela y necesidades para su transformación.»* y plantea un plan de tutoría o materiales específicos; en la matriz del `18_02_2025` se denomina *«Rituales escolares: oportunidades de cambio para la configuración de la escuela y necesidades para su transformación.»* con el producto integrador *«Feria de didácticas escolares alternativas: ABP, Proyectos de Aprendizaje»*.

---

## 3. Arquitectura del modelo funcional implementado

Migración aditiva: [`2026_11_01_080000_create_ayllu_mesa_proyectos_integradores_tables.php`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/database/migrations/2026_11_01_080000_create_ayllu_mesa_proyectos_integradores_tables.php)

- **A. Definición y versión del proyecto ([`AylluPiDefinicion`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Ayllu/AylluPiDefinicion.php)):** Almacena las versiones documentales por año (`matriz_excel`, `word_2020_2024`, `word_copia_03` y revisiones futuras). Las versiones en uso quedan bloqueadas contra sobrescritura; cualquier cambio en la fuente genera una nueva versión trazable.
- **B. Ejecución anual ([`AylluPiEjecucion`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Ayllu/AylluPiEjecucion.php)):** Relaciona una definición curricular adoptada con el año lectivo (`2026`), período (`PeriodoActual`), cohorte y par de ciclos (`I–II`, `III–IV`, `V–VI`, `VII–VIII`).
- **C. Aporte de curso ([`AylluPiAporteCurso`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Ayllu/AylluPiAporteCurso.php)):** Representa las 114 posiciones documentales con su coordenada exacta de archivo, hoja y celda, agrupación de programa (`FORMACION_GENERAL`, `INICIAL`, `PRIMARIA`, `PRIMARIA_EIB`), correspondencia con el catálogo `cursos`, acuerdos operativos de la mesa y estado real de desarrollo (`sin_registrar` → `en_desarrollo` → `para_revision_compartida` → `revisado` → `integrado_al_producto_anual`).
- **D. Desarrollo y evidencia versionada ([`AylluPiEvidenciaVersion`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Ayllu/AylluPiEvidenciaVersion.php)):** Historial numerado (`v1`, `v2`...) con autoría, audiencia explícita, reconocimiento de autoría comunitaria, restricciones de circulación (`ninguna`, `uso_interno_ciclo`, `reserva_comunitaria`, `autorizacion_retirada`) y almacenamiento de archivos en disco privado (`storage/app/private/ayllu_pi_evidencias/`).
- **E. Articulación y bitácora ([`AylluPiArticulacion`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Ayllu/AylluPiArticulacion.php), [`AylluPiBitacoraMesa`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Ayllu/AylluPiBitacoraMesa.php)):** Dependencias acordadas entre cursos (qué insumo entrega un curso a otro) y bitácora cronológica filtrada por audiencia.
- **F. Síntesis comunitaria y auditoría ([`AylluPiSintesisComunitaria`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Ayllu/AylluPiSintesisComunitaria.php), [`AylluPiAuditoriaEvento`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Models/Ayllu/AylluPiAuditoriaEvento.php)):** Flujo editorial (`pendiente_revision_editorial` → `aprobada_publicada`) que nunca amplía permisos de archivos privados originales, y bitácora de auditoría de eventos.

---

## 4. Matriz de permisos por acción y ámbito ([`AylluAuthorizationService`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/app/Services/AylluAuthorizationService.php))

| Acción | Coordinación / Autoridad | Docente asignado al aporte (`curso_docente` o `ayllu_pi_asignaciones_mesa` activa) | Docente de otro curso del mismo ciclo | Docente ajeno (de otro ciclo/año o con asignación revocada) | Estudiante del ciclo/equipo |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Ver matriz curricular (4 años)** | Sí | Sí | Sí | Sí (consulta curricular) | Sí |
| **Ver ficha operativa y deliberaciones (`mesa_docente_ciclo`)** | Sí | Sí | Sí | **No (`403`)** | **No (oculto/bloqueado)** |
| **Editar ajuste operativo del aporte** | Sí | Sí | **No (`403`)** | **No (`403`)** | **No (`403`)** |
| **Registrar versión de evidencia** | Sí | Sí | **No (`403`)** | **No (`403`)** | Sí (en su curso/equipo, audiencia `equipo_estudiantes`) |
| **Comentar en bitácora de la mesa del ciclo** | Sí | Sí | Sí | **No (`403`)** | **No (`403`)** |
| **Revisar versión (`revisado` / `devuelto_a_desarrollo`)** | Sí | Sí | Sí (revisión entre pares del ciclo) | **No (`403`)** | **No (`403`)** |
| **Integrar versión al producto anual (`integrado_al_producto_anual`)** | Sí | Tutor del ciclo / Coordinador | **No (`403`)** | **No (`403`)** | **No (`403`)** |
| **Descargar archivo privado de evidencia** | Según restricción | Sí | Sí (si audiencia es mesa/ciclo y no es `borrador_personal`) | **No (`403`)** | Solo si audiencia es `equipo_estudiantes`/`ciclo_completo` y sin `reserva_comunitaria` |
| **Aprobar definición / Adoptar versión / Revocar asignación** | Sí | **No (`403`)** | **No (`403`)** | **No (`403`)** | **No (`403`)** |
| **Aprobar y publicar síntesis comunitaria** | Sí | Solicita (`pendiente_revision_editorial`) | Solicita | **No (`403`)** | **No (`403`)** |

---

## 5. Procedimiento de instalación, importación idempotente y reversión segura

### Ejecución e importación verificable
```bash
# 1. Ejecutar migraciones aditivas
php artisan migrate

# 2. Previsualizar conciliación documental sin escribir en base de datos
php artisan ayllu:importar-proyectos --preview

# 3. Ejecutar importación transaccional e idempotente (114 entradas, 10 versiones, 9 anomalías)
php artisan ayllu:importar-proyectos --lectivo=2026

# 4. Ejecutar suites de pruebas automatizadas
php artisan test tests/Feature/AylluMesaProyectosIntegradoresTest.php tests/Feature/AylluPukllaMvpTest.php tests/Feature/SeguimientoIntegracionRolesTest.php
```

### Reversión segura del incremento
La migración [`2026_11_01_080000_create_ayllu_mesa_proyectos_integradores_tables.php`](file:///Users/pavelchaparro/.gemini/antigravity/scratch/Pukllasunchis/database/migrations/2026_11_01_080000_create_ayllu_mesa_proyectos_integradores_tables.php) bloquea por defecto cualquier `migrate:rollback` si existen registros en las tablas `ayllu_pi_*` o si el entorno es `production`. En entorno local de pruebas, si se desea revertir explícitamente el incremento sin tocar ninguna tabla institucional existente:
```bash
AYLLU_ALLOW_DESTRUCTIVE_ROLLBACK=true php artisan migrate:rollback --step=1
```
