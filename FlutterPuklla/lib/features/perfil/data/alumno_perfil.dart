/// Espejo (parcial) de `AlumnoResource` (`GET /api/v1/alumno/perfil`) — solo
/// los campos que la "ficha técnica" realmente muestra (ver
/// `alumnos/vistasAlumnos/index.blade.php`, sección "Datos personales" +
/// "Programa y ciclo"). El resto de campos del recurso (socioeconómicos,
/// vivienda, salud) son del formulario de matrícula, no de este resumen.
class AlumnoPerfil {
  const AlumnoPerfil({
    required this.id,
    required this.nombres,
    required this.apellidos,
    required this.dni,
    required this.email,
    required this.numero,
    required this.numeroReferencia,
    required this.departamento,
    required this.provincia,
    required this.distrito,
    required this.direccion,
    required this.programaId,
    required this.programaNombre,
    required this.cicloId,
    required this.cicloNombre,
  });

  factory AlumnoPerfil.fromJson(Map<String, dynamic> json) {
    return AlumnoPerfil(
      id: json['id'] as int,
      nombres: json['nombres'] as String,
      apellidos: json['apellidos'] as String,
      dni: json['dni'] as String,
      email: json['email'] as String,
      numero: json['numero'] as String,
      numeroReferencia: json['numero_referencia'] as String,
      departamento: json['departamento'] as String?,
      provincia: json['provincia'] as String?,
      distrito: json['distrito'] as String?,
      direccion: json['direccion'] as String,
      programaId: json['programa_id'] as int,
      programaNombre: json['programa_nombre'] as String?,
      cicloId: json['ciclo_id'] as int,
      cicloNombre: json['ciclo_nombre'] as String?,
    );
  }

  final int id;
  final String nombres;
  final String apellidos;
  final String dni;
  final String email;
  final String numero;
  final String numeroReferencia;
  final String? departamento;
  final String? provincia;
  final String? distrito;
  final String direccion;
  final int programaId;
  final String? programaNombre;
  final int cicloId;
  final String? cicloNombre;

  String get nombreCompleto => '$nombres $apellidos';

  String get domicilio {
    final ubicacion = [
      departamento,
      provincia,
      distrito,
    ].where((s) => s != null && s.isNotEmpty).join(' — ');
    return ubicacion.isEmpty ? direccion : '$ubicacion, $direccion';
  }
}
