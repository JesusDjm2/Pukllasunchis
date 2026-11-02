// Modelos de datos para el módulo de Calificaciones del Docente (FID y PPD)
// y motor reactivo de conversión cualitativa (rúbricas 1-5) a escala vigesimal (1-20).

class DocenteCalificarCursoInfo {
  const DocenteCalificarCursoInfo({
    required this.id,
    required this.nombre,
    required this.area,
    required this.esPpd,
    this.ciclo,
    this.programa,
  });

  factory DocenteCalificarCursoInfo.fromJson(Map<String, dynamic> json) {
    return DocenteCalificarCursoInfo(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}') ?? 0,
      nombre: json['nombre'] as String? ?? 'Curso',
      area: json['area'] as String? ?? 'fid',
      esPpd: json['es_ppd'] as bool? ?? false,
      ciclo: json['ciclo'] as String?,
      programa: json['programa'] as String?,
    );
  }

  final int id;
  final String nombre;
  final String area;
  final bool esPpd;
  final String? ciclo;
  final String? programa;
}

class DocentePeriodoInfo {
  const DocentePeriodoInfo({
    this.nombre,
    required this.parcial1Habilitado,
    required this.parcial2Habilitado,
  });

  factory DocentePeriodoInfo.fromJson(Map<String, dynamic> json) {
    return DocentePeriodoInfo(
      nombre: json['nombre'] as String?,
      parcial1Habilitado: json['parcial1_habilitado'] as bool? ?? true,
      parcial2Habilitado: json['parcial2_habilitado'] as bool? ?? true,
    );
  }

  final String? nombre;
  final bool parcial1Habilitado;
  final bool parcial2Habilitado;
}

class DocenteCompetenciaInfo {
  const DocenteCompetenciaInfo({
    required this.id,
    required this.nombre,
    this.descripcion,
  });

  factory DocenteCompetenciaInfo.fromJson(Map<String, dynamic> json) {
    return DocenteCompetenciaInfo(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}') ?? 0,
      nombre: json['nombre'] as String? ?? 'Competencia',
      descripcion: json['descripcion'] as String?,
    );
  }

  final int id;
  final String nombre;
  final String? descripcion;
}

class PeriodoRegistro {
  const PeriodoRegistro({
    this.valoracion1,
    this.valoracion2,
    this.valoracion3,
    this.valoracionCurso,
    this.calificacionCurso,
    this.calificacionSistema,
    this.observaciones,
  });

  factory PeriodoRegistro.fromJson(Map<String, dynamic> json) {
    int? asInt(dynamic v) => v == null ? null : (v is int ? v : int.tryParse('$v'));
    double? asDouble(dynamic v) => v == null ? null : (v is double ? v : double.tryParse('$v'));

    return PeriodoRegistro(
      valoracion1: asInt(json['valoracion_1']),
      valoracion2: asInt(json['valoracion_2']),
      valoracion3: asInt(json['valoracion_3']),
      valoracionCurso: asDouble(json['valoracion_curso']),
      calificacionCurso: json['calificacion_curso'] as String?,
      calificacionSistema: asInt(json['calificacion_sistema']),
      observaciones: json['observaciones'] as String?,
    );
  }

  final int? valoracion1;
  final int? valoracion2;
  final int? valoracion3;
  final double? valoracionCurso;
  final String? calificacionCurso;
  final int? calificacionSistema;
  final String? observaciones;

  PeriodoRegistro copyWith({
    int? valoracion1,
    int? valoracion2,
    int? valoracion3,
    double? valoracionCurso,
    String? calificacionCurso,
    int? calificacionSistema,
    String? observaciones,
    bool clearObservaciones = false,
  }) {
    return PeriodoRegistro(
      valoracion1: valoracion1 ?? this.valoracion1,
      valoracion2: valoracion2 ?? this.valoracion2,
      valoracion3: valoracion3 ?? this.valoracion3,
      valoracionCurso: valoracionCurso ?? this.valoracionCurso,
      calificacionCurso: calificacionCurso ?? this.calificacionCurso,
      calificacionSistema: calificacionSistema ?? this.calificacionSistema,
      observaciones: clearObservaciones ? null : (observaciones ?? this.observaciones),
    );
  }

