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
  final List<DocenteCurso> docentes;
  final List<CompetenciaCurso> competencias;
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
