int _asInt(dynamic v) {
  if (v == null) return 0;
  if (v is int) return v;
  if (v is num) return v.toInt();
  if (v is String) return int.tryParse(v) ?? 0;
  return 0;
}

/// Espejo de `DocenteCursoResource` (`GET /api/v1/docente/cursos`).
/// `progreso` viene con `parcial_1/parcial_2/desempeno` en FID y con
/// `calificado` en PPD — se dejan las claves tal cual las manda la API.
class DocenteCurso {
  const DocenteCurso({
    required this.id,
    required this.nombre,
    required this.area,
    this.ciclo,
    this.programa,
    this.creditos = 0,
    this.horas = 0,
    this.alumnosCount = 0,
    required this.silaboListo,
    required this.progreso,
  });

  factory DocenteCurso.fromJson(Map<String, dynamic> json) {
    return DocenteCurso(
      id: _asInt(json['id']),
      nombre: json['nombre'] as String? ?? '',
      area: json['area'] as String? ?? 'fid',
      ciclo: json['ciclo'] as String?,
      programa: json['programa'] as String?,
      creditos: _asInt(json['creditos']),
      horas: _asInt(json['horas']),
      alumnosCount: _asInt(json['alumnos_count']),
      silaboListo: json['silabo_listo'] as bool? ?? false,
      progreso: ((json['progreso'] as Map?) ?? {}).map(
        (clave, valor) => MapEntry(clave as String, (valor as num).toDouble()),
      ),
    );
  }

  final int id;
  final String nombre;

  /// `'fid'` o `'ppd'`, según el programa del ciclo.
  final String area;
  final String? ciclo;
  final String? programa;
  final int creditos;
  final int horas;
  final int alumnosCount;
  final bool silaboListo;
  final Map<String, double> progreso;

  bool get esPpd => area == 'ppd';
}
