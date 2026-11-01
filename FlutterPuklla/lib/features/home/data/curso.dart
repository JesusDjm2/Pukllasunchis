/// Espejo de `CursoResource` (`GET /api/v1/cursos`). `horas` y `creditos` son
/// `string` en la BD (ver migración `create_cursos_table`), no numéricos.
class Curso {
  const Curso({
    required this.id,
    required this.nombre,
    required this.sumilla,
    required this.cc,
    required this.horas,
    required this.creditos,
    required this.classroom,
    this.clave,
    this.cicloNombre,
    this.programaNombre,
    this.silaboUrl,
    this.calificaciones,
    this.docentes = const [],
    this.competencias = const [],
  });

  factory Curso.fromJson(Map<String, dynamic> json) {
    final docentesList = (json['docentes'] as List<dynamic>?)
            ?.map((e) => DocenteCurso.fromJson(e as Map<String, dynamic>))
            .toList() ??
        const [];

    final competenciasList = (json['competencias'] as List<dynamic>?)
            ?.map((e) => CompetenciaCurso.fromJson(e as Map<String, dynamic>))
            .toList() ??
        const [];

    final califJson = json['calificaciones'] as Map<String, dynamic>?;

    return Curso(
      id: json['id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? 'Curso sin nombre',
      sumilla: json['sumilla'] as String?,
      cc: json['cc']?.toString() ?? '—',
      horas: json['horas']?.toString() ?? '—',
      creditos: json['creditos']?.toString() ?? '—',
      classroom: json['classroom'] as String?,
      clave: json['clave'] as String?,
      cicloNombre: json['ciclo_nombre'] as String?,
      programaNombre: json['programa_nombre'] as String?,
      silaboUrl: json['silabo_url'] as String?,
      calificaciones: califJson != null ? CalificacionesResumenCurso.fromJson(califJson) : null,
      docentes: docentesList,
      competencias: competenciasList,
    );
  }

  final int id;
  final String nombre;
  final String? sumilla;
  final String cc;
  final String horas;
  final String creditos;
  final String? classroom;
  final String? clave;
  final String? cicloNombre;
  final String? programaNombre;
  final String? silaboUrl;
  final CalificacionesResumenCurso? calificaciones;
  final List<DocenteCurso> docentes;
  final List<CompetenciaCurso> competencias;
}

class CalificacionesResumenCurso {
  const CalificacionesResumenCurso({
    this.parcial1,
    this.parcial2,
    this.promedio,
    this.observaciones,
  });

  factory CalificacionesResumenCurso.fromJson(Map<String, dynamic> json) {
    return CalificacionesResumenCurso(
      parcial1: json['parcial_1']?.toString(),
      parcial2: json['parcial_2']?.toString(),
      promedio: json['promedio']?.toString(),
      observaciones: json['observaciones'] as String?,
    );
  }

  final String? parcial1;
  final String? parcial2;
  final String? promedio;
  final String? observaciones;

  bool get tieneAlgunaNota =>
      (parcial1 != null && parcial1!.isNotEmpty) ||
      (parcial2 != null && parcial2!.isNotEmpty) ||
      (promedio != null && promedio!.isNotEmpty);
}

class DocenteCurso {
  const DocenteCurso({
    required this.id,
    required this.nombre,
    this.email,
    this.telefono,
    this.fotoUrl,
  });

  factory DocenteCurso.fromJson(Map<String, dynamic> json) {
    return DocenteCurso(
      id: json['id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? 'Docente asignado',
      email: json['email'] as String?,
      telefono: json['telefono'] as String?,
      fotoUrl: json['foto_url'] as String?,
    );
  }

  final int id;
  final String nombre;
  final String? email;
  final String? telefono;
  final String? fotoUrl;
}

class CompetenciaCurso {
  const CompetenciaCurso({
    required this.id,
    required this.nombre,
    this.descripcion,
    this.capacidades,
  });

  factory CompetenciaCurso.fromJson(Map<String, dynamic> json) {
    return CompetenciaCurso(
      id: json['id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? '',
      descripcion: json['descripcion'] as String?,
      capacidades: json['capacidades'] as String?,
    );
  }

  final int id;
  final String nombre;
  final String? descripcion;
  final String? capacidades;
}
