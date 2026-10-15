/// Espejo de `ComunicadoResource` (`GET /api/v1/comunicados`). `archivo_url`
/// ya viene como URL absoluta (`asset()` de Laravel), no relativa a
/// `API_BASE_URL`.
class Comunicado {
  const Comunicado({
    required this.id,
    required this.titulo,
    required this.descripcion,
    required this.archivoUrl,
    required this.archivoTipo,
    required this.fechaPublicacion,
  });

  factory Comunicado.fromJson(Map<String, dynamic> json) {
    return Comunicado(
      id: json['id'] as int,
      titulo: json['titulo'] as String,
      descripcion: json['descripcion'] as String?,
      archivoUrl: json['archivo_url'] as String,
      archivoTipo: json['archivo_tipo'] as String,
      fechaPublicacion: DateTime.parse(json['fecha_publicacion'] as String),
    );
  }

  final int id;
  final String titulo;
  final String? descripcion;
  final String archivoUrl;

  /// `'imagen'` o `'pdf'` (enum de la BD).
  final String archivoTipo;
  final DateTime fechaPublicacion;

  bool get esImagen => archivoTipo == 'imagen';
}
