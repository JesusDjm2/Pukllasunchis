int? _toInt(dynamic value) {
  if (value == null) return null;
  if (value is int) return value;
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value);
  return null;
}

/// `{valoracion_curso, calificacion_curso, calificacion_sistema}` de un
/// parcial (Parcial 1 / Parcial 2 / Promedio) — `null` si el alumno aún no
/// tiene esa nota registrada.
class NotaParcial {
  const NotaParcial({
    this.valoracionCurso,
    this.calificacionCurso,
    this.calificacionSistema,
  });

  factory NotaParcial.fromJson(Map<String, dynamic> json) {
    return NotaParcial(
      valoracionCurso: json['valoracion_curso']?.toString(),
      calificacionCurso: json['calificacion_curso']?.toString(),
      calificacionSistema: json['calificacion_sistema']?.toString(),
    );
  }

  final String? valoracionCurso;
  final String? calificacionCurso;
  final String? calificacionSistema;
}

/// Un curso del período activo con sus tres parciales (`Parcial 1`/`Parcial
/// 2`/`Promedio` — tres modelos separados en el backend: `PeriodoUno`,
/// `PeriodoDos`, `PeriodoTres`).
class CursoConNotas {
  const CursoConNotas({
    required this.cursoId,
    required this.cursoNombre,
    this.cicloNombre,
    this.programaNombre,
    this.creditos,
    this.horas,
    this.parcial1,
    this.parcial2,
    this.promedio,
    this.observaciones,
  });

  factory CursoConNotas.fromJson(Map<String, dynamic> json) {
    NotaParcial? parcial(String key) {
      final value = json[key];
      return value is Map<String, dynamic> ? NotaParcial.fromJson(value) : null;
    }

    return CursoConNotas(
      cursoId: _toInt(json['curso_id']) ?? 0,
      cursoNombre: json['curso_nombre']?.toString() ?? '',
      cicloNombre: json['ciclo_nombre']?.toString(),
      programaNombre: json['programa_nombre']?.toString(),
      creditos: _toInt(json['creditos']),
      horas: _toInt(json['horas']),
      parcial1: parcial('parcial_1'),
      parcial2: parcial('parcial_2'),
      promedio: parcial('promedio'),
      observaciones: json['observaciones']?.toString(),
    );
  }

  final int cursoId;
  final String cursoNombre;
  final String? cicloNombre;
  final String? programaNombre;
  final int? creditos;
  final int? horas;
  final NotaParcial? parcial1;
  final NotaParcial? parcial2;
  final NotaParcial? promedio;
  final String? observaciones;

  /// Obtiene la nota numérica disponible más reciente para el curso.
  /// 1) Promedio final (si ya se publicó o registró).
  /// 2) Parcial 2 (si ya se registró y Parcial 1 no existe, o promedio si ambos existen).
  /// 3) Si existen Parcial 1 y Parcial 2 pero no promedio final: (P1 + P2) / 2.
  /// 4) Parcial 1.
  double? get notaVigenteNum {
    final nProm = double.tryParse(promedio?.calificacionSistema ?? '');
    if (nProm != null) return nProm;

    final nP1 = double.tryParse(parcial1?.calificacionSistema ?? '');
    final nP2 = double.tryParse(parcial2?.calificacionSistema ?? '');

    if (nP1 != null && nP2 != null) {
      return (nP1 + nP2) / 2.0;
    }
    if (nP2 != null) return nP2;
    if (nP1 != null) return nP1;
    return null;
  }

  /// Texto formateado de la nota vigente actual
  String? get notaVigenteTexto {
    final nProm = promedio?.calificacionSistema;
    if (nProm != null && nProm.trim().isNotEmpty) return nProm;

    final nP1 = double.tryParse(parcial1?.calificacionSistema ?? '');
    final nP2 = double.tryParse(parcial2?.calificacionSistema ?? '');

    if (nP1 != null && nP2 != null) {
      final prom = (nP1 + nP2) / 2.0;
      return prom % 1 == 0 ? prom.toInt().toString() : prom.toStringAsFixed(1);
    }
    if (parcial2?.calificacionSistema != null) return parcial2!.calificacionSistema;
    if (parcial1?.calificacionSistema != null) return parcial1!.calificacionSistema;
    return null;
  }

  /// Etiqueta explicativa de la evaluación a la que corresponde la nota
  String get etiquetaNotaVigente {
    if (promedio?.calificacionSistema != null && promedio!.calificacionSistema!.trim().isNotEmpty) {
      return 'Final';
    }
    if (parcial2?.calificacionSistema != null && parcial1?.calificacionSistema != null) {
      return 'Parciales';
    }
    if (parcial2?.calificacionSistema != null) return 'Parcial 2';
    if (parcial1?.calificacionSistema != null) return 'Parcial 1';
    return 'En curso';
  }

