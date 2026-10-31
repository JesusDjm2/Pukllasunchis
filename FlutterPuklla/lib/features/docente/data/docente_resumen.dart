/// Espejo de `GET /api/v1/docente/resumen` — las mismas métricas que el
/// hero del panel web (`DocenteCOntroller@vistaDocente`).
class DocenteResumen {
  const DocenteResumen({
    required this.periodoActual,
    required this.cursos,
    required this.cursosFid,
    required this.cursosPpd,
    required this.cursosAsincronicos,
    required this.incidenciasTotal,
    required this.incidenciasPendientes,
    required this.notificacionesSinLeer,
  });

  factory DocenteResumen.fromJson(Map<String, dynamic> json) {
    return DocenteResumen(
      periodoActual: json['periodo_actual'] as String?,
      cursos: json['cursos'] as int,
      cursosFid: json['cursos_fid'] as int,
      cursosPpd: json['cursos_ppd'] as int,
      cursosAsincronicos: json['cursos_asincronicos'] as int,
      incidenciasTotal: json['incidencias_total'] as int,
      incidenciasPendientes: json['incidencias_pendientes'] as int,
      notificacionesSinLeer: json['notificaciones_sin_leer'] as int,
    );
  }

  final String? periodoActual;
  final int cursos;
  final int cursosFid;
  final int cursosPpd;
  final int cursosAsincronicos;
  final int incidenciasTotal;
  final int incidenciasPendientes;
  final int notificacionesSinLeer;
}
