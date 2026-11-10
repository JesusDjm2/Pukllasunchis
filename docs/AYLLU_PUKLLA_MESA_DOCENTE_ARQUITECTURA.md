# AYLLU PUKLLA — MESA DOCENTE DE PROYECTOS INTEGRADORES ANUALES
## Diagnóstico Fase 0, Arquitectura Aplicada, Conciliación Documental y Matriz de Permisos

**Proyecto:** Ayllu Puklla — «Un espacio para aprender y construir comunidad»  
**Institución:** EESP Pukllasunchis (Cusco, Perú)  
**Rama de trabajo:** `feature/ayllu-puklla-proyectos-integradores` (creada desde `feature/ayllu-puklla` `c557f95`, basada a su vez en `sistemadeseguimientodeegresados` `ecda55d`)  
**Fecha de actualización:** 10 de noviembre de 2026

---

## 1. Fase 0: Auditoría técnica, correspondencia institucional y fuentes

### 1.1 Stack y entorno real verificado
- **Framework y motor:** Laravel `10.50.2` (`composer.json` requiere `php: ^8.1`; entorno CLI local ejecutando `PHP 8.4.12`), MySQL 8, Blade + Bootstrap 4, `spatie/laravel-permission ^6.20` y `ezyang/htmlpurifier ^4.19`.
- **Ramas preservadas sin alteración:** `main`, `sistemadeseguimientodeegresados` (`ecda55d`) y `feature/ayllu-puklla` (`c557f95`). Todo el incremento de la Mesa Docente de Proyectos Integradores se desarrolla en la rama dedicada `feature/ayllu-puklla-proyectos-integradores`.
- **Verificación de URL de referencia externa:** La URL de prototipo `https://ayllu-puklla-mesa-docente.tonedcello1.chatgpt.site` devuelve `HTTP/2 401` (requiere autorización de propietario en `chatgpt.site`). Conforme a la especificación, se declara explícitamente esta restricción de acceso y se construye el incremento directamente a partir de los seis archivos fuente oficiales.

### 1.2 Mapa de reutilización de entidades institucionales (sin duplicar fuentes de verdad)

| Entidad existente | Tabla | Ruta en el repositorio | Uso en Ayllu Puklla y Mesa Docente de Proyectos Integradores |
| :--- | :--- | :--- | :--- |
| `User` + Roles Spatie | `users`, `roles`, `model_has_roles` | `app/Models/User.php` | Identidad única institucional, autenticación y roles (`super-admin`, `admin`, `adminB`, `direccion`, `docente`, `tutor`, `alumno`, `alumnoB`). |
| `Docente` | `docentes`, `curso_docente`, `proyecto_docente` | `app/Models/Docente.php` | Asignación real vigente de docentes a cursos y a la organización de proyectos integradores. |
| `Alumno` / `ppd` | `alumnos`, `alumno_cursos`, `matriculas`, `ppds` | `app/Models/Alumno.php`, `app/Models/ppd.php` | Pertenencia estudiantil real a programa, ciclo y cursos matriculados. |
| `Programa` y `Ciclo` | `programas`, `ciclos`, `tutor_ciclos` | `app/Models/Programa.php`, `app/Models/Ciclo.php` | Estructura de programas FID/PPD, ciclos I–X y tutoría por ciclo. |
| `Curso` y `Silabo` | `cursos`, `silabos`, `unidades`, `rubricas` | `app/Models/Curso.php`, `app/Models/Silabo.php` | Catálogo institucional de cursos y sílabos vivos vinculados a cada posición documental confirmada. |
| `Proyecto` y `TaqeGrupo` | `proyectos`, `taqe_grupos`, `taqe_grupo_alumno`, `taqe_trabajos` | `app/Models/Proyecto.php`, `app/Models/TaqeGrupo.php` | Proyectos anuales asociados a `ciclos.proyecto_id` (IDs `11` a `14` para ciclos I–VIII) y equipos estudiantiles Taq'e. |
| `PeriodoActual` | `periodo_actuals` | `app/Models/PeriodoActual.php` | Semestre lectivo activo (`2026-II`), separado del año formativo curricular (`1..4`). |

