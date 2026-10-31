class DocenteAlumno {
  const DocenteAlumno({
    required this.id,
    required this.nombreCompleto,
    this.email,
    this.telefono,
    this.dni,
  });

  factory DocenteAlumno.fromJson(Map<String, dynamic> json) {
    return DocenteAlumno(
      id: json['id'] as int? ?? 0,
      nombreCompleto: json['nombre_completo'] as String? ?? '',
      email: json['email'] as String?,
      telefono: json['telefono'] as String?,
      dni: json['dni'] as String?,
    );
  }

  final int id;
  final String nombreCompleto;
  final String? email;
  final String? telefono;
  final String? dni;
}

class DocenteCursoAlumnos {
  const DocenteCursoAlumnos({
    required this.cursoId,
    required this.cursoNombre,
    this.ciclo,
    this.programa,
    required this.total,
    required this.alumnos,
  });

  factory DocenteCursoAlumnos.fromJson(Map<String, dynamic> json) {
    final list = json['alumnos'] as List? ?? [];
    return DocenteCursoAlumnos(
      cursoId: json['curso_id'] as int? ?? 0,
      cursoNombre: json['curso_nombre'] as String? ?? '',
      ciclo: json['ciclo'] as String?,
      programa: json['programa'] as String?,
      total: json['total'] as int? ?? 0,
      alumnos: list
          .map((a) => DocenteAlumno.fromJson(a as Map<String, dynamic>))
          .toList(),
    );
  }

  final int cursoId;
  final String cursoNombre;
  final String? ciclo;
  final String? programa;
  final int total;
  final List<DocenteAlumno> alumnos;
}