  /// ¿Aprobado según nota mínima reglamentaria (11)?
  bool get esAprobado {
    final n = notaVigenteNum;
    return n != null && n >= 11.0;
  }

  /// ¿Desaprobado (< 11)?
  bool get esDesaprobado {
    final n = notaVigenteNum;
    return n != null && n < 11.0;
  }

  /// ¿Tiene alguna nota registrada?
  bool get tieneNota => notaVigenteNum != null;
}

class PeriodoActualCalificaciones {
  const PeriodoActualCalificaciones({
    this.periodoNombre,
    required this.cursos,
    this.p1Visible = true,
    this.p2Visible = true,
    this.fechaP1Visible,
    this.fechaP2Visible,
  });

  factory PeriodoActualCalificaciones.fromJson(Map<String, dynamic> json) {
    final cursosRaw = json['cursos'];
    final cursosList = cursosRaw is List
        ? cursosRaw
            .whereType<Map<String, dynamic>>()
            .map((j) => CursoConNotas.fromJson(j))
            .toList()
        : <CursoConNotas>[];

    return PeriodoActualCalificaciones(
      periodoNombre: json['periodo_nombre']?.toString(),
      p1Visible: json['p1_visible'] as bool? ?? true,
      p2Visible: json['p2_visible'] as bool? ?? true,
      fechaP1Visible: json['fecha_p1_visible']?.toString(),
      fechaP2Visible: json['fecha_p2_visible']?.toString(),
      cursos: cursosList,
    );
  }

  final String? periodoNombre;
  final bool p1Visible;
  final bool p2Visible;
  final String? fechaP1Visible;
  final String? fechaP2Visible;
  final List<CursoConNotas> cursos;
}

/// Espejo de `CalificacionResource` para "Períodos anteriores" — modelo
/// `Periodo` (tabla `periodos`), no `Calificacion` (`calificacions`, vacía en
/// producción).
class Calificacion {
  const Calificacion({
    required this.id,
    required this.cursoId,
    this.cursoNombre,
    this.cicloNombre,
    this.programaNombre,
    this.creditos,
    this.horas,
    this.valoracionCurso,
    this.calificacionCurso,
    this.calificacionSistema,
  });

  factory Calificacion.fromJson(Map<String, dynamic> json) {
    return Calificacion(
      id: _toInt(json['id']) ?? 0,
      cursoId: _toInt(json['curso_id']) ?? 0,
      cursoNombre: json['curso_nombre']?.toString(),
      cicloNombre: json['ciclo_nombre']?.toString(),
      programaNombre: json['programa_nombre']?.toString(),
      creditos: _toInt(json['creditos']),
      horas: _toInt(json['horas']),
      valoracionCurso: json['valoracion_curso']?.toString(),
      calificacionCurso: json['calificacion_curso']?.toString(),
      calificacionSistema: json['calificacion_sistema']?.toString(),
    );
  }

  final int id;
  final int cursoId;
  final String? cursoNombre;
  final String? cicloNombre;
  final String? programaNombre;
  final int? creditos;
  final int? horas;
  final String? valoracionCurso;
  final String? calificacionCurso;
  final String? calificacionSistema;
}

class CalificacionesPorPeriodo {
  const CalificacionesPorPeriodo({required this.periodoNombre, required this.calificaciones});

  final String periodoNombre;
  final List<Calificacion> calificaciones;
}

/// `GET /api/v1/calificaciones` completo: período actual + históricos.
class CalificacionesResponse {
  const CalificacionesResponse({required this.periodoActual, required this.anteriores});

  factory CalificacionesResponse.fromJson(Map<String, dynamic> json) {
    final periodoActualRaw = json['periodo_actual'];
    final periodoActualMap = periodoActualRaw is Map<String, dynamic>
        ? periodoActualRaw
        : const <String, dynamic>{'cursos': []};

    final anterioresRaw = json['anteriores'];
    final List<CalificacionesPorPeriodo> anterioresList = [];

    if (anterioresRaw is Map) {
      for (final entry in anterioresRaw.entries) {
        final items = entry.value;
        if (items is List) {
          anterioresList.add(
            CalificacionesPorPeriodo(
              periodoNombre: entry.key.toString(),
              calificaciones: items
                  .whereType<Map<String, dynamic>>()
                  .map((j) => Calificacion.fromJson(j))
                  .toList(),
            ),
          );
        }
      }
    }

    return CalificacionesResponse(
      periodoActual: PeriodoActualCalificaciones.fromJson(periodoActualMap),
      anteriores: anterioresList,
    );
  }

  final PeriodoActualCalificaciones periodoActual;

  /// Orden ascendente (más antiguo primero), tal como lo entrega el backend
  /// (`sortKeys()`, igual que la web). La UI decide si mostrarlo invertido.
  final List<CalificacionesPorPeriodo> anteriores;
}
