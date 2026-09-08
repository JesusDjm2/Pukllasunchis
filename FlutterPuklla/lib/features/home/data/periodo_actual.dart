/// Espejo de `PeriodoActualResource` (`GET /api/v1/periodo-actual`).
/// `fecha_inicio`/`fecha_cierre` viajan como datetime ISO (cast `date` de
/// Eloquent serializado por Carbon), aunque solo importa la parte de fecha.
class PeriodoActual {
  const PeriodoActual({
    required this.id,
    required this.nombre,
    required this.horario,
    required this.fechaInicio,
    required this.fechaCierre,
    required this.formularioHabilitado,
  });

  factory PeriodoActual.fromJson(Map<String, dynamic> json) {
    return PeriodoActual(
      id: json['id'] as int,
      nombre: json['nombre'] as String,
      horario: json['horario'] as String?,
      fechaInicio: _parseDate(json['fecha_inicio'] as String?),
      fechaCierre: _parseDate(json['fecha_cierre'] as String?),
      formularioHabilitado: json['formulario_habilitado'] as bool,
    );
  }

  final int id;
  final String nombre;
  final String? horario;
  final DateTime? fechaInicio;
  final DateTime? fechaCierre;
  final bool formularioHabilitado;
}

DateTime? _parseDate(String? value) => value == null ? null : DateTime.tryParse(value);
