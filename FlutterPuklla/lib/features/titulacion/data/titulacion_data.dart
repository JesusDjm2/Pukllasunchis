/// Espejo de `GET /api/v1/alumno/titulacion`: "Mi proceso de titulación",
/// lo mismo que la web muestra en titulacion/_avance_alumno (con proceso) o
/// titulacion/_guia_inicio (sin proceso, pero ya puede iniciarlo).
class TitulacionData {
  const TitulacionData({
    required this.esEgresado,
    this.promocion,
    required this.disponible,
    required this.tieneProceso,
    required this.puedeIniciar,
    required this.enlaceWeb,
    this.estado,
    this.siguientePaso,
    this.etapas = const [],
    this.vigenciaPti,
    this.solicitudPti,
    this.carta,
    this.originalidad,
    this.tramites,
  });

  factory TitulacionData.fromJson(Map<String, dynamic> json) {
    Map<String, dynamic>? m(String k) => json[k] as Map<String, dynamic>?;
    return TitulacionData(
      esEgresado: json['es_egresado'] as bool? ?? false,
      promocion: json['promocion'] as String?,
      disponible: json['disponible'] as bool? ?? false,
      tieneProceso: json['tiene_proceso'] as bool? ?? false,
      puedeIniciar: json['puede_iniciar'] as bool? ?? false,
      enlaceWeb: json['enlace_web'] as String? ?? '',
      estado: m('estado') != null ? TitulacionEstado.fromJson(m('estado')!) : null,
      siguientePaso: m('siguiente_paso') != null ? TitulacionSiguientePaso.fromJson(m('siguiente_paso')!) : null,
      etapas: (json['etapas'] as List? ?? const [])
          .map((e) => TitulacionEtapa.fromJson(e as Map<String, dynamic>))
          .toList(),
      vigenciaPti: m('vigencia_pti') != null ? TitulacionVigencia.fromJson(m('vigencia_pti')!) : null,
      solicitudPti: m('solicitud_pti') != null ? TitulacionAccion.fromJson(m('solicitud_pti')!) : null,
      carta: m('carta') != null ? TitulacionAccion.fromJson(m('carta')!) : null,
      originalidad: m('originalidad') != null ? TitulacionAccion.fromJson(m('originalidad')!) : null,
      tramites: m('tramites') != null ? TitulacionTramites.fromJson(m('tramites')!) : null,
    );
  }

  final bool esEgresado;
  final String? promocion;

  /// Si el alumno ve "Mi titulación": egresado, o con proceso iniciado, o
  /// ya puede iniciarlo (FID desde el Ciclo IX).
  final bool disponible;
  final bool tieneProceso;
  final bool puedeIniciar;

  /// Su panel en la web (allí están el grupo, las RD y los formularios).
  final String enlaceWeb;
  final TitulacionEstado? estado;
  final TitulacionSiguientePaso? siguientePaso;
  final List<TitulacionEtapa> etapas;
  final TitulacionVigencia? vigenciaPti;

  /// Primer paso del que aún no tiene proceso: la solicitud de aprobación del PTI.
  final TitulacionAccion? solicitudPti;

  /// Carta de aceptación de la Institución Educativa (con el PTI aprobado).
  final TitulacionAccion? carta;

  /// Revisión de originalidad (Turnitin), cuando la web la habilita para alumnos.
  final TitulacionAccion? originalidad;

  /// "Qué hacer ahora": trámites del tramo en curso.
  final TitulacionTramites? tramites;

  /// Acciones con su estado (en el orden en que aparecen en la web).
  List<TitulacionAccion> get acciones => [
        if (solicitudPti != null) solicitudPti!,
        if (carta != null) carta!,
        if (originalidad != null) originalidad!,
      ];

  /// 0..1 según las etapas completas (para la barra de avance).
  double get progreso {
    if (etapas.isEmpty) return 0;
    final hitos = etapas.expand((e) => e.hitos).toList();
    if (hitos.isEmpty) return 0;
    return hitos.where((h) => h.situacion == 'logrado' || h.situacion == 'sin_registro').length / hitos.length;
  }
}

class TitulacionEstado {
  const TitulacionEstado({required this.clave, required this.nombre, required this.color});