---

## 2. Inventario y conciliación de las 6 fuentes documentales

Se procesaron íntegramente los 6 archivos fuente y se generó el manifiesto canónico auditable en `database/data/fuentes_proyectos_integradores_matriz.json` (`version_manifiesto: 2026.11.01.2`):

| # | Archivo fuente | Tipo | Año / Ciclos | Posiciones / Entradas |
| :--- | :--- | :--- | :--- | ---: |
| 1 | `Año 1 Proyectos integradores .xlsx` | Matriz anual Excel | Año 1 (Ciclo I y Ciclo II) | 20 (`10` + `10`) |
| 2 | `Año 2  Proyectos integradores .xlsx` | Matriz anual Excel | Año 2 (Ciclo III y Ciclo IV) | 30 (`16` + `14`) |
| 3 | `Año 3 Proyectos integradores .xlsx` | Matriz anual Excel | Año 3 (Ciclo V y Ciclo VI) | 36 (`18` + `18`) |
| 4 | `Año 4 Proyectos integradores 18_02_2025.xlsx` | Matriz anual Excel | Año 4 (Ciclo VII y Ciclo VIII) | 28 (`16` + `12`) |
| 5 | `2020 - 2024 Proyectos integradores.docx` | Fundamentación Word | Años 1 a 4 (Sección 6.6.2) | 4 tablas de proyecto |
| 6 | `Copia de 03 Proyectos INTEGRADORES.docx` | Fundamentación Word | Años 1 a 4 (Sección 6.6.2) | 4 tablas de proyecto |
| **Total** | **Conciliación exacta** | | **4 años · 8 ciclos (I–VIII)** | **114 posiciones de matriz** |

### 2.1 Agrupación documental por programa y coordenadas de cabeceras combinadas

Cada una de las 114 posiciones documentales toma su agrupación de la fila de cabecera y sus celdas combinadas (`mergeCells`) en la única hoja (`Hoja 1`) de cada matriz Excel:

| Año | Ciclo | Fila Cabecera | Columnas FG | Columnas Inicial | Columnas Primaria | Columnas Primaria EIB | Formación General | Inicial | Primaria | Primaria EIB | Total |
| :---: | :---: | ---: | :---: | :---: | :---: | :---: | ---: | ---: | ---: | ---: | ---: |
| 1 | I | `6` | `C:G` | `H` | `I` | `J:L` | 5 | 1 | 1 | 3 | **10** |
| 1 | II | `16` | `C:G` | `H` | `I` | `J:L` | 5 | 1 | 1 | 3 | **10** |
| 2 | III | `6` | `C:E` | `F:I` | `J:M` | `N:R` | 3 | 4 | 4 | 5 | **16** |
| 2 | IV | `16` | `C:F` | `G:I` | `J:L` | `M:P` | 4 | 3 | 3 | 4 | **14** |
| 3 | V | `6` | `C:D` | `E:I` | `J:N` | `O:T` | 2 | 5 | 5 | 6 | **18** |
| 3 | VI | `18` | `C:D` | `E:I` | `J:N` | `O:T` | 2 | 5 | 5 | 6 | **18** |
| 4 | VII | `5` | `C:D` | `E:H` | `I:M` | `N:R` | 2 | 4 | 5 | 5 | **16** |
| 4 | VIII | `17` | `C:D` | `E:G` | `H:J` | `K:N` | 2 | 3 | 3 | 4 | **12** |
| **Total** | **`I–VIII`** | | | | | | **25** | **26** | **27** | **36** | **114** |

### 2.2 Las 9 celdas de Competencias con valores numéricos anómalos de 5 dígitos

Todas provienen de celdas con formato numérico personalizado de fecha en Excel (`numFmtId=164/165`, `formatCode="d, m, yy"`). Se registran por identidad documental (`archivo`, `hoja`, `celda_competencias` y `celda_curso` de la misma columna) con su valor bruto original y formato Excel, señaladas para revisión institucional sin reconstrucción especulativa:

| # | Pos. Global | Año | Ciclo | Archivo Fuente | Celda Comp. | Celda Curso | Valor Bruto | Formato Excel | Curso de la Misma Columna | Agrupación |
| :---: | :---: | :---: | :---: | :--- | :---: | :---: | :---: | :---: | :--- | :--- |
| 1 | `#11` | 1 | `II` | `Año 1 Proyectos integradores .xlsx` | `C18` | `C17` | `41185.0` | `d, m, yy` | `Práctica e investigación II` | Formación general |
| 2 | `#13` | 1 | `II` | `Año 1 Proyectos integradores .xlsx` | `E18` | `E17` | `40424.0` | `d, m, yy` | `Comunicación oral en la Educación Superior` | Formación general |
| 3 | `#14` | 1 | `II` | `Año 1 Proyectos integradores .xlsx` | `F18` | `F17` | `40756.0` | `d, m, yy` | `Resolución de problemas matemáticos II` | Formación general |
| 4 | `#22` | 2 | `III` | `Año 2  Proyectos integradores .xlsx` | `D8` | `D7` | `40424.0` | `d, m, yy` | `Desarrollo Personal II` | Formación general |
| 5 | `#23` | 2 | `III` | `Año 2  Proyectos integradores .xlsx` | `E8` | `E7` | `41189.0` | `d, m, yy` | `Arte, Creatividad y Aprendizaje` | Formación general |
| 6 | `#38` | 2 | `IV` | `Año 2  Proyectos integradores .xlsx` | `D18` | `D17` | `39236.0` | `d, m, yy` | `Deliberación y Participación` | Formación general |
| 7 | `#39` | 2 | `IV` | `Año 2  Proyectos integradores .xlsx` | `E18` | `E17` | `41091.0` | `d, m, yy` | `Ciencia y Epistemologías` | Formación general |
| 8 | `#52` | 3 | `V` | `Año 3 Proyectos integradores .xlsx` | `D8` | `D7` | `41189.0` | `d, m, yy` | `Literatura y Sociedad en Contextos Diversos` | Formación general |
| 9 | `#60` | 3 | `V` | `Año 3 Proyectos integradores .xlsx` | `L8` | `L7` | `40727.0` | `d, m, yy` | `Aprendizaje de la Comunicación I` | Primaria |

### 2.3 Los 5 hitos documentados de Primer Año (Ciclos I–II)

Documentados literalmente en `2020 - 2024 Proyectos integradores.docx` y `Copia de 03 Proyectos INTEGRADORES.docx` exclusivamente para el Proyecto Integrador 1 (`hitos_documentados === null` en Años 2, 3 y 4, donde se mantiene el estado explícito `Pendiente de definición institucional`):

| Hito | Contenido documentado | Plazo relativo documentado |
| :---: | :--- | :--- |
| **1** | Definición del espacio de exploración: tema, justificación y actores claves; registro en intranet, revisión docente y retroalimentación. | `No se precisa fecha.` |
| **2** | Socialización de observaciones y reajuste. | `Un mes antes de finalizar el primer semestre.` |
| **3** | Sustentación de la propuesta. | `No se precisa fecha.` |
| **4** | Socialización de avances. | `A mediados del siguiente semestre.` |
| **5** | Entrega final. | `Tres semanas antes de concluir el II ciclo.` |

