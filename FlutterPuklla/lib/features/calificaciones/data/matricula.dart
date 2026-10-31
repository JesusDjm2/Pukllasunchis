/// Espejo de `MatriculaResource` (`GET /api/v1/matriculas`).
class Matricula {
  const Matricula({
    required this.id,
    required this.periodoActualId,
    required this.periodoNombre,
    required this.estado,
    required this.comprobante,
    required this.fechaCompletado,
    required this.createdAt,
  });

  factory Matricula.fromJson(Map<String, dynamic> json) {
    return Matricula(
      id: json['id'] as int,
      periodoActualId: json['periodo_actual_id'] as int,
      periodoNombre: json['periodo_nombre'] as String?,
      estado: json['estado'] as String,
      comprobante: json['comprobante'] as String?,
      fechaCompletado: json['fecha_completado'] == null
          ? null
          : DateTime.tryParse(json['fecha_completado'] as String),
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }

  final int id;
  final int periodoActualId;
  final String? periodoNombre;
  final String estado;
  final String? comprobante;
  final DateTime? fechaCompletado;
  final DateTime createdAt;
}