  factory TitulacionEstado.fromJson(Map<String, dynamic> json) => TitulacionEstado(
        clave: json['clave'] as String? ?? '',
        nombre: json['nombre'] as String? ?? '',
        color: json['color'] as String? ?? 'secondary',
      );

  final String clave;
  final String nombre;

  /// Nombre del color Bootstrap que usa la web (success, info, primary, warning, secondary).
  final String color;
}

class TitulacionSiguientePaso {
  const TitulacionSiguientePaso({
    required this.clave,
    required this.etapa,
    this.etapaTitulo,
    required this.label,
    required this.falta,
  });

  factory TitulacionSiguientePaso.fromJson(Map<String, dynamic> json) => TitulacionSiguientePaso(
        clave: json['clave'] as String? ?? '',
        etapa: json['etapa'] as String? ?? '',
        etapaTitulo: json['etapa_titulo'] as String?,
        label: json['label'] as String? ?? '',
        falta: json['falta'] as String? ?? '',
      );

  final String clave;
  final String etapa;
  final String? etapaTitulo;
  final String label;
  final String falta;
}

class TitulacionEtapa {
  const TitulacionEtapa({
    required this.clave,
    required this.titulo,
    required this.estado,
    this.resumen,
    this.hitos = const [],
  });

  factory TitulacionEtapa.fromJson(Map<String, dynamic> json) => TitulacionEtapa(
        clave: json['clave'] as String? ?? '',
        titulo: json['titulo'] as String? ?? '',
        estado: json['estado'] as String? ?? 'pendiente',
        resumen: json['resumen'] as String?,
        hitos: (json['hitos'] as List? ?? const [])
            .map((e) => TitulacionHito.fromJson(e as Map<String, dynamic>))
            .toList(),
      );

  final String clave;
  final String titulo;

  /// completo | en_proceso | pendiente
  final String estado;
  final String? resumen;
  final List<TitulacionHito> hitos;
}

class TitulacionHito {
  const TitulacionHito({
    required this.clave,
    required this.label,
    required this.situacion,
    this.fecha,
    this.detalle,
    required this.falta,
    this.enlace,
  });

  factory TitulacionHito.fromJson(Map<String, dynamic> json) => TitulacionHito(
        clave: json['clave'] as String? ?? '',
        label: json['label'] as String? ?? '',
        situacion: json['situacion'] as String? ?? 'pendiente',
        fecha: DateTime.tryParse(json['fecha'] as String? ?? ''),
        detalle: json['detalle'] as String?,
        falta: json['falta'] as String? ?? '',
        enlace: json['enlace'] as String?,
      );

  final String clave;
  final String label;

  /// logrado | actual | pendiente | sin_registro (falta el dato, pero un paso posterior ya se logró)
  final String situacion;
  final DateTime? fecha;
  final String? detalle;
  final String falta;
  final String? enlace;
}

class TitulacionVigencia {
  const TitulacionVigencia({
    this.venceEl,
    required this.vencido,
    required this.conExtension,
    required this.mesesRestantes,
    required this.urgente,
  });

  factory TitulacionVigencia.fromJson(Map<String, dynamic> json) => TitulacionVigencia(
        venceEl: DateTime.tryParse(json['vence_el'] as String? ?? ''),
        vencido: json['vencido'] as bool? ?? false,
        conExtension: json['con_extension'] as bool? ?? false,
        mesesRestantes: json['meses_restantes'] as int? ?? 0,
        urgente: json['urgente'] as bool? ?? false,
      );

  final DateTime? venceEl;
  final bool vencido;
  final bool conExtension;
  final int mesesRestantes;
  final bool urgente;
}

/// Una solicitud de titulación (PTI, carta, originalidad) con el estado de la
/// última enviada. Se envía desde la web (lleva archivos).
class TitulacionAccion {
  const TitulacionAccion({this.estado, required this.mensaje, required this.boton, required this.url, this.formularioApp});

  factory TitulacionAccion.fromJson(Map<String, dynamic> json) => TitulacionAccion(
        estado: json['estado'] as String?,
        mensaje: json['mensaje'] as String? ?? '',
        boton: json['boton'] as String? ?? 'Abrir',
        url: json['url'] as String? ?? '',
        formularioApp: json['formulario_app'] as String?,
      );