### 2.4 Diferencias documentales preservadas entre los Word y las Matrices
- **Año 1 (Ciclos I–II):** En los Word se titula *«Escuela y Territorio: Diagnóstico Socio-Cultural»*; en la matriz Excel se denomina en Ciclo I *«Escuela y territorio: la escuela situada»* (`C4`) y en Ciclo II *«Territorio»* (`C14`), con producto integrador *«Mapa parlante del territorio de una Institución Educativa»*. `Copia de 03` explicita la sabiduría comunitaria sobre el espacio sagrado (lagunas, puquios, apus protectores).
- **Año 2 (Ciclos III–IV):** En los Word se titula *«Prácticas de crianza y educación intercultural.»*; en la matriz se denomina *«Prácticas de crianza interculturales»* y define como producto integrador el *«Podcast sobre la comparación de prácticas de crianza entre familias con procedencias culturales distintas.»*.
- **Año 3 (Ciclos V–VI):** En el Word `2020 - 2024` se denomina *«Modos de vida, valores y cultura: tecnologías, medios de comunicación y textos escolares..»*; en `Copia de 03` cambia el título a *«Saber y conocer: formas de aprendizaje»* e introduce los aprendizajes de los abuelos, aunque conserva pegado el pasaje previo sobre medios y textos escolares; la matriz de 3er año consolida el propósito en torno a las experiencias lectoescritoras y procesos de aprendizaje de los abuelos y el producto *«Material didáctico concreto: juegos, títeres, cuentos»*.
- **Año 4 (Ciclos VII–VIII):** En los Word se denomina *«Rituales escolares: configuración de la escuela y necesidades para su transformación.»* y plantea un plan de tutoría o materiales específicos; en la matriz del `18_02_2025` se denomina *«Rituales escolares: oportunidades de cambio para la configuración de la escuela y necesidades para su transformación.»* con el producto integrador *«Feria de didácticas escolares alternativas: ABP, Proyectos de Aprendizaje»*.

---

## 3. Arquitectura del modelo funcional implementado

Migración aditiva: `database/migrations/2026_11_01_080000_create_ayllu_mesa_proyectos_integradores_tables.php`

- **A. Definición y versión del proyecto (`app/Models/Ayllu/AylluPiDefinicion.php`):** Almacena las versiones documentales por año (`matriz_excel`, `word_2020_2024`, `word_copia_03` y revisiones futuras). Las versiones en uso quedan bloqueadas contra sobrescritura; cualquier cambio académico genera una nueva versión trazable.
- **B. Ejecución anual (`app/Models/Ayllu/AylluPiEjecucion.php`):** Relaciona una definición curricular adoptada con el año lectivo (`2026`), período (`PeriodoActual`), cohorte y par de ciclos (`I–II`, `III–IV`, `V–VI`, `VII–VIII`).
- **C. Aporte de curso (`app/Models/Ayllu/AylluPiAporteCurso.php`):** Representa las 114 posiciones documentales con su coordenada exacta de archivo, hoja y celda, agrupación de programa (`FORMACION_GENERAL`, `INICIAL`, `PRIMARIA`, `PRIMARIA_EIB`), correspondencia con el catálogo `cursos`, acuerdos operativos de la mesa y estado real de desarrollo (`sin_registrar` → `en_desarrollo` → `para_revision_compartida` → `revisado` → `integrado_al_producto_anual`).
- **D. Desarrollo y evidencia versionada (`app/Models/Ayllu/AylluPiEvidenciaVersion.php`):** Historial numerado (`v1`, `v2`...) con autoría, audiencia explícita, reconocimiento de autoría comunitaria, restricciones de circulación (`ninguna`, `uso_interno_ciclo`, `reserva_comunitaria`, `autorizacion_retirada`) y almacenamiento de archivos en el disco privado `local` (`storage/app/private/ayllu_pi_evidencias/`), fuera de `storage/app/public/` y de `public/`.
- **E. Articulación y bitácora (`app/Models/Ayllu/AylluPiArticulacion.php`, `app/Models/Ayllu/AylluPiBitacoraMesa.php`):** Dependencias acordadas entre cursos y bitácora cronológica filtrada por audiencia.
- **F. Síntesis comunitaria y auditoría (`app/Models/Ayllu/AylluPiSintesisComunitaria.php`, `app/Models/Ayllu/AylluPiAuditoriaEvento.php`):** Flujo editorial (`pendiente_revision_editorial` → `aprobada_publicada`) que nunca amplía permisos de archivos privados originales, y bitácora de auditoría de eventos.

---

## 4. Matriz de permisos por acción y ámbito (`app/Services/AylluAuthorizationService.php`)