  Map<String, dynamic> toJson(int alumnoId) {
    return {
      'alumno_id': alumnoId,
      'valoracion_1': valoracion1,
      'valoracion_2': valoracion2,
      'valoracion_3': valoracion3,
      'valoracion_curso': valoracionCurso,
      'calificacion_curso': calificacionCurso,
      'calificacion_sistema': calificacionSistema,
      'observaciones': observaciones,
    };
  }
}

class PpdRegistro {
  const PpdRegistro({
    this.ppC1_1,
    this.ppC1_2,
    this.ppC1_4,
    this.pfC1_1,
    this.pfC1_2,
    this.pfC1_3,
    this.pgC1_3,

    this.ppC2_1,
    this.ppC2_2,
    this.ppC2_4,
    this.pfC2_1,
    this.pfC2_2,
    this.pfC2_3,
    this.pgC2_3,

    this.ppC3_1,
    this.ppC3_2,
    this.ppC3_4,
    this.pfC3_1,
    this.pfC3_2,
    this.pfC3_3,
    this.pgC3_3,

    this.calificacionCurso,
    this.calificacionSistema,
    this.nivelDesempeno,
    this.observaciones,
  });

  factory PpdRegistro.fromJson(Map<String, dynamic> json) {
    int? asInt(dynamic v) => v == null ? null : (v is int ? v : int.tryParse('$v'));
    double? asDouble(dynamic v) => v == null ? null : (v is double ? v : double.tryParse('$v'));

    return PpdRegistro(
      ppC1_1: asInt(json['pp_c1_1']),
      ppC1_2: asInt(json['pp_c1_2']),
      ppC1_4: asInt(json['pp_c1_4']),
      pfC1_1: asInt(json['pf_c1_1']),
      pfC1_2: asInt(json['pf_c1_2']),
      pfC1_3: asInt(json['pf_c1_3']),
      pgC1_3: asInt(json['pg_c1_3']),

      ppC2_1: asInt(json['pp_c2_1']),
      ppC2_2: asInt(json['pp_c2_2']),
      ppC2_4: asInt(json['pp_c2_4']),
      pfC2_1: asInt(json['pf_c2_1']),
      pfC2_2: asInt(json['pf_c2_2']),
      pfC2_3: asInt(json['pf_c2_3']),
      pgC2_3: asInt(json['pg_c2_3']),

      ppC3_1: asInt(json['pp_c3_1']),
      ppC3_2: asInt(json['pp_c3_2']),
      ppC3_4: asInt(json['pp_c3_4']),
      pfC3_1: asInt(json['pf_c3_1']),
      pfC3_2: asInt(json['pf_c3_2']),
      pfC3_3: asInt(json['pf_c3_3']),
      pgC3_3: asInt(json['pg_c3_3']),

      calificacionCurso: asDouble(json['calificacion_curso']),
      calificacionSistema: asInt(json['calificacion_sistema']),
      nivelDesempeno: json['nivel_desempeno']?.toString(),
      observaciones: json['observaciones'] as String?,
    );
  }

  // Competencia 1
  final int? ppC1_1;
  final int? ppC1_2;
  final int? ppC1_4;
  final int? pfC1_1;
  final int? pfC1_2;
  final int? pfC1_3;
  final int? pgC1_3;

  // Competencia 2
  final int? ppC2_1;
  final int? ppC2_2;
  final int? ppC2_4;
  final int? pfC2_1;
  final int? pfC2_2;
  final int? pfC2_3;
  final int? pgC2_3;

  // Competencia 3
  final int? ppC3_1;
  final int? ppC3_2;
  final int? ppC3_4;
  final int? pfC3_1;
  final int? pfC3_2;
  final int? pfC3_3;
  final int? pgC3_3;

  // Evaluaciones Finales
  final double? calificacionCurso;
  final int? calificacionSistema;
  final String? nivelDesempeno;
  final String? observaciones;

  /// Obtiene un indicador por índice de competencia (0..2)
  int? getParticipacion(int compIdx) => switch (compIdx) {
        0 => ppC1_1,
        1 => ppC2_1,
        _ => ppC3_1,
      };

