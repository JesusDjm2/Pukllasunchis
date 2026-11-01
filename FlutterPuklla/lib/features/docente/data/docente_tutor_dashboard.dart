class DocenteTutoriaItem {
  const DocenteTutoriaItem({
    required this.id,
    required this.alumnoNombre,
    this.ciclo,
    this.programa,
    this.urgencia,
    required this.motivo,
    required this.estado,
    this.fecha,
  });

  factory DocenteTutoriaItem.fromJson(Map<String, dynamic> json) {
    return DocenteTutoriaItem(
      id: json['id'] as int? ?? 0,
      alumnoNombre: json['alumno_nombre'] as String? ?? 'Alumno',
      ciclo: json['ciclo'] as String?,
      programa: json['programa'] as String?,
      urgencia: json['urgencia'] as String?,
      motivo: json['motivo'] as String? ?? '',
      estado: json['estado'] as String? ?? 'pendiente',
      fecha: json['fecha'] as String?,
    );
  }

  final int id;
  final String alumnoNombre;
  final String? ciclo;
  final String? programa;
  final String? urgencia;
  final String motivo;
  final String estado;
  final String? fecha;
}

class DocenteSugerenciaItem {
  const DocenteSugerenciaItem({
    required this.id,
    required this.alumnoNombre,
    this.ciclo,
    this.programa,
    required this.mensaje,
    required this.estado,
    this.fecha,
  });

  factory DocenteSugerenciaItem.fromJson(Map<String, dynamic> json) {
    return DocenteSugerenciaItem(
      id: json['id'] as int? ?? 0,
      alumnoNombre: json['alumno_nombre'] as String? ?? 'Alumno',
      ciclo: json['ciclo'] as String?,
      programa: json['programa'] as String?,
      mensaje: json['mensaje'] as String? ?? '',
      estado: json['estado'] as String? ?? 'pendiente',
      fecha: json['fecha'] as String?,
    );
  }

  final int id;
  final String alumnoNombre;
  final String? ciclo;
  final String? programa;
  final String mensaje;
  final String estado;
  final String? fecha;
}

class DocenteTutorDashboard {
  const DocenteTutorDashboard({
    this.ciclos = const [],
    this.tutorias = const [],
    this.sugerencias = const [],
  });

  factory DocenteTutorDashboard.fromJson(Map<String, dynamic> json) {
    final ciclosList = json['ciclos'] as List? ?? [];
    final tutoriasList = json['tutorias'] as List? ?? [];
    final sugerenciasList = json['sugerencias'] as List? ?? [];

    return DocenteTutorDashboard(
      ciclos: ciclosList
          .map((c) => (c as Map<String, dynamic>)['nombre'] as String? ?? '')
          .where((s) => s.isNotEmpty)
          .toList(),
      tutorias: tutoriasList
          .map((t) => DocenteTutoriaItem.fromJson(t as Map<String, dynamic>))
          .toList(),
      sugerencias: sugerenciasList
          .map((s) => DocenteSugerenciaItem.fromJson(s as Map<String, dynamic>))
          .toList(),
    );
  }

  final List<String> ciclos;
  final List<DocenteTutoriaItem> tutorias;
  final List<DocenteSugerenciaItem> sugerencias;
}
