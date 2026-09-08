/// Espejo de la respuesta de `GET /api/v1/me`.
class AuthUser {
  const AuthUser({
    required this.id,
    required this.name,
    required this.apellidos,
    required this.email,
    required this.roles,
    required this.alumnoId,
    required this.docenteId,
  });

  factory AuthUser.fromJson(Map<String, dynamic> json) {
    return AuthUser(
      id: json['id'] as int,
      name: json['name'] as String,
      apellidos: json['apellidos'] as String?,
      email: json['email'] as String,
      roles: List<String>.from(json['roles'] as List? ?? const []),
      alumnoId: json['alumno_id'] as int?,
      docenteId: json['docente_id'] as int?,
    );
  }

  final int id;
  final String name;
  final String? apellidos;
  final String email;
  final List<String> roles;
  final int? alumnoId;
  final int? docenteId;

  bool get isAlumno => alumnoId != null;
  bool get isDocente => docenteId != null;
}
