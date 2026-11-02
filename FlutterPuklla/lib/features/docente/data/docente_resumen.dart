int _asInt(dynamic v) {
  if (v == null) return 0;
  if (v is int) return v;
  if (v is num) return v.toInt();
  if (v is String) return int.tryParse(v) ?? 0;
  return 0;
}

class DocenteTutorCiclo {
  const DocenteTutorCiclo({
    required this.id,
    required this.nombre,
    this.programa,
  });

  factory DocenteTutorCiclo.fromJson(Map<String, dynamic> json) {
    return DocenteTutorCiclo(
      id: _asInt(json['id']),
      nombre: json['nombre'] as String? ?? '',
      programa: json['programa'] as String?,
    );
  }

  final int id;
  final String nombre;
  final String? programa;
}

class DocenteTutorData {
  const DocenteTutorData({
    required this.ciclos,
    required this.tutoriasPendientes,
    required this.sugerenciasPendientes,
  });

  factory DocenteTutorData.fromJson(Map<String, dynamic> json) {
    final ciclosList = json['ciclos'] as List? ?? [];
    return DocenteTutorData(
      ciclos: ciclosList
          .map((c) => DocenteTutorCiclo.fromJson(c as Map<String, dynamic>))
          .toList(),
      tutoriasPendientes: json['tutorias_pendientes'] as int? ?? 0,
      sugerenciasPendientes: json['sugerencias_pendientes'] as int? ?? 0,
    );
  }

  final List<DocenteTutorCiclo> ciclos;
  final int tutoriasPendientes;
  final int sugerenciasPendientes;
}

class DocenteInvestigadorData {
  const DocenteInvestigadorData({
    required this.solicitudesPendientes,
  });

  factory DocenteInvestigadorData.fromJson(Map<String, dynamic> json) {
    return DocenteInvestigadorData(
      solicitudesPendientes: json['solicitudes_pendientes'] as int? ?? 0,
    );
  }

  final int solicitudesPendientes;
}

class DocenteProyectoACargo {
  const DocenteProyectoACargo({
    required this.id,
    required this.nombre,
    this.producto,
    required this.rol,
  });

  factory DocenteProyectoACargo.fromJson(Map<String, dynamic> json) {
    return DocenteProyectoACargo(
      id: json['id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? '',
      producto: json['producto'] as String?,
      rol: json['rol'] as String? ?? 'encargado',
    );
  }

  final int id;
  final String nombre;
  final String? producto;
  final String rol;
}

/// Espejo de `GET /api/v1/docente/resumen` — métricas y roles del usuario
/// del panel web (`DocenteCOntroller@vistaDocente`).
class DocenteResumen {
  const DocenteResumen({
    this.periodoActual,
    required this.cursos,
    required this.cursosFid,
    required this.cursosPpd,
    required this.cursosAsincronicos,
    required this.incidenciasTotal,
    required this.incidenciasPendientes,
    required this.notificacionesSinLeer,
    this.totalAlumnos = 0,
    this.totalMatriculas = 0,
    this.roles = const [],
    this.esTutor = false,
    this.esInvestigador = false,
    this.esTitulacion = false,
    this.esBolsa = false,
    this.esAdmin = false,
    this.tutorData,
    this.investigadorData,
    this.proyectosACargo = const [],
  });

  factory DocenteResumen.fromJson(Map<String, dynamic> json) {
    final rolesList = (json['roles'] as List?)?.map((e) => e.toString()).toList() ?? [];
    final proyectosList = json['proyectos_a_cargo'] as List? ?? [];
    final tutorMap = json['tutor_data'] as Map<String, dynamic>?;
    final invMap = json['investigador_data'] as Map<String, dynamic>?;

    return DocenteResumen(
      periodoActual: json['periodo_actual'] as String?,
      cursos: _asInt(json['cursos']),
      cursosFid: _asInt(json['cursos_fid']),
      cursosPpd: _asInt(json['cursos_ppd']),
      cursosAsincronicos: _asInt(json['cursos_asincronicos']),
      incidenciasTotal: _asInt(json['incidencias_total']),
      incidenciasPendientes: _asInt(json['incidencias_pendientes']),
      notificacionesSinLeer: _asInt(json['notificaciones_sin_leer']),
      totalAlumnos: _asInt(json['total_alumnos']),
      totalMatriculas: _asInt(json['total_matriculas']),
      roles: rolesList,
      esTutor: json['es_tutor'] as bool? ?? false,
      esInvestigador: json['es_investigador'] as bool? ?? false,
      esTitulacion: json['es_titulacion'] as bool? ?? false,
      esBolsa: json['es_bolsa'] as bool? ?? false,
      esAdmin: json['es_admin'] as bool? ?? false,
      tutorData: tutorMap != null ? DocenteTutorData.fromJson(tutorMap) : null,
      investigadorData: invMap != null ? DocenteInvestigadorData.fromJson(invMap) : null,
      proyectosACargo: proyectosList
          .map((p) => DocenteProyectoACargo.fromJson(p as Map<String, dynamic>))
          .toList(),
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
  final int totalAlumnos;
  final int totalMatriculas;
  final List<String> roles;
  final bool esTutor;
  final bool esInvestigador;
  final bool esTitulacion;
  final bool esBolsa;
  final bool esAdmin;
  final DocenteTutorData? tutorData;
  final DocenteInvestigadorData? investigadorData;
  final List<DocenteProyectoACargo> proyectosACargo;

  bool get tieneFuncionesEspeciales =>
      esTutor || esInvestigador || esTitulacion || esBolsa || proyectosACargo.isNotEmpty;
}
