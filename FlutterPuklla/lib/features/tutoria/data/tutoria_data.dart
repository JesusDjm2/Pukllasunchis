class TutoriaAlumno {
  const TutoriaAlumno({
    required this.id,
    required this.nombreCompleto,
    required this.programa,
    required this.ciclo,
    required this.tutores,
  });

  factory TutoriaAlumno.fromJson(Map<String, dynamic> json) {
    return TutoriaAlumno(
      id: json['id'] as int? ?? 0,
      nombreCompleto: json['nombre_completo'] as String? ?? '',
      programa: json['programa'] as String? ?? '',
      ciclo: json['ciclo'] as String? ?? '',
      tutores: (json['tutores'] as List?)?.map((e) => e.toString()).toList() ?? [],
    );
  }

  final int id;
  final String nombreCompleto;
  final String programa;
  final String ciclo;
  final List<String> tutores;
}

class TutoriaOpcion {
  const TutoriaOpcion({
    required this.clave,
    required this.texto,
    required this.etiquetaCorta,
    required this.color,
  });

  factory TutoriaOpcion.fromJson(Map<String, dynamic> json) {
    return TutoriaOpcion(
      clave: json['clave'] as String? ?? '',
      texto: json['texto'] as String? ?? '',
      etiquetaCorta: json['etiqueta_corta'] as String? ?? '',
      color: json['color'] as String? ?? 'slate',
    );
  }

  final String clave;
  final String texto;
  final String etiquetaCorta;
  final String color;
}

class DocenteElegible {
  const DocenteElegible({
    required this.id,
    required this.nombre,
    this.detalle,
    this.grupo,
  });

  factory DocenteElegible.fromJson(Map<String, dynamic> json) {
    return DocenteElegible(
      id: json['id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? '',
      detalle: json['detalle'] as String?,
      grupo: json['grupo'] as String?,
    );
  }

  final int id;
  final String nombre;
  final String? detalle;
  final String? grupo;
}

class TutoriaHistorialItem {
  const TutoriaHistorialItem({
    required this.id,
    required this.realId,
    required this.tipo,
    required this.etiqueta,
    required this.texto,
    required this.fecha,
    required this.listo,
    required this.estado,
    this.extra,
  });

  factory TutoriaHistorialItem.fromJson(Map<String, dynamic> json) {
    return TutoriaHistorialItem(
      id: json['id'] as String? ?? '',
      realId: json['real_id'] as int? ?? 0,
      tipo: json['tipo'] as String? ?? 'tutoria',
      etiqueta: json['etiqueta'] as String? ?? '',
      texto: json['texto'] as String? ?? '',
      fecha: json['fecha'] as String? ?? '',
      listo: json['listo'] as bool? ?? false,
      estado: json['estado'] as String? ?? 'Pendiente',
      extra: json['extra'] as String?,
    );
  }

  final String id;
  final int realId;
  final String tipo; // 'tutoria' | 'sugerencia'
  final String etiqueta; // 'Tutoría' | 'Sugerencia'
  final String texto;
  final String fecha;
  final bool listo;
  final String estado; // 'Pendiente' | 'Atendida' | 'Revisada'
  final String? extra;
}

class TutoriaData {
  const TutoriaData({
    required this.alumno,
    required this.opciones,
    required this.docentesElegibles,
    required this.historial,
  });

  factory TutoriaData.fromJson(Map<String, dynamic> json) {
    final alumnoMap = json['alumno'] as Map<String, dynamic>? ?? {};
    final opcionesList = json['opciones'] as List? ?? [];
    final docentesList = json['docentes_elegibles'] as List? ?? [];
    final historialList = json['historial'] as List? ?? [];

    return TutoriaData(
      alumno: TutoriaAlumno.fromJson(alumnoMap),
      opciones: opcionesList
          .map((e) => TutoriaOpcion.fromJson(e as Map<String, dynamic>))
          .toList(),
      docentesElegibles: docentesList
          .map((e) => DocenteElegible.fromJson(e as Map<String, dynamic>))
          .toList(),
      historial: historialList
          .map((e) => TutoriaHistorialItem.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  final TutoriaAlumno alumno;
  final List<TutoriaOpcion> opciones;
  final List<DocenteElegible> docentesElegibles;
  final List<TutoriaHistorialItem> historial;
}
