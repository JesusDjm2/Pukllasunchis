class TaqeProyecto {
  const TaqeProyecto({
    required this.id,
    required this.nombre,
    this.producto,
    this.descripcion,
    this.ciclo,
  });

  factory TaqeProyecto.fromJson(Map<String, dynamic> json) {
    return TaqeProyecto(
      id: json['id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? '',
      producto: json['producto'] as String?,
      descripcion: json['descripcion'] as String?,
      ciclo: json['ciclo'] as String?,
    );
  }

  final int id;
  final String nombre;
  final String? producto;
  final String? descripcion;
  final String? ciclo;
}

class TaqeCursoOpcion {
  const TaqeCursoOpcion({
    required this.id,
    required this.nombre,
    this.ciclo,
  });

  factory TaqeCursoOpcion.fromJson(Map<String, dynamic> json) {
    return TaqeCursoOpcion(
      id: json['id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? '',
      ciclo: json['ciclo'] as String?,
    );
  }

  final int id;
  final String nombre;
  final String? ciclo;
}

class TaqeCompanero {
  const TaqeCompanero({
    required this.id,
    required this.nombre,
  });

  factory TaqeCompanero.fromJson(Map<String, dynamic> json) {
    return TaqeCompanero(
      id: json['id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? '',
    );
  }

  final int id;
  final String nombre;
}

class TaqeIntegrante {
  const TaqeIntegrante({
    required this.id,
    required this.nombre,
    required this.esOrganizador,
    required this.rol,
    required this.estadoInvitacion,
  });

  factory TaqeIntegrante.fromJson(Map<String, dynamic> json) {
    return TaqeIntegrante(
      id: json['id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? '',
      esOrganizador: json['es_organizador'] as bool? ?? false,
      rol: json['rol'] as String? ?? 'Integrante',
      estadoInvitacion: json['estado_invitacion'] as String? ?? 'aceptado',
    );
  }

  final int id;
  final String nombre;
  final bool esOrganizador;
  final String rol;
  final String estadoInvitacion;
}

class TaqeArchivo {
  const TaqeArchivo({
    required this.id,
    required this.nombre,
    this.tipo,
    required this.tipoIcono,
    this.url,
    required this.esEnlace,
    this.subidoPor,
    this.subidoPorId,
    this.fecha,
    this.puedeEliminar = false,
    this.puedeMover = false,
  });

  factory TaqeArchivo.fromJson(Map<String, dynamic> json) {
    return TaqeArchivo(
      id: json['id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? '',
      tipo: json['tipo'] as String?,
      tipoIcono: json['tipo_icono'] as String? ?? 'otro',
      url: json['url'] as String?,
      esEnlace: json['es_enlace'] as bool? ?? false,
      subidoPor: json['subido_por'] as String?,
      subidoPorId: json['subido_por_id'] as int?,
      fecha: json['fecha'] as String?,
      puedeEliminar: json['puede_eliminar'] as bool? ?? false,
      puedeMover: json['puede_mover'] as bool? ?? false,
    );
  }

  final int id;
  final String nombre;
  final String? tipo;
  final String tipoIcono;
  final String? url;
  final bool esEnlace;
  final String? subidoPor;
  final int? subidoPorId;
  final String? fecha;
  final bool puedeEliminar;
  final bool puedeMover;
}

class TaqeGrupo {
  const TaqeGrupo({
    required this.id,
    required this.nombre,
    this.cursoId,
    required this.cursoNombre,
    required this.estado,
    required this.esOrganizador,
    required this.entregado,
    this.entregadoAt,
    this.puedeEntregar = false,
    this.puedeReabrir = false,
    required this.integrantes,
    required this.evidencias,
    required this.finales,
  });

  factory TaqeGrupo.fromJson(Map<String, dynamic> json) {
    final integrantesList = json['integrantes'] as List? ?? [];
    final evidenciasList = json['evidencias'] as List? ?? [];
    final finalesList = json['finales'] as List? ?? [];

    return TaqeGrupo(
      id: json['id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? '',
      cursoId: json['curso_id'] as int?,
      cursoNombre: json['curso_nombre'] as String? ?? 'Curso',
      estado: json['estado'] as String? ?? 'en_proceso',
      esOrganizador: json['es_organizador'] as bool? ?? false,
      entregado: json['entregado'] as bool? ?? false,
      entregadoAt: json['entregado_at'] as String?,
      puedeEntregar: json['puede_entregar'] as bool? ?? false,
      puedeReabrir: json['puede_reabrir'] as bool? ?? false,
      integrantes: integrantesList
          .map((e) => TaqeIntegrante.fromJson(e as Map<String, dynamic>))
          .toList(),
      evidencias: evidenciasList
          .map((e) => TaqeArchivo.fromJson(e as Map<String, dynamic>))
          .toList(),
      finales: finalesList
          .map((e) => TaqeArchivo.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  final int id;
  final String nombre;
  final int? cursoId;
  final String cursoNombre;
  final String estado;
  final bool esOrganizador;
  final bool entregado;
  final String? entregadoAt;
  final bool puedeEntregar;
  final bool puedeReabrir;
  final List<TaqeIntegrante> integrantes;
  final List<TaqeArchivo> evidencias;
  final List<TaqeArchivo> finales;
}

class TaqeInvitacion {
  const TaqeInvitacion({
    required this.id,
    required this.grupoNombre,
    required this.cursoNombre,
    required this.creadorNombre,
    this.fecha,
  });

  factory TaqeInvitacion.fromJson(Map<String, dynamic> json) {
    return TaqeInvitacion(
      id: json['id'] as int? ?? 0,
      grupoNombre: json['grupo_nombre'] as String? ?? '',
      cursoNombre: json['curso_nombre'] as String? ?? '',
      creadorNombre: json['creador_nombre'] as String? ?? '',
      fecha: json['fecha'] as String?,
    );
  }

  final int id;
  final String grupoNombre;
  final String cursoNombre;
  final String creadorNombre;
  final String? fecha;
}

class TaqeInvestigador {
  const TaqeInvestigador({
    required this.id,
    required this.nombre,
    this.email,
    this.telefono,
  });

  factory TaqeInvestigador.fromJson(Map<String, dynamic> json) {
    return TaqeInvestigador(
      id: json['id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? '',
      email: json['email'] as String?,
      telefono: json['telefono'] as String?,
    );
  }

  final int id;
  final String nombre;
  final String? email;
  final String? telefono;
}

class TaqeData {
  const TaqeData({
    required this.periodoNombre,
    this.proyecto,
    this.cursosSinGrupo = const [],
    required this.misGrupos,
    required this.invitacionesPendientes,
    required this.investigadores,
    required this.gruposAnterioresCount,
  });

  factory TaqeData.fromJson(Map<String, dynamic> json) {
    final proyectoMap = json['proyecto'] as Map<String, dynamic>?;
    final cursosList = json['cursos_sin_grupo'] as List? ?? [];
    final gruposList = json['mis_grupos'] as List? ?? [];
    final invitacionesList = json['invitaciones_pendientes'] as List? ?? [];
    final investigadoresList = json['investigadores'] as List? ?? [];

    return TaqeData(
      periodoNombre: json['periodo_nombre'] as String? ?? '',
      proyecto: proyectoMap != null ? TaqeProyecto.fromJson(proyectoMap) : null,
      cursosSinGrupo: cursosList
          .map((e) => TaqeCursoOpcion.fromJson(e as Map<String, dynamic>))
          .toList(),
      misGrupos: gruposList
          .map((e) => TaqeGrupo.fromJson(e as Map<String, dynamic>))
          .toList(),
      invitacionesPendientes: invitacionesList
          .map((e) => TaqeInvitacion.fromJson(e as Map<String, dynamic>))
          .toList(),
      investigadores: investigadoresList
          .map((e) => TaqeInvestigador.fromJson(e as Map<String, dynamic>))
          .toList(),
      gruposAnterioresCount: json['grupos_anteriores_count'] as int? ?? 0,
    );
  }

  final String periodoNombre;
  final TaqeProyecto? proyecto;
  final List<TaqeCursoOpcion> cursosSinGrupo;
  final List<TaqeGrupo> misGrupos;
  final List<TaqeInvitacion> invitacionesPendientes;
  final List<TaqeInvestigador> investigadores;
  final int gruposAnterioresCount;
}
