/// Árbol completo de `GET /api/v1/cursos-especiales/{curso}`. La API no
/// expone contenido de lección (`contenido_texto`/`archivo_url`) ni
/// `respuesta_correcta` de ejercicio — solo lo necesario para navegar y
/// marcar progreso.
class CursoEspecialDetalle {
  const CursoEspecialDetalle({
    required this.id,
    required this.nombre,
    required this.descripcion,
    required this.imagenUrl,
    required this.porcentaje,
    required this.niveles,
  });

  factory CursoEspecialDetalle.fromJson(Map<String, dynamic> json) {
    return CursoEspecialDetalle(
      id: json['id'] as int,
      nombre: json['nombre'] as String,
      descripcion: json['descripcion'] as String?,
      imagenUrl: json['imagen_url'] as String?,
      porcentaje: json['porcentaje'] as int,
      niveles: (json['niveles'] as List)
          .map((n) => Nivel.fromJson(n as Map<String, dynamic>))
          .toList(),
    );
  }

  final int id;
  final String nombre;
  final String? descripcion;
  final String? imagenUrl;
  final int porcentaje;
  final List<Nivel> niveles;
}

class Nivel {
  const Nivel({
    required this.id,
    required this.nombre,
    required this.orden,
    required this.unidades,
  });

  factory Nivel.fromJson(Map<String, dynamic> json) {
    return Nivel(
      id: json['id'] as int,
      nombre: json['nombre'] as String,
      orden: json['orden'] as int,
      unidades: (json['unidades'] as List)
          .map((u) => Unidad.fromJson(u as Map<String, dynamic>))
          .toList(),
    );
  }

  final int id;
  final String nombre;
  final int orden;
  final List<Unidad> unidades;
}

class Unidad {
  const Unidad({
    required this.id,
    required this.nombre,
    required this.descripcion,
    required this.orden,
    required this.lecciones,
    required this.ejercicios,
  });

  factory Unidad.fromJson(Map<String, dynamic> json) {
    return Unidad(
      id: json['id'] as int,
      nombre: json['nombre'] as String,
      descripcion: json['descripcion'] as String?,
      orden: json['orden'] as int,
      lecciones: (json['lecciones'] as List)
          .map((l) => Leccion.fromJson(l as Map<String, dynamic>))
          .toList(),
      ejercicios: (json['ejercicios'] as List)
          .map((e) => Ejercicio.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  final int id;
  final String nombre;
  final String? descripcion;
  final int orden;
  final List<Leccion> lecciones;
  final List<Ejercicio> ejercicios;
}

class Leccion {
  const Leccion({
    required this.id,
    required this.nombre,
    required this.tipo,
    required this.duracionMin,
    required this.orden,
    required this.completada,
  });

  factory Leccion.fromJson(Map<String, dynamic> json) {
    return Leccion(
      id: json['id'] as int,
      nombre: json['nombre'] as String,
      tipo: json['tipo'] as String,
      duracionMin: json['duracion_min'] as int?,
      orden: json['orden'] as int,
      completada: json['completada'] as bool,
    );
  }

  final int id;
  final String nombre;
  final String tipo;
  final int? duracionMin;
  final int orden;
  final bool completada;
}

class Ejercicio {
  const Ejercicio({
    required this.id,
    required this.tipo,
    required this.pregunta,
    required this.opciones,
    required this.puntajeMax,
    required this.orden,
    required this.completado,
  });

  factory Ejercicio.fromJson(Map<String, dynamic> json) {
    return Ejercicio(
      id: json['id'] as int,
      tipo: json['tipo'] as String,
      pregunta: json['pregunta'] as String,
      opciones: json['opciones'] == null ? null : List<String>.from(json['opciones'] as List),
      puntajeMax: json['puntaje_max'] as int,
      orden: json['orden'] as int,
      completado: json['completado'] as bool,
    );
  }

  final int id;
  final String tipo;
  final String pregunta;

  /// `null` si el ejercicio es de respuesta libre (no opción múltiple).
  final List<String>? opciones;
  final int puntajeMax;
  final int orden;
  final bool completado;
}
