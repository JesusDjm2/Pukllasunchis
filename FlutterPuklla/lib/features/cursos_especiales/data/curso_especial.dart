/// Espejo de `CursoEspecialResource` (`GET /api/v1/cursos-especiales`), la
/// entrada del catálogo.
class CursoEspecial {
  const CursoEspecial({
    required this.id,
    required this.nombre,
    required this.descripcion,
    required this.imagenUrl,
    required this.inscrito,
    required this.porcentaje,
  });

  factory CursoEspecial.fromJson(Map<String, dynamic> json) {
    return CursoEspecial(
      id: json['id'] as int,
      nombre: json['nombre'] as String,
      descripcion: json['descripcion'] as String?,
      imagenUrl: json['imagen_url'] as String?,
      inscrito: json['inscrito'] as bool,
      porcentaje: json['porcentaje'] as int?,
    );
  }

  final int id;
  final String nombre;
  final String? descripcion;
  final String? imagenUrl;
  final bool inscrito;

  /// Solo si [inscrito] es `true`.
  final int? porcentaje;
}