  int? getActividad(int compIdx) => switch (compIdx) {
        0 => ppC1_2,
        1 => ppC2_2,
        _ => ppC3_2,
      };

  int? getPromedioProceso(int compIdx) => switch (compIdx) {
        0 => ppC1_4,
        1 => ppC2_4,
        _ => ppC3_4,
      };

  int? getAutoevaluacion(int compIdx) => switch (compIdx) {
        0 => pfC1_1,
        1 => pfC2_1,
        _ => pfC3_1,
      };

  int? getEvaluacion(int compIdx) => switch (compIdx) {
        0 => pfC1_2,
        1 => pfC2_2,
        _ => pfC3_2,
      };

  int? getPromedioFinal(int compIdx) => switch (compIdx) {
        0 => pfC1_3,
        1 => pfC2_3,
        _ => pfC3_3,
      };

  int? getValoracion(int compIdx) => switch (compIdx) {
        0 => pgC1_3,
        1 => pgC2_3,
        _ => pgC3_3,
      };

  /// Conversión oficial de puntaje vigesimal del sistema (0-20) a la escala de curso (1.0-4.0)
  static double? convertirCalificacionCurso(double? valor) {
    if (valor == null) return null;
    if (valor >= 1 && valor < 1.5) return 1.0;
    if (valor >= 1.5 && valor < 2.5) return 1.1;
    if (valor >= 2.5 && valor < 3.5) return 1.2;
    if (valor >= 3.5 && valor < 4.5) return 1.3;
    if (valor >= 4.5 && valor < 5.5) return 1.4;
    if (valor >= 5.5 && valor < 6.5) return 1.5;
    if (valor >= 6.5 && valor < 7.5) return 1.6;
    if (valor >= 7.5 && valor < 8.5) return 1.7;
    if (valor >= 8.5 && valor < 9.5) return 1.8;
    if (valor >= 9.5 && valor < 10.5) return 1.9;
    if (valor >= 10.5 && valor < 11.5) return 2.2;
    if (valor >= 11.5 && valor < 12.5) return 2.5;
    if (valor >= 12.5 && valor < 13.5) return 2.7;
    if (valor >= 13.5 && valor < 14.5) return 2.9;
    if (valor >= 14.5 && valor < 15.5) return 3.1;
    if (valor >= 15.5 && valor < 16.5) return 3.3;
    if (valor >= 16.5 && valor < 17.5) return 3.5;
    if (valor >= 17.5 && valor < 18.5) return 3.7;
    if (valor >= 18.5 && valor < 19.5) return 3.9;
    if (valor >= 19.5 && valor <= 20) return 4.0;
    return null;
  }

