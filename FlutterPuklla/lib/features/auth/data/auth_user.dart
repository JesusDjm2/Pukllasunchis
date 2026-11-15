/// Espejo de la respuesta de `GET /api/v1/me`.
class AuthUser {
  const AuthUser({
    required this.id,
    required this.name,
    required this.apellidos,
    required this.email,
    this.foto,
    required this.roles,
    this.beca = false,
    this.condicion,
    this.esEgresado = false,
    required this.alumnoId,
    required this.docenteId,
  });

  factory AuthUser.fromJson(Map<String, dynamic> json) {
    return AuthUser(
      id: json['id'] as int,
      name: json['name'] as String,
      apellidos: json['apellidos'] as String?,
      email: json['email'] as String,
      foto: json['foto'] as String?,
      roles: List<String>.from(json['roles'] as List? ?? const []),
      beca: json['beca'] as bool? ?? false,
      condicion: json['condicion'] as String?,
      esEgresado: json['es_egresado'] as bool? ?? false,
      alumnoId: json['alumno_id'] as int?,
      docenteId: json['docente_id'] as int?,
    );
  }

  final int id;
  final String name;
  final String? apellidos;
  final String email;
  final String? foto;
  final List<String> roles;
  final bool beca;

  /// `condicion` del usuario en la web (Regular, Egresado…).
  final String? condicion;

  /// Egresado: la web le da su propia intranet (titulación, bolsa, comunicados),
  /// sin matrícula, cursos del periodo, tutoría ni investigaciones.
  final bool esEgresado;
  final int? alumnoId;
  final int? docenteId;

  bool get isAlumno => alumnoId != null || roles.contains('alumno');
  bool get isDocente => docenteId != null || roles.contains('docente');
  bool get isTutor => roles.contains('tutor');
  bool get isDocenteOTutor => isDocente || isTutor;
}
