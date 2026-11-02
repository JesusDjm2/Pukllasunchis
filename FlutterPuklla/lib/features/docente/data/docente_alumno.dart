int _asInt(dynamic v) {
  if (v == null) return 0;
  if (v is int) return v;
  if (v is num) return v.toInt();
  if (v is String) return int.tryParse(v) ?? 0;
  return 0;
}

class DocenteAlumno {
  const DocenteAlumno({
    required this.id,
    required this.nombreCompleto,
    this.email,
    this.telefono,
    this.telefonoReferencia,
    this.dni,
  });

  factory DocenteAlumno.fromJson(Map<String, dynamic> json) {
    return DocenteAlumno(
      id: _asInt(json['id']),
      nombreCompleto: json['nombre_completo'] as String? ?? '',
      email: json['email']?.toString(),
      telefono: json['telefono']?.toString(),
      telefonoReferencia: json['telefono_referencia']?.toString(),
      dni: json['dni']?.toString(),
    );
  }

  final int id;
  final String nombreCompleto;
  final String? email;
  final String? telefono;
  final String? telefonoReferencia;
  final String? dni;
}

class DocenteCursoAlumnos {
  const DocenteCursoAlumnos({
    required this.cursoId,
    required this.cursoNombre,
    this.ciclo,
    this.programa,
    this.area,
    required this.total,
    required this.alumnos,
  });

  factory DocenteCursoAlumnos.fromJson(Map<String, dynamic> json) {
    final list = json['alumnos'] as List? ?? [];
    return DocenteCursoAlumnos(
      cursoId: _asInt(json['curso_id']),
      cursoNombre: json['curso_nombre'] as String? ?? '',
      ciclo: json['ciclo'] as String?,
      programa: json['programa'] as String?,
      area: json['area'] as String?,
      total: _asInt(json['total']),
      alumnos: list
          .map((a) => DocenteAlumno.fromJson(a as Map<String, dynamic>))
          .toList(),
    );
  }

  final int cursoId;
  final String cursoNombre;
  final String? ciclo;
  final String? programa;
  final String? area;
  final int total;
  final List<DocenteAlumno> alumnos;

  bool get esPpd => area == 'ppd';
}