  /// Recalcula todos los promedios derivados de la fila de forma reactiva
  PpdRegistro conNota({
    required int compIdx,
    required String campo,
    required int? valor,
    required int totalCompetencias,
  }) {
    int? p1 = compIdx == 0 ? (campo == 'participacion' ? valor : ppC1_1) : (compIdx == 1 ? (campo == 'participacion' ? valor : ppC2_1) : (campo == 'participacion' ? valor : ppC3_1));
    int? p2 = compIdx == 0 ? (campo == 'actividad' ? valor : ppC1_2) : (compIdx == 1 ? (campo == 'actividad' ? valor : ppC2_2) : (campo == 'actividad' ? valor : ppC3_2));
    int? f1 = compIdx == 0 ? (campo == 'autoevaluacion' ? valor : pfC1_1) : (compIdx == 1 ? (campo == 'autoevaluacion' ? valor : pfC2_1) : (campo == 'autoevaluacion' ? valor : pfC3_1));
    int? f2 = compIdx == 0 ? (campo == 'evaluacion' ? valor : pfC1_2) : (compIdx == 1 ? (campo == 'evaluacion' ? valor : pfC2_2) : (campo == 'evaluacion' ? valor : pfC3_2));

    int? p4;
    if (p1 != null || p2 != null) {
      p4 = (((p1 ?? 0) + (p2 ?? 0)) / 2).round();
    }

    int? f3;
    if (f1 != null || f2 != null) {
      f3 = (((f1 ?? 0) + (f2 ?? 0)) / 2).round();
    }

    int? pg3;
    if (p4 != null || f3 != null) {
      pg3 = (((p4 ?? 0) * 0.4) + ((f3 ?? 0) * 0.6)).round();
    }

    // Copiar registro con los nuevos valores de la competencia
    PpdRegistro regActualizado = copyWith(
      ppC1_1: compIdx == 0 ? p1 : ppC1_1,
      ppC1_2: compIdx == 0 ? p2 : ppC1_2,
      ppC1_4: compIdx == 0 ? p4 : ppC1_4,
      pfC1_1: compIdx == 0 ? f1 : pfC1_1,
      pfC1_2: compIdx == 0 ? f2 : pfC1_2,
      pfC1_3: compIdx == 0 ? f3 : pfC1_3,
      pgC1_3: compIdx == 0 ? pg3 : pgC1_3,

      ppC2_1: compIdx == 1 ? p1 : ppC2_1,
      ppC2_2: compIdx == 1 ? p2 : ppC2_2,
      ppC2_4: compIdx == 1 ? p4 : ppC2_4,
      pfC2_1: compIdx == 1 ? f1 : pfC2_1,
      pfC2_2: compIdx == 1 ? f2 : pfC2_2,
      pfC2_3: compIdx == 1 ? f3 : pfC2_3,
      pgC2_3: compIdx == 1 ? pg3 : pgC2_3,

      ppC3_1: compIdx == 2 ? p1 : ppC3_1,
      ppC3_2: compIdx == 2 ? p2 : ppC3_2,
      ppC3_4: compIdx == 2 ? p4 : ppC3_4,
      pfC3_1: compIdx == 2 ? f1 : pfC3_1,
      pfC3_2: compIdx == 2 ? f2 : pfC3_2,
      pfC3_3: compIdx == 2 ? f3 : pfC3_3,
      pgC3_3: compIdx == 2 ? pg3 : pgC3_3,
    );

    // Recalcular notas finales consolidadas
    final vals = <int>[];
    if (regActualizado.pgC1_3 != null && totalCompetencias >= 1) vals.add(regActualizado.pgC1_3!);
    if (regActualizado.pgC2_3 != null && totalCompetencias >= 2) vals.add(regActualizado.pgC2_3!);
    if (regActualizado.pgC3_3 != null && totalCompetencias >= 3) vals.add(regActualizado.pgC3_3!);

    int? nuevoSistema;
    double? nuevoCurso;
    String? nuevoDesempeno;

    if (vals.isNotEmpty) {
      final prom = vals.reduce((a, b) => a + b) / vals.length;
      nuevoSistema = prom.round();
      nuevoCurso = convertirCalificacionCurso(nuevoSistema.toDouble());
      nuevoDesempeno = nuevoCurso?.floor().toString();
    }

    return regActualizado.copyWith(
      calificacionSistema: nuevoSistema,
      calificacionCurso: nuevoCurso,
      nivelDesempeno: nuevoDesempeno,
    );
  }

  PpdRegistro copyWith({
    int? ppC1_1,
    int? ppC1_2,
    int? ppC1_4,
    int? pfC1_1,
    int? pfC1_2,
    int? pfC1_3,
    int? pgC1_3,

    int? ppC2_1,
    int? ppC2_2,
    int? ppC2_4,
    int? pfC2_1,
    int? pfC2_2,
    int? pfC2_3,
    int? pgC2_3,

    int? ppC3_1,
    int? ppC3_2,
    int? ppC3_4,
    int? pfC3_1,
    int? pfC3_2,
    int? pfC3_3,
    int? pgC3_3,

    double? calificacionCurso,
    int? calificacionSistema,
    String? nivelDesempeno,
    String? observaciones,
  }) {
    return PpdRegistro(
      ppC1_1: ppC1_1 ?? this.ppC1_1,
      ppC1_2: ppC1_2 ?? this.ppC1_2,
      ppC1_4: ppC1_4 ?? this.ppC1_4,
      pfC1_1: pfC1_1 ?? this.pfC1_1,
      pfC1_2: pfC1_2 ?? this.pfC1_2,
      pfC1_3: pfC1_3 ?? this.pfC1_3,
      pgC1_3: pgC1_3 ?? this.pgC1_3,

      ppC2_1: ppC2_1 ?? this.ppC2_1,
      ppC2_2: ppC2_2 ?? this.ppC2_2,
      ppC2_4: ppC2_4 ?? this.ppC2_4,
      pfC2_1: pfC2_1 ?? this.pfC2_1,
      pfC2_2: pfC2_2 ?? this.pfC2_2,
      pfC2_3: pfC2_3 ?? this.pfC2_3,
      pgC2_3: pgC2_3 ?? this.pgC2_3,

      ppC3_1: ppC3_1 ?? this.ppC3_1,
      ppC3_2: ppC3_2 ?? this.ppC3_2,
      ppC3_4: ppC3_4 ?? this.ppC3_4,
      pfC3_1: pfC3_1 ?? this.pfC3_1,
      pfC3_2: pfC3_2 ?? this.pfC3_2,
      pfC3_3: pfC3_3 ?? this.pfC3_3,
      pgC3_3: pgC3_3 ?? this.pgC3_3,

      calificacionCurso: calificacionCurso ?? this.calificacionCurso,
      calificacionSistema: calificacionSistema ?? this.calificacionSistema,
      nivelDesempeno: nivelDesempeno ?? this.nivelDesempeno,
      observaciones: observaciones ?? this.observaciones,
    );
  }

