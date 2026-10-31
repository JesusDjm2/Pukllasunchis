/// Espejo de `IncidenciaResource` (`GET /api/v1/incidencias`, docente propio).
class DocenteIncidencia {
  const DocenteIncidencia({
    required this.id,
    required this.fecha,
    required this.reporte,
    required this.estado,
    required this.alumnoNombre,
    required this.cicloNombre,
    required this.imagenUrl,
  });

  factory DocenteIncidencia.fromJson(Map<String, dynamic> json) {
    final alumno = json['alumno'] as Map<String, dynamic>?;
    final ciclo = json['ciclo'] as Map<String, dynamic>?;

    return DocenteIncidencia(
      id: json['id'] as int,
      fecha: DateTime.parse(json['fecha'] as String),
      reporte: json['reporte'] as String,
      estado: json['estado'] as String? ?? 'pendiente',
      alumnoNombre: alumno == null
          ? null
          : '${alumno['nombres'] ?? ''} ${alumno['apellidos'] ?? ''}'.trim(),
      cicloNombre: ciclo?['nombre'] as String?,
      imagenUrl: json['imagen_url'] as String?,
    );
  }

  final int id;
  final DateTime fecha;
  final String reporte;
  final String estado;
  final String? alumnoNombre;
  final String? cicloNombre;
  final String? imagenUrl;

  bool get estaPendiente => estado == 'pendiente';
}

/// Envoltura `{"data": [...], "meta": {...}}` del listado paginado.
class DocenteIncidenciasPage {
  const DocenteIncidenciasPage({
    required this.items,
    required this.currentPage,
    required this.lastPage,
    required this.total,
  });

  factory DocenteIncidenciasPage.fromJson(Map<String, dynamic> json) {
    final meta = json['meta'] as Map<String, dynamic>;

    return DocenteIncidenciasPage(
      items: (json['data'] as List)
          .map((item) => DocenteIncidencia.fromJson(item as Map<String, dynamic>))
          .toList(),
      currentPage: meta['current_page'] as int,
      lastPage: meta['last_page'] as int,
      total: meta['total'] as int,
    );
  }

  final List<DocenteIncidencia> items;
  final int currentPage;
  final int lastPage;
  final int total;

  bool get hayPaginaAnterior => currentPage > 1;
  bool get hayPaginaSiguiente => currentPage < lastPage;
}
