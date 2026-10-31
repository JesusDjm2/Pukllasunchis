/// Espejo de `DocenteCursoResource` (`GET /api/v1/docente/cursos`).
/// `progreso` viene con `parcial_1/parcial_2/desempeno` en FID y con
/// `calificado` en PPD — se dejan las claves tal cual las manda la API.
class DocenteCurso {
  const DocenteCurso({
    required this.id,
    required this.nombre,
    required this.area,
    required this.ciclo,
    required this.programa,
    required this.silaboListo,
    required this.progreso,
  });

  factory DocenteCurso.fromJson(Map<String, dynamic> json) {
    return DocenteCurso(
      id: json['id'] as int,
      nombre: json['nombre'] as String,
      area: json['area'] as String,
      ciclo: json['ciclo'] as String?,
      programa: json['programa'] as String?,
      silaboListo: json['silabo_listo'] as bool,
      progreso: (json['progreso'] as Map).map(
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
  final bool silaboListo;
  final Map<String, double> progreso;

  bool get esPpd => area == 'ppd';
}