  Map<String, dynamic> toJson({required int id, int? ppdId}) {
    return {
      'id': id,
      'ppd_id': ppdId,

      'pp_c1_1': ppC1_1,
      'pp_c1_2': ppC1_2,
      'pp_c1_4': ppC1_4,
      'pf_c1_1': pfC1_1,
      'pf_c1_2': pfC1_2,
      'pf_c1_3': pfC1_3,
      'pg_c1_3': pgC1_3,

      'pp_c2_1': ppC2_1,
      'pp_c2_2': ppC2_2,
      'pp_c2_4': ppC2_4,
      'pf_c2_1': pfC2_1,
      'pf_c2_2': pfC2_2,
      'pf_c2_3': pfC2_3,
      'pg_c2_3': pgC2_3,

      'pp_c3_1': ppC3_1,
      'pp_c3_2': ppC3_2,
      'pp_c3_4': ppC3_4,
      'pf_c3_1': pfC3_1,
      'pf_c3_2': pfC3_2,
      'pf_c3_3': pfC3_3,
      'pg_c3_3': pgC3_3,

      'calificacion_curso': calificacionCurso,
      'calificacion_sistema': calificacionSistema,
      'nivel_desempeno': nivelDesempeno,
      'observaciones': observaciones,
    };
  }
}

class DocenteAlumnoCalificacion {
  const DocenteAlumnoCalificacion({
    required this.id,
    this.ppdId,
    required this.nombreCompleto,
    this.dni,
    required this.esInhabilitado,
    required this.noMatriculado,
    this.p1,
    this.p2,
    this.p3,
    this.ppd,
  });

  factory DocenteAlumnoCalificacion.fromJson(Map<String, dynamic> json) {
    return DocenteAlumnoCalificacion(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}') ?? 0,
      ppdId: json['ppd_id'] is int ? json['ppd_id'] as int : int.tryParse('${json['ppd_id']}'),
      nombreCompleto: json['nombre_completo'] as String? ?? 'Alumno',
      dni: json['dni'] as String?,
      esInhabilitado: json['es_inhabilitado'] as bool? ?? false,
      noMatriculado: json['no_matriculado'] as bool? ?? false,
      p1: json['p1'] != null ? PeriodoRegistro.fromJson(json['p1'] as Map<String, dynamic>) : null,
      p2: json['p2'] != null ? PeriodoRegistro.fromJson(json['p2'] as Map<String, dynamic>) : null,
      p3: json['p3'] != null ? PeriodoRegistro.fromJson(json['p3'] as Map<String, dynamic>) : null,
      ppd: json['calificacion'] != null ? PpdRegistro.fromJson(json['calificacion'] as Map<String, dynamic>) : null,
    );
  }

  final int id;
  final int? ppdId;
  final String nombreCompleto;
  final String? dni;
  final bool esInhabilitado;
  final bool noMatriculado;
  final PeriodoRegistro? p1;
  final PeriodoRegistro? p2;
  final PeriodoRegistro? p3;
  final PpdRegistro? ppd;

  DocenteAlumnoCalificacion copyWith({
    PeriodoRegistro? p1,
    PeriodoRegistro? p2,
    PeriodoRegistro? p3,
    PpdRegistro? ppd,
  }) {
    return DocenteAlumnoCalificacion(
      id: id,
      ppdId: ppdId,
      nombreCompleto: nombreCompleto,
      dni: dni,
      esInhabilitado: esInhabilitado,
      noMatriculado: noMatriculado,
      p1: p1 ?? this.p1,
      p2: p2 ?? this.p2,
      p3: p3 ?? this.p3,
      ppd: ppd ?? this.ppd,
    );
  }
}

