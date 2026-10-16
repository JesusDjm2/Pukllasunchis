<?php

namespace App\Services;

use App\Models\Alumno;
use App\Models\PeriodoActual;
use App\Models\PeriodoActualPpd;
use App\Models\ppd;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Datos demográficos de los estudiantes (fichas FID `alumnos` y PPD `ppds`).
 *
 * Las fichas guardan las mismas respuestas con escrituras distintas según la versión del
 * formulario (códigos y frases, "Sí"/"1", JSON y CSV, mayúsculas, placeholders como "Elegir",
 * números imposibles). Aquí se normaliza todo a categorías limpias y se entrega a la vista una
 * fila compacta por estudiante: la vista filtra, cruza y grafica en el navegador sin recargar.
 */
class DatosDemograficos
{
    public const SECCIONES = [
        'perfil' => ['Perfil personal', 'fa-user'],
        'origen' => ['Origen y residencia', 'fa-map-marker-alt'],
        'identidad' => ['Identidad y lengua', 'fa-language'],
        'economia' => ['Familia y economía', 'fa-hand-holding-usd'],
        'vivienda' => ['Vivienda y servicios', 'fa-home'],
        'conectividad' => ['Conectividad y estudio', 'fa-wifi'],
        'salud' => ['Salud', 'fa-heartbeat'],
        'formacion' => ['Formación y motivación', 'fa-graduation-cap'],
        'cultura' => ['Hábitos y cultura', 'fa-book-open'],
    ];

    private const DEPARTAMENTOS = [
        'Amazonas', 'Áncash', 'Apurímac', 'Arequipa', 'Ayacucho', 'Cajamarca', 'Callao', 'Cusco',
        'Huancavelica', 'Huánuco', 'Ica', 'Junín', 'La Libertad', 'Lambayeque', 'Lima', 'Loreto',
        'Madre de Dios', 'Moquegua', 'Pasco', 'Piura', 'Puno', 'San Martín', 'Tacna', 'Tumbes', 'Ucayali',
    ];

    private const SITUACIONES = ['Activo', 'Licencia', 'Egresado', 'Retirado', 'Inhabilitado'];

    /**
     * @return array{secciones: array, dims: array, filas: array, periodos: array}
     */
    /** Datos del proceso de titulación por user_id (solo en la vista de Titulación). */
    private ?array $titulacion = null;

    /**
     * Misma herramienta, limitada a quienes están en el proceso de titulación (registrados y
     * activos que pueden iniciar), con una sección propia del proceso. $poblacion:
     * [user_id => ['condicion', 'estado', 'vigencia', 'promocion', 'orden_estados']].
     */
    public function construirTitulacion(array $poblacion): array
    {
        $this->titulacion = $poblacion;

        return $this->construir();
    }

    public function construir(): array
    {
        $catalogo = $this->catalogo();
        $registros = [];
        $soloUsuarios = $this->titulacion !== null ? array_keys($this->titulacion) : null;

        $fid = Alumno::with(['user.roles', 'programa', 'ciclo', 'matriculas:id,alumno_id,periodo_actual_id'])
            ->whereHas('user')
            ->when($soloUsuarios !== null, fn ($q) => $q->whereIn('user_id', $soloUsuarios))
            ->get();
        foreach ($fid as $a) {
            $registros[] = $this->registro($a, 'FID', $catalogo,
                $a->matriculas->pluck('periodo_actual_id')->unique()->map(fn ($id) => 'f' . $id)->values()->all());
        }

        $ppd = ppd::with(['user.roles', 'programa', 'ciclo', 'matriculas:id,ppd_id,periodo_actual_ppd_id,semestre'])
            ->whereHas('user')
            ->when($soloUsuarios !== null, fn ($q) => $q->whereIn('user_id', $soloUsuarios))
            ->get();
        foreach ($ppd as $p) {
            $periodos = [];
            foreach ($p->matriculas as $m) {
                $periodos[] = 'p' . $m->periodo_actual_ppd_id;
                $periodos[] = 'p' . $m->periodo_actual_ppd_id . 's' . ((int) $m->semestre ?: 1);
            }
            $registros[] = $this->registro($p, 'PPD', $catalogo, array_values(array_unique($periodos)));
        }

        $this->canonizarLugares($registros, ['departamento', 'provincia', 'distrito']);

        // Una persona puede tener ficha FID y PPD: en Titulación cuenta una sola vez.
        if ($soloUsuarios !== null) {
            $registros = array_values(collect($registros)->unique('id')->all());
        }
        $secciones = ($this->titulacion !== null ? ['titulacion' => ['Proceso de titulación', 'fa-user-graduate']] : []) + self::SECCIONES;

        return array_merge([
            'secciones' => collect($secciones)->map(fn ($s, $k) => ['key' => $k, 'titulo' => $s[0], 'icono' => $s[1]])->values()->all(),
            'periodos' => $this->periodos(),
        ], $this->codificar($catalogo, $registros));
    }

    // ── Catálogo de variables ─────────────────────────────────────────────────────────────