| Acción | Coordinación / Autoridad | Docente asignado al aporte (`curso_docente` o `ayllu_pi_asignaciones_mesa` activa) | Docente de otro curso del mismo ciclo | Docente ajeno (de otro ciclo/año o con asignación revocada) | Estudiante del ciclo y programa correspondiente | Estudiante de otro ciclo o de otro programa (ej. Primaria vs Primaria EIB) |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Ver matriz curricular (4 años)** | Sí | Sí | Sí | Sí (consulta curricular) | Sí | Sí (consulta general) |
| **Abrir ficha del aporte (`GET /aportes/{id}`)** | Sí | Sí | Sí | **No (`403`)** | Sí (vista estudiantil sin deliberaciones docentes) | **No (`403`)** (salvo `FORMACION_GENERAL` común de su ciclo) |
| **Ver deliberaciones (`mesa_docente_ciclo`)** | Sí | Sí | Sí | **No (`403`)** | **No (oculto/bloqueado)** | **No (`403`)** |
| **Editar ajuste operativo del aporte** | Sí | Sí | **No (`403`)** | **No (`403`)** | **No (`403`)** | **No (`403`)** |
| **Registrar versión de evidencia** | Sí | Sí | **No (`403`)** | **No (`403`)** | Sí (en su curso/equipo, audiencia `equipo_estudiantes`) | **No (`403`)** |
| **Comentar en bitácora de la mesa del ciclo** | Sí | Sí | Sí | **No (`403`)** | **No (`403`)** | **No (`403`)** |
| **Revisar versión (`revisado` / `devuelto_a_desarrollo`)** | Sí | Sí | Sí (revisión entre pares del ciclo) | **No (`403`)** | **No (`403`)** | **No (`403`)** |
| **Integrar versión al producto anual** | Sí | Tutor del ciclo / Coordinador | **No (`403`)** | **No (`403`)** | **No (`403`)** | **No (`403`)** |
| **Descargar archivo privado de evidencia** | Según restricción | Sí | Sí (si audiencia es mesa/ciclo y no es `borrador_personal`) | **No (`403`)** | Solo si audiencia es `equipo_estudiantes` (de su equipo) o `ciclo_completo` (de su programa/común) y sin `reserva_comunitaria` | **No (`403`)** |
| **Aprobar definición / Adoptar versión / Revocar asignación** | Sí | **No (`403`)** | **No (`403`)** | **No (`403`)** | **No (`403`)** | **No (`403`)** |
| **Aprobar y publicar síntesis comunitaria** | Sí | Solicita (`pendiente_revision_editorial`) | Solicita | **No (`403`)** | **No (`403`)** | **No (`403`)** |

---

## 5. Procedimiento de instalación, importación, reparación técnica idempotente y reversión segura

### Ejecución, vista previa y reparación técnica verificable
```bash
# 1. Ejecutar migraciones aditivas
php artisan migrate

# 2. Previsualizar conciliación documental sin escribir en base de datos
php artisan ayllu:importar-proyectos --preview

# 3. Ejecutar importación transaccional e idempotente (114 entradas, 10 versiones, 9 anomalías)
php artisan ayllu:importar-proyectos --lectivo=2026

# 4. Previsualizar si existen registros persistidos con diferencias técnicas de correspondencia/hitos
php artisan ayllu:importar-proyectos --preview-repair

# 5. Aplicar reparación técnica acotada, transaccional e idempotente (preservando evidencias, versiones e historial)
php artisan ayllu:importar-proyectos --repair

# 6. Ejecutar suites de pruebas automatizadas
php artisan test tests/Feature/AylluMesaProyectosIntegradoresTest.php tests/Feature/AylluPukllaMvpTest.php tests/Feature/SeguimientoIntegracionRolesTest.php
```

### Reversión segura del incremento
La migración `database/migrations/2026_11_01_080000_create_ayllu_mesa_proyectos_integradores_tables.php` bloquea por defecto cualquier `migrate:rollback` si existen registros en las tablas `ayllu_pi_*` o si el entorno es `production`. En entorno local de pruebas, si se desea revertir explícitamente el incremento sin tocar ninguna tabla institucional existente:
```bash
AYLLU_ALLOW_DESTRUCTIVE_ROLLBACK=true php artisan migrate:rollback --step=1
```
