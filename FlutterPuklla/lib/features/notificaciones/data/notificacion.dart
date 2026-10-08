/// Espejo de `NotificacionResource` (`GET /api/v1/notificaciones`). `datos`
/// es el payload propio de cada `tipo` (ver `app/Notifications/*.php`) — no
/// se tipa campo por campo aquí, cada pantalla lee solo lo que necesita.
class Notificacion {
  const Notificacion({
    required this.id,
    required this.tipo,
    required this.datos,
    required this.leida,
    required this.createdAt,
  });

  factory Notificacion.fromJson(Map<String, dynamic> json) {
    return Notificacion(
      id: json['id'] as String,
      tipo: json['tipo'] as String,
      datos: json['datos'] as Map<String, dynamic>,
      leida: json['leida'] as bool,
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }

  final String id;

  /// `CalificacionRegistrada` | `ComunicadoPublicado` | `MatriculaVoucherVerificado`.
  final String tipo;
  final Map<String, dynamic> datos;
  final bool leida;
  final DateTime createdAt;

  String get mensaje => datos['mensaje'] as String? ?? 'Tienes una notificación nueva.';
}