    /**
     * Cada variable: sección, título, pregunta del formulario, tipo (cat|multi), forma del gráfico
     * (barra|columna|dona), orden fijo para las ordinales y la función que extrae el valor limpio.
     */
    private function catalogo(): array
    {
        $si = fn ($campo) => fn ($f) => self::siNo($f->$campo);
        $tit = fn (string $campo) => fn ($f, $c) => $c['tit'][$campo] ?? null;
        $ordenEstados = $this->titulacion !== null ? (collect($this->titulacion)->first()['orden_estados'] ?? null) : null;
        $ordenPromociones = $this->titulacion !== null ? (collect($this->titulacion)->first()['orden_promociones'] ?? null) : null;

        return ($this->titulacion === null ? [] : [
            'tit_condicion' => ['titulacion', 'Condición', 'Egresados y estudiantes que ya pueden iniciar su titulación', 'cat', 'dona', ['Egresado(a)', 'Estudiante con proceso iniciado', 'Estudiante que puede iniciar'], $tit('condicion')],
            'tit_estado' => ['titulacion', 'Estado de titulación', 'Se calcula de las RD y los grados inscritos en SUNEDU', 'cat', 'barra', $ordenEstados, $tit('estado')],
            'tit_vigencia' => ['titulacion', 'Vigencia del PTI', 'Solo de quienes tienen PTI y aún no se titulan', 'cat', 'barra', ['Por vencer (≤ 4 meses)', 'Vence en ≤ 12 meses', 'Vigente', 'Vencido', 'Sin fecha'], $tit('vigencia')],
            'tit_promocion' => ['titulacion', 'Promoción', 'Año de egreso (o «En curso» si aún estudia)', 'cat', 'columna', $ordenPromociones, $tit('promocion')],
        ]) + [
            // Perfil personal
            'modalidad' => ['perfil', 'Modalidad', 'FID: formación inicial · PPD: profesionalización', 'cat', 'dona', ['FID', 'PPD'], fn ($f, $c) => $c['tipo']],
            'situacion' => ['perfil', 'Situación académica', 'Estado de la cuenta en el sistema', 'cat', 'barra', self::SITUACIONES, fn ($f, $c) => $c['situacion']],
            'programa' => ['perfil', 'Programa de estudios', null, 'cat', 'barra', null, fn ($f) => $f->programa?->nombre],
            'ciclo' => ['perfil', 'Ciclo', 'Ciclo que cursa actualmente', 'cat', 'columna', array_map(fn ($r) => 'Ciclo ' . $r, ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X']), fn ($f) => self::ciclo($f->ciclo?->nombre)],
            'genero' => ['perfil', 'Género', null, 'cat', 'dona', ['Femenino', 'Masculino'], fn ($f) => self::mapa($f->genero, ['/^f/' => 'Femenino', '/^m/' => 'Masculino'], null)],
            'edad' => ['perfil', 'Edad', 'Calculada a partir de la fecha de nacimiento', 'cat', 'columna', ['Hasta 20', '21 a 25', '26 a 30', '31 a 35', '36 a 45', '46 a más'], fn ($f, $c) => self::tramo($c['edad'], [[0, 20, 'Hasta 20'], [21, 25, '21 a 25'], [26, 30, '26 a 30'], [31, 35, '31 a 35'], [36, 45, '36 a 45'], [46, 200, '46 a más']])],
            'estado_civil' => ['perfil', 'Estado civil', null, 'cat', 'barra', ['Soltero(a)', 'Conviviente', 'Casado(a)', 'Separado(a)', 'Divorciado(a)', 'Viudo(a)'], fn ($f) => self::mapa($f->estado_civil, ['/^solter/' => 'Soltero(a)', '/^casad/' => 'Casado(a)', '/^convivien/' => 'Conviviente', '/^separad/' => 'Separado(a)', '/^divorciad/' => 'Divorciado(a)', '/^viud/' => 'Viudo(a)'])],
            'hijos' => ['perfil', 'Número de hijos', null, 'cat', 'columna', ['Sin hijos', '1', '2', '3', '4 o más'], fn ($f) => self::tramo(self::entero($f->num_hijos, 0, 15), [[0, 0, 'Sin hijos'], [1, 1, '1'], [2, 2, '2'], [3, 3, '3'], [4, 15, '4 o más']])],

            // Origen y residencia
            'departamento' => ['origen', 'Departamento de residencia', null, 'cat', 'barra', null, fn ($f) => self::departamento($f->departamento)],
            'provincia' => ['origen', 'Provincia de residencia', null, 'cat', 'barra', null, fn ($f) => self::lugar($f->provincia)],
            'distrito' => ['origen', 'Distrito de residencia', null, 'cat', 'barra', null, fn ($f) => self::lugar($f->distrito)],
            'permanencia' => ['origen', 'Permanencia en la vivienda', '¿Dónde vive durante el periodo de estudios?', 'cat', 'barra', ['Vive en Cusco ciudad', 'En Cusco de lunes a viernes', 'Vive en comunidad y viaja a Cusco'], fn ($f) => self::mapa($f->permanencia_vivienda, ['/permanente/' => 'Vive en Cusco ciudad', '/lunes a viernes/' => 'En Cusco de lunes a viernes', '/comunidad/' => 'Vive en comunidad y viaja a Cusco'])],
            'procedencia' => ['origen', 'Procedencia familiar', '¿Su familia procede de una comunidad?', 'cat', 'barra', ['Vive en la comunidad', 'Sus padres viven en la comunidad', 'Sus abuelos viven en la comunidad', 'Ya no viven en la comunidad', 'Familia de zona urbana'], fn ($f) => self::mapa($f->procedencia_familiar, ['/\byo\b|^vivo en/' => 'Vive en la comunidad', '/padres/' => 'Sus padres viven en la comunidad', '/abuelos/' => 'Sus abuelos viven en la comunidad', '/ya no viv|no vivimos/' => 'Ya no viven en la comunidad', '/urban/' => 'Familia de zona urbana'])],

            // Identidad y lengua
            'autoidentificacion' => ['identidad', 'Autoidentificación étnica', 'Por sus costumbres y antepasados, usted se considera…', 'cat', 'barra', null, fn ($f) => self::mapa($f->te_consideras, ['/quechua/' => 'Quechua', '/aymara|aimara/' => 'Aimara', '/otro pueblo/' => 'Otro pueblo indígena', '/amazon|nativo/' => 'Nativo de la Amazonía', '/negro|moreno|zambo|afro/' => 'Afroperuano', '/mestiz/' => 'Mestizo', '/blanco/' => 'Blanco', '/no sabe|no responde/' => 'No sabe / no responde'])],
            'lengua_materna' => ['identidad', 'Lengua materna', 'Primera lengua que aprendió', 'cat', 'barra', ['Quechua', 'Castellano y quechua', 'Castellano', 'Otra'], fn ($f) => self::lengua($f->lengua_1)],
            'segunda_lengua' => ['identidad', 'Segunda lengua', null, 'cat', 'barra', ['Quechua', 'Castellano', 'Otra'], fn ($f) => self::lengua($f->lengua_2)],
            'habla_quechua' => ['identidad', 'Habla quechua', 'Quechua como lengua materna o segunda lengua', 'cat', 'dona', ['Sí', 'No'], fn ($f) => self::hablaQuechua($f)],

            // Familia y economía
            'nivel_socioeconomico' => ['economia', 'Nivel socioeconómico', 'Sector con el que se identifica', 'cat', 'columna', ['Popular', 'Medio', 'Medio alto', 'Alto'], fn ($f) => self::mapa($f->sector_socioeconomico, ['/^popular/' => 'Popular', '/medio alto/' => 'Medio alto', '/^medio/' => 'Medio', '/^alto/' => 'Alto'])],
            'ingreso' => ['economia', 'Ingreso mensual familiar', null, 'cat', 'columna', ['Menos de S/ 500', 'S/ 501 – 930', 'S/ 931 – 1 200', 'S/ 1 201 – 2 000', 'Más de S/ 2 000'], fn ($f) => self::mapa($f->ingreso_mensual, ['/menos/' => 'Menos de S/ 500', '/501/' => 'S/ 501 – 930', '/931/' => 'S/ 931 – 1 200', '/1201/' => 'S/ 1 201 – 2 000', '/mas de/' => 'Más de S/ 2 000'], null)],
            'quien_mantiene' => ['economia', 'Quién lo mantiene', '¿Quién cubre sus gastos?', 'cat', 'barra', null, fn ($f) => self::mapa($f->quien_mantiene, ['/^yo\b(?!.*conyuge)/' => 'Se mantiene solo(a)', '/padre y madre|mis padres|ambos/' => 'Padre y madre', '/^madre/' => 'Madre', '/^padre/' => 'Padre', '/conyuge|esposo/' => 'Cónyuge'])],
            'convivientes' => ['economia', 'Con quién vive', null, 'cat', 'barra', null, fn ($f) => self::convivientes($f->convivientes)],
            'dependientes_ninos' => ['economia', 'Niños dependientes en el hogar', null, 'cat', 'columna', ['Ninguno', '1', '2', '3 o más'], fn ($f) => self::tramo(self::entero($f->cant_dependientes_child, 0, 20), [[0, 0, 'Ninguno'], [1, 1, '1'], [2, 2, '2'], [3, 20, '3 o más']])],
            'dependientes_mayores' => ['economia', 'Adultos mayores dependientes', null, 'cat', 'columna', ['Ninguno', '1', '2', '3 o más'], fn ($f) => self::tramo(self::entero($f->cant_dependientes_old, 0, 20), [[0, 0, 'Ninguno'], [1, 1, '1'], [2, 2, '2'], [3, 20, '3 o más']])],
            'trabaja' => ['economia', 'Trabaja actualmente', null, 'cat', 'dona', ['Sí', 'No', 'Todavía no'], fn ($f) => self::mapa($f->trabajas, ['/^(si|1)$/' => 'Sí', '/todavia/' => 'Todavía no', '/^(no|0)$/' => 'No'], null)],
            'sector_laboral' => ['economia', 'Sector en el que trabaja', 'Respuesta libre agrupada por rubro', 'cat', 'barra', null, fn ($f) => self::sectorLaboral($f->sector_laboral)],
            'horas_trabajo' => ['economia', 'Horas de trabajo a la semana', null, 'cat', 'columna', ['No labora', '1 a 10', '11 a 20', '21 a 30', '31 a 40', 'Más de 40'], fn ($f) => self::tramo(self::entero($f->hrs_laboradas_sem, 0, 100), [[0, 0, 'No labora'], [1, 10, '1 a 10'], [11, 20, '11 a 20'], [21, 30, '21 a 30'], [31, 40, '31 a 40'], [41, 100, 'Más de 40']])],
            'ayuda_familiar' => ['economia', 'Recibe ayuda económica familiar', null, 'cat', 'dona', ['Sí', 'No'], $si('ayuda_economica')],
            'apoyo_formacion' => ['economia', 'Apoyo para su formación', 'Tipo de apoyo que recibe en su formación docente', 'cat', 'barra', ['Beca integral', 'Beca parcial', 'Otro', 'Ninguno'], fn ($f) => self::mapa($f->tipo_apoyo_formacion, ['/ningun/' => 'Ninguno', '/integral/' => 'Beca integral', '/parcial/' => 'Beca parcial'])],
            'beca_escolar' => ['economia', 'Estudió con beca en el colegio', null, 'cat', 'barra', ['Beca total', 'Beca parcial', 'Sí (sin detalle)', 'No'], fn ($f) => self::mapa($f->estudio_beca, ['/total/' => 'Beca total', '/parcial/' => 'Beca parcial', '/^(si|1)$/' => 'Sí (sin detalle)', '/^(no|0)$/' => 'No'], null)],

            // Vivienda y servicios
            'tipo_vivienda' => ['vivienda', 'Tipo de vivienda', null, 'cat', 'barra', null, fn ($f) => self::texto($f->tipo_vivienda)],
            'tenencia_vivienda' => ['vivienda', 'Tenencia de la vivienda', null, 'cat', 'barra', null, fn ($f) => self::texto($f->situacion_vivienda)],
            'material_vivienda' => ['vivienda', 'Material de la vivienda', null, 'cat', 'barra', null, fn ($f) => self::texto($f->material_vivienda)],
            'dormitorios' => ['vivienda', 'Dormitorios en la vivienda', null, 'cat', 'columna', ['1', '2', '3', '4', '5 o más'], fn ($f) => self::tramo(self::entero($f->dormitorios_vivienda, 1, 20), [[1, 1, '1'], [2, 2, '2'], [3, 3, '3'], [4, 4, '4'], [5, 20, '5 o más']])],
            'agua' => ['vivienda', 'Horas de agua al día', null, 'cat', 'columna', ['Sin servicio', '1 a 5 h', '6 a 11 h', '12 a 23 h', '24 horas'], fn ($f) => self::horasServicio($f->hrs_disponibles_agua)],
            'luz' => ['vivienda', 'Horas de luz al día', null, 'cat', 'columna', ['Sin servicio', '1 a 5 h', '6 a 11 h', '12 a 23 h', '24 horas'], fn ($f) => self::horasServicio($f->hrs_disponibles_luz)],
            'bienes' => ['vivienda', 'Bienes en el hogar', 'Respuesta múltiple', 'multi', 'barra', null, fn ($f) => self::multiple($f->bienes_vivienda, ['Cocina a gas', 'Cocina eléctrica' => 'Cocina electrica', 'Televisor', 'Refrigeradora', 'Lavadora', 'Computadora', 'Horno microondas', 'DVD', 'Mini componente', 'Aspiradora', 'Secadora de ropa', 'Cámara de video', 'Juego de video', 'Bicicleta', 'Motocicleta', 'Automóvil', 'Ninguno' => 'Ninguna de las anteriores'])],
            'otros_servicios' => ['vivienda', 'Otros servicios en el hogar', 'Respuesta múltiple', 'multi', 'barra', null, fn ($f) => self::multiple($f->otros_servicios, ['Internet' => 'Servicio de Internet', 'Cable' => 'Servicio de cable', 'Teléfono fijo' => 'Servicio de teléfono', 'Empleado(a) doméstico', 'Ninguno' => 'Ninguna de las anteriores'])],

            // Conectividad y estudio
            'internet_casa' => ['conectividad', 'Internet en casa', '¿Tienes acceso a internet en casa?', 'cat', 'dona', ['Sí', 'No'], $si('internet')],
            'lugar_conexion' => ['conectividad', 'Dónde se conecta (sin internet en casa)', null, 'cat', 'barra', null, fn ($f) => self::siNo($f->internet) === 'No' ? self::texto($f->internet_lugar) : null],
            'tipo_conexion' => ['conectividad', 'Tipo de conexión', null, 'cat', 'barra', null, fn ($f) => self::mapa($f->servicio_internet, ['/movil/' => 'Internet móvil', '/fijo/' => 'Internet fijo', '/satelit/' => 'Satelital'])],
            'dispositivo' => ['conectividad', 'Dispositivo para estudiar', null, 'cat', 'barra', null, fn ($f) => self::texto($f->dispositivo_internet)],
            'uso_dispositivo' => ['conectividad', 'Uso del dispositivo', null, 'cat', 'dona', ['Propio', 'Compartido'], fn ($f) => self::mapa($f->propio_compartido, ['/^1$/' => 'Propio', '/^0$/' => 'Compartido'], null)],
            'horas_estudio' => ['conectividad', 'Horas dedicadas al estudio', 'Declaradas por el estudiante', 'cat', 'columna', ['1 a 2', '3 a 4', '5 a 6', '7 a 10', 'Más de 10'], fn ($f) => self::tramo(self::entero($f->num_hrs_estudio, 1, 60), [[1, 2, '1 a 2'], [3, 4, '3 a 4'], [5, 6, '5 a 6'], [7, 10, '7 a 10'], [11, 60, 'Más de 10']])],
            'forma_estudio' => ['conectividad', 'Cómo prefiere estudiar', null, 'cat', 'barra', null, fn ($f) => self::mapa($f->forma_estudio, ['/grupo/' => 'En grupo', '/^solo/' => 'Solo(a)', '/profesor/' => 'Con profesor particular'])],

            // Salud
            'seguro' => ['salud', 'Seguro de salud', null, 'cat', 'barra', null, fn ($f) => self::mapa($f->tipo_seguro, ['/sis|integral/' => 'SIS', '/essalud/' => 'EsSalud', '/ningun/' => 'Sin seguro', '/ejercito|marina|fap|pnp|policia/' => 'FF. AA. / PNP'])],
            'problema_salud' => ['salud', 'Presenta algún problema de salud', null, 'cat', 'dona', ['Sí', 'No'], $si('problemas_salud')],
            'consulta_medica' => ['salud', 'Consulta médica en los últimos 12 meses', null, 'cat', 'dona', ['Sí', 'No'], $si('ultima_consulta')],
            'familiar_salud' => ['salud', 'Familiar con problema de salud', 'Algún miembro del hogar presenta un problema de salud', 'cat', 'dona', ['Sí', 'No'], $si('familiar_salud')],

            // Formación y motivación
            'preparacion' => ['formacion', 'Preparación para postular', null, 'cat', 'barra', null, fn ($f) => self::mapa($f->tipo_preparacion, ['/academia/' => 'Academia', '/otro iesp|eesp/' => 'Centro pre de otro IESP/EESP', '/centro pre/' => 'Centro pre', '/ningun/' => 'Ninguna'])],
            'motivo_eesp' => ['formacion', 'Por qué eligió la EESP Pukllasunchis', null, 'cat', 'barra', null, fn ($f) => self::texto($f->motivo_estudio_eesp)],
            'motivo_docencia' => ['formacion', 'Por qué estudia docencia', null, 'cat', 'barra', null, fn ($f) => self::texto($f->motivo_docencia)],
            'gestion_procedencia' => ['formacion', 'Gestión de la institución de procedencia', 'Solo PPD: institución donde obtuvo su título anterior', 'cat', 'dona', ['Pública', 'Privada'], fn ($f, $c) => $c['tipo'] === 'PPD' ? self::mapa($f->gestion_institucion, ['/public/' => 'Pública', '/privad/' => 'Privada'], null) : null],
            'carrera_procedencia' => ['formacion', 'Carrera de procedencia', 'Solo PPD', 'cat', 'barra', null, fn ($f, $c) => $c['tipo'] === 'PPD' ? self::mapa($f->carrera_procedencia, ['/inicial/' => 'Educación Inicial', '/primaria/' => 'Educación Primaria', '/educa|pedag|docen|profe/' => 'Educación (otra o sin especificar)']) : null],

            // Hábitos y cultura
            'lectura' => ['cultura', 'Frecuencia de lectura', 'Sin contar textos académicos', 'cat', 'columna', ['Todos los días', '1 vez por semana', 'Cada 15 días', '1 vez al mes', 'Menos de 1 vez al mes'], fn ($f) => self::mapa($f->frecuencia_lectura, ['/todos los dias/' => 'Todos los días', '/semana/' => '1 vez por semana', '/15/' => 'Cada 15 días', '/1 vez al mes/' => '1 vez al mes', '/mas de/' => 'Menos de 1 vez al mes'], null)],
            'acceso_lectura' => ['cultura', 'Cómo accede a lo que lee', null, 'cat', 'barra', null, fn ($f) => self::mapa($f->acceso_lectura, ['/internet/' => 'Lee por internet', '/compra/' => 'Las compra', '/trabajo|oficina/' => 'En el trabajo u oficina'])],
            'museos' => ['cultura', 'Última visita a un museo', null, 'cat', 'columna', ['El último mes', 'Últimos 3 meses', 'Últimos 6 meses', 'Últimos 12 meses', 'Hace más de 12 meses'], fn ($f) => self::mapa($f->visitas_museos, ['/ultimo mes/' => 'El último mes', '/3 meses/' => 'Últimos 3 meses', '/6 meses/' => 'Últimos 6 meses', '/mas de 12/' => 'Hace más de 12 meses', '/12 meses/' => 'Últimos 12 meses'], null)],
            'uso_internet' => ['cultura', 'Para qué usa más internet', null, 'cat', 'barra', null, fn ($f) => self::mapa($f->actividades_internet, ['/aprender/' => 'Aprender algo nuevo', '/entreten|musica/' => 'Entretenimiento y música', '/prensa|actualidad/' => 'Informarse de la actualidad', '/correo/' => 'Correo electrónico'])],
            'habilidades' => ['cultura', 'Habilidades artísticas', 'Respuesta múltiple', 'multi', 'barra', null, fn ($f) => self::multiple($f->habilidades, ['Danzas' => 'Danzas (', 'Música' => 'Música (', 'Artes plásticas' => 'Artes pláticas (', 'Literatura' => 'Literatura (', 'Otras' => 'Otros'])],
            'tiempo_libre' => ['cultura', 'Dispone de tiempo libre', 'Para actividades recreativas o culturales', 'cat', 'dona', ['Sí', 'No'], $si('tiempo_libre')],
        ];
    }

    // ── Construcción de filas ─────────────────────────────────────────────────────────────

    private function registro($ficha, string $tipo, array $catalogo, array $periodos): array
    {
        $user = $ficha->user;
        $ctx = [
            'tipo' => $tipo,
            'situacion' => $this->situacion($user, $ficha->ciclo?->nombre),
            'edad' => self::edad($ficha->fecha_nacimiento),
            'tit' => $this->titulacion[$user->id] ?? [],
        ];

        $valores = [];
        foreach ($catalogo as $key => $def) {
            try {
                $valores[$key] = ($def[6])($ficha, $ctx);
            } catch (\Throwable $e) {
                $valores[$key] = null;
            }
        }

        return [
            'id' => $user->id,
            'tipo' => $tipo,
            'nombre' => trim(($ficha->apellidos ?: $user->apellidos) . ', ' . ($ficha->nombres ?: $user->name), ', '),
            'dni' => $ficha->dni ?: $user->dni,
            'programa_id' => $ficha->programa_id,
            'ciclo_id' => $ficha->ciclo_id,
            'edad' => $ctx['edad'],
            'periodos' => $periodos,
            'valores' => $valores,
        ];
    }

    private function situacion($user, ?string $ciclo): string
    {
        $perfil = trim((string) $user->perfil);
        if (in_array($perfil, ['Licencia', 'Egresado', 'Retirado'], true)) {
            return $perfil;
        }
        if ($ciclo && Str::contains(Str::lower($ciclo), 'egresad')) {
            return 'Egresado';
        }

        return $user->roles->contains('name', 'inhabilitado') ? 'Inhabilitado' : 'Activo';
    }

    /** Une escrituras distintas del mismo lugar ("CUSCO", "Cusco", "cusco ") bajo la más cuidada. */
    private function canonizarLugares(array &$registros, array $keys): void
    {
        foreach ($keys as $key) {
            $variantes = [];
            foreach ($registros as $r) {
                if (($v = $r['valores'][$key]) !== null) {
                    $variantes[self::clave($v)][$v] = ($variantes[self::clave($v)][$v] ?? 0) + 1;
                }
            }
            $canon = [];
            foreach ($variantes as $clave => $formas) {
                // Prefiere la forma con tildes; si empatan, la más usada.
                uksort($formas, fn ($a, $b) => [strlen($b) - mb_strlen($b), $formas[$b]] <=> [strlen($a) - mb_strlen($a), $formas[$a]]);
                $canon[$clave] = array_key_first($formas);
            }
            foreach ($registros as &$r) {
                if (($v = $r['valores'][$key]) !== null) {
                    $r['valores'][$key] = $canon[self::clave($v)];
                }
            }
            unset($r);
        }
    }

    /**
     * Codifica cada valor como índice de su categoría: la página recibe ~500 filas de enteros
     * en vez de textos repetidos. Las categorías ordinales mantienen su orden; el resto va por
     * frecuencia.
     */
    private function codificar(array $catalogo, array $registros): array
    {
        $dims = [];
        $indices = [];
        foreach ($catalogo as $key => $def) {
            [$seccion, $titulo, $pregunta, $tipo, $grafico, $orden] = $def;
            $cuenta = [];
            foreach ($registros as $r) {
                foreach ((array) ($r['valores'][$key] ?? []) as $v) {
                    $cuenta[$v] = ($cuenta[$v] ?? 0) + 1;
                }
            }
            arsort($cuenta);
            $presentes = array_map('strval', array_keys($cuenta));
            $cats = $orden
                ? array_values(array_merge(array_values(array_intersect($orden, $presentes)), array_diff($presentes, $orden)))
                : $presentes;
            $indices[$key] = array_flip($cats);
            $dims[] = [
                'key' => $key,
                'seccion' => $seccion,
                'titulo' => $titulo,
                'pregunta' => $pregunta,
                'tipo' => $tipo,
                'grafico' => $grafico,
                'ordinal' => (bool) $orden && $grafico !== 'dona',
                'cats' => $cats,
            ];
        }

        $filas = [];
        foreach ($registros as $r) {
            $v = [];
            foreach ($dims as $d) {
                $valor = $r['valores'][$d['key']] ?? null;
                if ($valor === null || $valor === []) {
                    $v[] = null;
                } elseif ($d['tipo'] === 'multi') {
                    $v[] = array_values(array_map(fn ($x) => $indices[$d['key']][$x], (array) $valor));
                } else {
                    $v[] = $indices[$d['key']][$valor];
                }
            }
            $filas[] = [
                'i' => $r['id'], 't' => $r['tipo'], 'n' => $r['nombre'], 'd' => $r['dni'],
                'p' => $r['programa_id'], 'c' => $r['ciclo_id'], 'e' => $r['edad'],
                'm' => $r['periodos'], 'v' => $v,
            ];
        }

        return ['dims' => $dims, 'filas' => $filas];
    }

    private function periodos(): array
    {
        $fid = PeriodoActual::orderByDesc('id')->get(['id', 'nombre', 'actual'])
            ->map(fn ($p) => ['id' => 'f' . $p->id, 'nombre' => $p->nombre, 'actual' => (bool) $p->actual, 'tipo' => 'FID']);
        $ppd = PeriodoActualPpd::orderByDesc('id')->get(['id', 'nombre', 'actual'])
            ->flatMap(fn ($p) => [
                ['id' => 'p' . $p->id, 'nombre' => $p->nombre . ' (año completo)', 'actual' => (bool) $p->actual, 'tipo' => 'PPD'],
                ['id' => 'p' . $p->id . 's1', 'nombre' => $p->nombre . ' · 1.er semestre', 'actual' => false, 'tipo' => 'PPD'],
                ['id' => 'p' . $p->id . 's2', 'nombre' => $p->nombre . ' · 2.º semestre', 'actual' => false, 'tipo' => 'PPD'],
            ]);

        return $fid->concat($ppd)->values()->all();
    }

    // ── Normalizadores ────────────────────────────────────────────────────────────────────

    /** Minúsculas sin tildes ni signos: base para comparar respuestas escritas distinto. */
    public static function clave(?string $v): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii((string) $v))));
    }

    /** Texto limpio o null si es vacío o un placeholder del formulario. */
    public static function texto($v): ?string
    {
        $v = trim(preg_replace('/\s+/u', ' ', (string) $v));
        $k = self::clave($v);
        if ($k === '' || in_array($k, ['elegir', 'seleccione', 'selecciona', 'null', 'n a', 'na'], true)) {
            return null;
        }
        $v = str_replace('Académia', 'Academia', $v);

        return mb_strtoupper(mb_substr($v, 0, 1)) . mb_substr($v, 1);
    }

    /**
     * Primera regla (regex sobre la clave) que coincide. Sin coincidencia: $otro ('Otro' por
     * defecto; null descarta el valor como no interpretable).
     */
    public static function mapa($v, array $reglas, ?string $otro = 'Otro'): ?string
    {
        if (self::texto($v) === null) {
            return null;
        }
        $k = self::clave($v);
        foreach ($reglas as $patron => $etiqueta) {
            if (preg_match($patron, $k)) {
                return $etiqueta;
            }
        }

        return $otro;
    }

    public static function siNo($v): ?string
    {
        return self::mapa($v, ['/^(1|si|s|true)$/' => 'Sí', '/^(0|no|n|false)$/' => 'No'], null);
    }

    /** Entero dentro de un rango creíble; "ninguno", "cero", "O" (letra) cuentan como 0. */
    public static function entero($v, int $min, int $max): ?int
    {
        $k = self::clave($v);
        if ($k === '') {
            return null;
        }
        if (preg_match('/^(o|no|ninguno|ninguna|no hay|cero|nada)$/', $k)) {
            $n = 0;
        } elseif (preg_match('/^\d+/', $k, $m)) {
            $n = (int) $m[0];
        } else {
            return null;
        }

        return $n >= $min && $n <= $max ? $n : null;
    }

    public static function tramo(?int $n, array $tramos): ?string
    {
        if ($n === null) {
            return null;
        }
        foreach ($tramos as [$desde, $hasta, $etiqueta]) {
            if ($n >= $desde && $n <= $hasta) {
                return $etiqueta;
            }
        }

        return null;
    }

    /** Edad en años; fechas imposibles (menos de 15 o más de 80 años) se descartan. */
    public static function edad($fecha): ?int
    {
        if (empty($fecha)) {
            return null;
        }
        try {
            $edad = Carbon::parse($fecha)->diffInYears(now());
        } catch (\Throwable $e) {
            return null;
        }

        return $edad >= 15 && $edad <= 80 ? (int) $edad : null;
    }

    public static function ciclo(?string $nombre): ?string
    {
        $r = strtoupper(trim(preg_replace('/^ciclo\s+/i', '', (string) $nombre)));

        return preg_match('/^(I|II|III|IV|V|VI|VII|VIII|IX|X)$/', $r) ? 'Ciclo ' . $r : null;
    }

    public static function lengua($v): ?string
    {
        return self::mapa($v, ['/castellano.*quechua|quechua.*castellano/' => 'Castellano y quechua', '/quechua/' => 'Quechua', '/castellano|espanol/' => 'Castellano'], 'Otra');
    }

    private static function hablaQuechua($f): ?string
    {
        $l1 = self::lengua($f->lengua_1);
        $l2 = self::lengua($f->lengua_2);
        if ($l1 === null && $l2 === null) {
            return null;
        }

        return Str::contains(($l1 ?? '') . ' ' . ($l2 ?? ''), ['Quechua', 'quechua']) ? 'Sí' : 'No';
    }

    public static function convivientes($v): ?string
    {
        return self::mapa($v, [
            '/^(no|ninguno)$/' => null,
            '/^sol[oa]$/' => 'Vive solo(a)',
            '/padre y madre|mis padres|^padres/' => 'Padre y madre',
            '/esposo|esposa|conyuge|pareja/' => 'Cónyuge o pareja',
            '/^madre|mama/' => 'Madre',
            '/^padre|papa/' => 'Padre',
            '/hij/' => 'Hijos',
            '/herman/' => 'Hermanos',
            '/abuel/' => 'Abuelos',
        ]);
    }

    public static function sectorLaboral($v): ?string
    {
        $k = self::clave($v);
        if (self::texto($v) === null || strlen($k) < 2) {
            return null;
        }

        return self::mapa($v, [
            '/^(no|ninguno|ninguna|niguno|en ninguno|sin trabajo|estudiante)\b|no trabajo|no estoy trabajando|todavia no/' => 'No trabaja',
            '/educa|docen|profe|auxiliar|magisterio|colegio|escuela|inicial|primaria|infantil|eesp|pukllasunchis/' => 'Educación',
            '/restaur|cafeter|comedor|hotel|turism|cocina|gastronom/' => 'Turismo y gastronomía',
            '/tienda|vend|comerci|negocio|bodega|venta|papeler|panader|optica|almacen/' => 'Comercio',
            '/transport|conduct|chofer|taxi/' => 'Transporte',
            '/agri|agro|campo|chacra|ganad/' => 'Agricultura y ganadería',
            '/independ|informal|personal|a veces|eventual/' => 'Independiente o eventual',
            '/public|estado|municipal/' => 'Sector público',
            '/privad/' => 'Sector privado',
        ]);
    }

    public static function horasServicio($v): ?string
    {
        $k = self::clave($v);
        if ($k === '') {
            return null;
        }
        if (preg_match('/todo el dia|24 horas/', $k)) {
            $h = 24;
        } elseif (is_numeric(trim((string) $v))) {
            $h = (float) $v;
        } else {
            return null;
        }
        if ($h < 0 || $h > 24) {
            return null;
        }

        return match (true) {
            $h == 0 => 'Sin servicio',
            $h < 6 => '1 a 5 h',
            $h < 12 => '6 a 11 h',
            $h < 23.5 => '12 a 23 h',
            default => '24 horas',
        };
    }

    /**
     * Respuesta múltiple guardada como JSON o como CSV (y a veces partida en las comas internas
     * de una opción, p. ej. "Danzas (Danzas folklóricas", " Ballet"…): se reconstruye buscando
     * cada opción conocida en el texto completo. $opciones: [etiqueta => texto a buscar] o
     * [texto] cuando la etiqueta es el mismo texto. "Ninguno" solo cuenta si no hay otra.
     */
    public static function multiple($v, array $opciones): ?array
    {
        // El modelo FID castea estos campos a array; PPD los guarda como CSV.
        $crudo = is_array($v) ? '' : (string) $v;
        $lista = is_array($v) ? $v : json_decode($crudo, true);
        $texto = self::clave(is_array($lista) ? implode(' | ', $lista) : $crudo);
        if ($texto === '') {
            return null;
        }
        $hay = [];
        foreach ($opciones as $etiqueta => $buscar) {
            $etiqueta = is_int($etiqueta) ? $buscar : $etiqueta;
            if (str_contains($texto, self::clave($buscar))) {
                $hay[] = $etiqueta;
            }
        }
        if (count($hay) > 1) {
            $hay = array_values(array_diff($hay, ['Ninguno']));
        }

        return $hay ?: null;
    }

    public static function departamento($v): ?string
    {
        $k = self::clave($v);
        foreach (self::DEPARTAMENTOS as $d) {
            if ($k === self::clave($d)) {
                return $d;
            }
        }

        return null;
    }

    public static function lugar($v): ?string
    {
        $t = self::texto($v);
        if ($t === null || strlen(self::clave($t)) < 3) {
            return null;
        }

        return mb_convert_case(mb_strtolower($t), MB_CASE_TITLE);
    }
}
