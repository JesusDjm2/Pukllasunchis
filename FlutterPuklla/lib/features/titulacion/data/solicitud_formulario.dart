import 'dart:typed_data';

/// Las tres solicitudes de titulación que el estudiante envía desde la app.
/// Espejo de `/api/v1/alumno/titulacion/{ruta}` (y de `/mi-titulacion/*` en la web).
enum TipoSolicitud {
  pti('solicitud-pti', 'Aprobación del PTI'),
  carta('carta', 'Carta de aceptación'),
  originalidad('originalidad', 'Revisión de originalidad');

  const TipoSolicitud(this.ruta, this.titulo);

  final String ruta;
  final String titulo;

  static TipoSolicitud? deRuta(String? ruta) =>
      TipoSolicitud.values.where((t) => t.ruta == ruta).firstOrNull;
}

/// Lo que devuelve el GET de cada formulario: si puede enviar, su última
/// solicitud, los valores iniciales, qué campos fija la ficha y las opciones.
class SolicitudFormulario {
  const SolicitudFormulario({
    this.motivoNoPuede,
    this.ultima,
    this.historial = const [],
    this.valores = const {},
    this.fijos = const {},
    this.opciones = const {},
    this.archivos = const {},
    this.pago,
    this.pagos = const {},
    this.titulos = const {},
    this.tesistas = const [],
    this.solicitante,
    this.habilitada = true,
    this.formularioExterno,
    this.similitudMaxima,
  });

  factory SolicitudFormulario.fromJson(Map<String, dynamic> json) {
    final ultima = json['ultima'] as Map<String, dynamic>?;
    final pago = json['pago'];
    return SolicitudFormulario(
      motivoNoPuede: json['motivo_no_puede'] as String?,
      ultima: ultima != null ? SolicitudResumen.fromJson(ultima) : null,
      historial: (json['historial'] as List? ?? const [])
          .map((e) => SolicitudResumen.fromJson(e as Map<String, dynamic>))
          .toList(),
      valores: (json['valores'] as Map<String, dynamic>? ?? const {})
          .map((k, v) => MapEntry(k, v?.toString() ?? '')),
      fijos: Set<String>.from(json['fijos'] as List? ?? const []),
      opciones: (json['opciones'] as Map<String, dynamic>? ?? const {}).map(
        (k, v) => MapEntry(k, (v as List).map(Opcion.fromJson).toList()),
      ),
      archivos: (json['archivos'] as Map<String, dynamic>? ?? const {})
          .map((k, v) => MapEntry(k, ReglaArchivo.fromJson(v as Map<String, dynamic>))),
      // Config de la web: [monto, código, concepto].
      pago: pago is List && pago.length >= 3
          ? PagoTramite(monto: '${pago[0]}', codigo: '${pago[1]}', concepto: '${pago[2]}')
          : null,
      pagos: (json['pagos'] as Map<String, dynamic>? ?? const {}).map((k, v) {
        final p = v as Map<String, dynamic>;
        return MapEntry(k, PagoTramite(monto: '${p['monto']}', codigo: '${p['codigo']}', concepto: '${p['concepto']}'));
      }),
      titulos: (json['titulos'] as Map<String, dynamic>? ?? const {})
          .map((k, v) => MapEntry(k, v?.toString() ?? '')),
      tesistas: List<String>.from(json['tesistas'] as List? ?? const []),
      solicitante: json['solicitante'] as String?,
      habilitada: json['habilitada'] as bool? ?? true,
      formularioExterno: json['formulario_externo'] as String?,
      similitudMaxima: json['similitud_maxima'] as int?,
    );
  }

  /// null = puede enviar. Si no, el motivo (en revisión, falta el PTI, etc.).
  final String? motivoNoPuede;
  final SolicitudResumen? ultima;
  final List<SolicitudResumen> historial;
  final Map<String, String> valores;

  /// Campos que salen de la ficha y no se cambian (programa, ciclo…).
  final Set<String> fijos;
  final Map<String, List<Opcion>> opciones;
  final Map<String, ReglaArchivo> archivos;
  final PagoTramite? pago;

  /// Originalidad: pago según la etapa (ti / tesis).
  final Map<String, PagoTramite> pagos;

  /// Originalidad: título sugerido según la etapa.
  final Map<String, String> titulos;
  final List<String> tesistas;
  final String? solicitante;

  /// Originalidad: si la web la habilitó para alumnos (si no, va el formulario de Google).
  final bool habilitada;
  final String? formularioExterno;
  final int? similitudMaxima;

  bool get puedeEnviar => motivoNoPuede == null;
}

class SolicitudResumen {
  const SolicitudResumen({
    required this.estado,
    required this.estadoNombre,
    this.fecha,
    this.observacion,
    this.fechaLimite,
    this.similitud,
    this.apto,
    this.etapa,
    this.institucion,
  });

  factory SolicitudResumen.fromJson(Map<String, dynamic> json) => SolicitudResumen(
        estado: json['estado'] as String? ?? '',
        estadoNombre: json['estado_nombre'] as String? ?? '',
        fecha: DateTime.tryParse(json['fecha'] as String? ?? ''),
        observacion: json['observacion'] as String?,
        fechaLimite: DateTime.tryParse(json['fecha_limite'] as String? ?? ''),
        similitud: (json['similitud'] as num?)?.toDouble(),
        apto: json['apto'] as bool?,
        etapa: json['etapa'] as String?,
        institucion: json['institucion'] as String?,
      );

  /// recibida/enviada | observada | atendida/emitida
  final String estado;
  final String estadoNombre;
  final DateTime? fecha;
  final String? observacion;
  final DateTime? fechaLimite;
  final double? similitud;
  final bool? apto;
  final String? etapa;
  final String? institucion;

  bool get observada => estado == 'observada';
  bool get enRevision => estado == 'recibida' || estado == 'enviada';
}

class Opcion {
  const Opcion({required this.valor, required this.texto});

  /// Acepta `"Educación Inicial"` o `{"valor": "D1", "texto": "Área D1: ..."}`.
  factory Opcion.fromJson(dynamic json) => json is Map<String, dynamic>
      ? Opcion(valor: '${json['valor']}', texto: '${json['texto']}')
      : Opcion(valor: '$json', texto: '$json');

  final String valor;
  final String texto;
}

class ReglaArchivo {
  const ReglaArchivo({required this.extensiones, required this.maxMb, this.maxArchivos = 1});

  factory ReglaArchivo.fromJson(Map<String, dynamic> json) => ReglaArchivo(
        extensiones: List<String>.from(json['extensiones'] as List? ?? const []),
        maxMb: json['max_mb'] as int? ?? 10,
        maxArchivos: json['max_archivos'] as int? ?? 1,
      );

  final List<String> extensiones;
  final int maxMb;
  final int maxArchivos;

  String get descripcion => '${extensiones.map((e) => e.toUpperCase()).join(', ')} · hasta $maxMb MB'
      '${maxArchivos > 1 ? ' · máx. $maxArchivos archivos' : ''}';
}

class PagoTramite {
  const PagoTramite({required this.monto, required this.codigo, required this.concepto});

  final String monto;
  final String codigo;
  final String concepto;
}

/// Archivo elegido en el dispositivo, listo para subir.
class ArchivoElegido {
  const ArchivoElegido({required this.nombre, required this.bytes});

  final String nombre;
  final Uint8List bytes;

  double get mb => bytes.lengthInBytes / (1024 * 1024);
}

/// Respuesta del POST: el mensaje que la web muestra al enviar.
class SolicitudEnviada {
  const SolicitudEnviada({required this.mensaje});

  final String mensaje;
}