  /// null (aún no la pide) | recibida/enviada | observada | atendida/emitida
  final String? estado;
  final String mensaje;
  final String boton;
  final String url;

  /// Formulario nativo de la app (solicitud-pti, carta, originalidad); null = solo en la web.
  final String? formularioApp;

  bool get observada => estado == 'observada';
  bool get enCurso => estado == 'recibida' || estado == 'enviada';
  bool get lista => estado == 'atendida' || estado == 'emitida';
}

class TitulacionTramites {
  const TitulacionTramites({required this.titulo, this.items = const [], this.contactos = const []});

  factory TitulacionTramites.fromJson(Map<String, dynamic> json) => TitulacionTramites(
        titulo: json['titulo'] as String? ?? '',
        items: (json['items'] as List? ?? const [])
            .map((e) => TitulacionTramite.fromJson(e as Map<String, dynamic>))
            .toList(),
        contactos: (json['contactos'] as List? ?? const [])
            .map((e) => TitulacionContacto.fromJson(e as Map<String, dynamic>))
            .toList(),
      );

  final String titulo;
  final List<TitulacionTramite> items;
  final List<TitulacionContacto> contactos;
}

class TitulacionTramite {
  const TitulacionTramite({
    required this.titulo,
    this.opcional,
    this.pago,
    this.pasos = const [],
    this.formulario,
    this.descargas = const [],
    this.adjuntar = const [],
    this.resultado,
    this.plazo,
    this.nota,
  });

  factory TitulacionTramite.fromJson(Map<String, dynamic> json) {
    final pago = json['pago'] as Map<String, dynamic>?;
    final form = json['formulario'] as Map<String, dynamic>?;
    return TitulacionTramite(
      titulo: json['titulo'] as String? ?? '',
      opcional: json['opcional'] as String?,
      pago: pago != null ? TitulacionPago.fromJson(pago) : null,
      pasos: List<String>.from(json['pasos'] as List? ?? const []),
      formulario: form != null ? TitulacionEnlace.fromJson(form) : null,
      descargas: (json['descargas'] as List? ?? const [])
          .map((e) => TitulacionEnlace.fromJson(e as Map<String, dynamic>))
          .toList(),
      adjuntar: List<String>.from(json['adjuntar'] as List? ?? const []),
      resultado: json['resultado'] as String?,
      plazo: json['plazo'] as String?,
      nota: json['nota'] as String?,
    );
  }

  final String titulo;
  final String? opcional;
  final TitulacionPago? pago;
  final List<String> pasos;
  final TitulacionEnlace? formulario;
  final List<TitulacionEnlace> descargas;
  final List<String> adjuntar;
  final String? resultado;
  final String? plazo;
  final String? nota;
}

class TitulacionPago {
  const TitulacionPago({required this.monto, required this.codigo, required this.concepto});

  factory TitulacionPago.fromJson(Map<String, dynamic> json) => TitulacionPago(
        monto: '${json['monto'] ?? ''}',
        codigo: '${json['codigo'] ?? ''}',
        concepto: json['concepto'] as String? ?? '',
      );

  final String monto;
  final String codigo;
  final String concepto;
}

class TitulacionEnlace {
  const TitulacionEnlace({required this.texto, required this.url, this.formularioApp});

  factory TitulacionEnlace.fromJson(Map<String, dynamic> json) => TitulacionEnlace(
        texto: json['texto'] as String? ?? '',
        url: json['url'] as String? ?? '',
        formularioApp: json['formulario_app'] as String?,
      );

  final String texto;
  final String url;

  /// Formulario nativo de la app; null = se abre en el navegador (p. ej. Google Forms).
  final String? formularioApp;
}

class TitulacionContacto {
  const TitulacionContacto({required this.nombre, required this.telefono});

  factory TitulacionContacto.fromJson(Map<String, dynamic> json) => TitulacionContacto(
        nombre: json['nombre'] as String? ?? '',
        telefono: json['telefono'] as String? ?? '',
      );

  final String nombre;
  final String telefono;

  /// Enlace de WhatsApp (solo dígitos, con el 51 del Perú).
  String get whatsapp => 'https://wa.me/${telefono.replaceAll(RegExp(r'[^0-9]'), '')}';
}
