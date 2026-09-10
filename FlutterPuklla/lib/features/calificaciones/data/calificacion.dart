/// `{valoracion_curso, calificacion_curso, calificacion_sistema}` de un
/// parcial (Parcial 1 / Parcial 2 / Promedio) — `null` si el alumno aún no
/// tiene esa nota registrada.
class NotaParcial {
  const NotaParcial({
    required this.valoracionCurso,
    required this.calificacionCurso,
    required this.calificacionSistema,
  });

  factory NotaParcial.fromJson(Map<String, dynamic> json) {
    return NotaParcial(
      valoracionCurso: json['valoracion_curso'] as String?,
      calificacionCurso: json['calificacion_curso'] as String?,
      calificacionSistema: json['calificacion_sistema'] as String?,
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
    required this.parcial1,
    required this.parcial2,
    required this.promedio,
    required this.observaciones,
  });

  factory CursoConNotas.fromJson(Map<String, dynamic> json) {
    NotaParcial? parcial(String key) {
      final value = json[key];
      return value == null ? null : NotaParcial.fromJson(value as Map<String, dynamic>);
    }

    return CursoConNotas(
      cursoId: json['curso_id'] as int,
      cursoNombre: json['curso_nombre'] as String,
      parcial1: parcial('parcial_1'),
      parcial2: parcial('parcial_2'),
      promedio: parcial('promedio'),
      observaciones: json['observaciones'] as String?,
    );
  }

  final int cursoId;
  final String cursoNombre;
  final NotaParcial? parcial1;
  final NotaParcial? parcial2;
  final NotaParcial? promedio;
  final String? observaciones;
}

class PeriodoActualCalificaciones {
  const PeriodoActualCalificaciones({required this.periodoNombre, required this.cursos});

  factory PeriodoActualCalificaciones.fromJson(Map<String, dynamic> json) {
    return PeriodoActualCalificaciones(
      periodoNombre: json['periodo_nombre'] as String?,
      cursos: (json['cursos'] as List)
          .map((j) => CursoConNotas.fromJson(j as Map<String, dynamic>))
          .toList(),
    );
  }

  final String? periodoNombre;
  final List<CursoConNotas> cursos;
}

/// Espejo de `CalificacionResource` para "Períodos anteriores" — modelo
/// `Periodo` (tabla `periodos`), no `Calificacion` (`calificacions`, vacía en
/// producción).
class Calificacion {
  const Calificacion({
    required this.id,
    required this.cursoId,
    required this.cursoNombre,
    required this.valoracionCurso,
    required this.calificacionCurso,
    required this.calificacionSistema,
  });

  factory Calificacion.fromJson(Map<String, dynamic> json) {
    return Calificacion(
      id: json['id'] as int,
      cursoId: json['curso_id'] as int,
      cursoNombre: json['curso_nombre'] as String?,
      valoracionCurso: json['valoracion_curso'] as String?,
      calificacionCurso: json['calificacion_curso'] as String?,
      calificacionSistema: json['calificacion_sistema'] as String?,
    );
  }

  final int id;
  final int cursoId;
  final String? cursoNombre;
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
    final anterioresJson = json['anteriores'] as Map<String, dynamic>;
    return CalificacionesResponse(
      periodoActual: PeriodoActualCalificaciones.fromJson(json['periodo_actual'] as Map<String, dynamic>),
      anteriores: anterioresJson.entries
          .map(
            (entry) => CalificacionesPorPeriodo(
              periodoNombre: entry.key,
              calificaciones: (entry.value as List)
                  .map((j) => Calificacion.fromJson(j as Map<String, dynamic>))
                  .toList(),
            ),
          )
          .toList(),
    );
  }

  final PeriodoActualCalificaciones periodoActual;

  /// Orden ascendente (más antiguo primero), tal como lo entrega el backend
  /// (`sortKeys()`, igual que la web). La UI decide si mostrarlo invertido.
  final List<CalificacionesPorPeriodo> anteriores;
}