class DocenteCalificarData {
  const DocenteCalificarData({
    required this.curso,
    required this.periodo,
    required this.competencias,
    required this.alumnos,
  });

  factory DocenteCalificarData.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>? ?? json;

    return DocenteCalificarData(
      curso: DocenteCalificarCursoInfo.fromJson(data['curso'] as Map<String, dynamic>? ?? {}),
      periodo: DocentePeriodoInfo.fromJson(data['periodo'] as Map<String, dynamic>? ?? {}),
      competencias: (data['competencias'] as List<dynamic>? ?? [])
          .map((c) => DocenteCompetenciaInfo.fromJson(c as Map<String, dynamic>))
          .toList(),
      alumnos: (data['alumnos'] as List<dynamic>? ?? [])
          .map((a) => DocenteAlumnoCalificacion.fromJson(a as Map<String, dynamic>))
          .toList(),
    );
  }

  final DocenteCalificarCursoInfo curso;
  final DocentePeriodoInfo periodo;
  final List<DocenteCompetenciaInfo> competencias;
  final List<DocenteAlumnoCalificacion> alumnos;

  // -------------------------------------------------------------
  // MOTOR DE CÁLCULO REACTIVO PARA FID
  // -------------------------------------------------------------

  /// Convierte el promedio cualitativo (1.00 a 5.00) en nota vigesimal (1 a 20)
  /// idéntico al motor JavaScript oficial de la institución.
  static int? calcularValoracionVigesimal(double? valor) {
    if (valor == null || valor <= 0) return null;
    if (valor <= 1.144) return 1;
    if (valor <= 1.344) return 2;
    if (valor <= 1.544) return 3;
    if (valor <= 1.744) return 4;
    if (valor <= 1.944) return 5;
    if (valor <= 2.144) return 6;
    if (valor <= 2.344) return 7;
    if (valor <= 2.544) return 8;
    if (valor <= 2.744) return 9;
    if (valor <= 2.944) return 10;
    if (valor <= 3.244) return 11;
    if (valor <= 3.544) return 12;
    if (valor <= 3.744) return 13;
    if (valor <= 3.944) return 14;
    if (valor <= 4.144) return 15;
    if (valor <= 4.344) return 16;
    if (valor <= 4.544) return 17;
    if (valor <= 4.744) return 18;
    if (valor <= 4.944) return 19;
    if (valor <= 5.000) return 20;
    return null;
  }

  /// Calcula el nivel de logro a partir de la nota vigesimal (1 a 20).
  static String? calcularNivelLogro(int? nota) {
    if (nota == null) return null;
    if (nota <= 5) return 'Previo al inicio';
    if (nota <= 10) return 'Inicio';
    if (nota <= 14) return 'En proceso';
    if (nota <= 19) return 'Logrado';
    if (nota >= 20) return 'Destacado';
    return null;
  }

  /// Calcula el promedio de valoraciones según el número de competencias asignadas al curso.
  static double? calcularPromedio(List<int?> valoraciones, int totalCompetencias) {
    if (totalCompetencias <= 0) return null;
    final validos = valoraciones.where((v) => v != null && v > 0).cast<int>().toList();
    if (validos.isEmpty) return null;
    final suma = validos.fold<int>(0, (prev, elem) => prev + elem);
    // Se divide entre el número oficial de competencias asignadas al curso
    final promedio = suma / totalCompetencias;
    return double.parse(promedio.toStringAsFixed(2));
  }
}
