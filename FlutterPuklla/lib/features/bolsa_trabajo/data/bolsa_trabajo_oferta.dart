class BolsaTrabajoOferta {
  const BolsaTrabajoOferta({
    required this.id,
    required this.nombre,
    required this.detalles,
    this.imagenUrl,
    this.fechaPublicacion,
    this.vigente = false,
    this.nombrePublicador,
    this.telefonoPublicador,
  });

  factory BolsaTrabajoOferta.fromJson(Map<String, dynamic> json) {
    return BolsaTrabajoOferta(
      id: json['id'] as int,
      nombre: json['nombre'] as String? ?? 'Convocatoria laboral',
      detalles: json['detalles'] as String? ?? '',
      imagenUrl: json['imagen_url'] as String?,
      fechaPublicacion: json['fecha_publicacion'] != null
          ? DateTime.tryParse(json['fecha_publicacion'] as String)
          : null,
      vigente: json['vigente'] as bool? ?? false,
      nombrePublicador: json['nombre_publicador'] as String?,
      telefonoPublicador: json['telefono_publicador'] as String?,
    );
  }

  final int id;
  final String nombre;
  final String detalles;
  final String? imagenUrl;
  final DateTime? fechaPublicacion;
  final bool vigente;
  final String? nombrePublicador;
  final String? telefonoPublicador;

  /// Limpia etiquetas HTML básicas para el resumen de texto
  String get resumenLimpio {
    final sinTags = detalles.replaceAll(RegExp(r'<[^>]*>|&nbsp;|&amp;|&quot;'), ' ');
    final limpio = sinTags.replaceAll(RegExp(r'\s+'), ' ').trim();
    return limpio.length > 160 ? '${limpio.substring(0, 160)}...' : limpio;
  }
}
