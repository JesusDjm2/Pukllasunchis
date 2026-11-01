/// Espejo de `AlumnoResource` (`GET /api/v1/alumno/perfil`) — incluye tanto los
/// campos de la "ficha técnica" (solo lectura) como los del formulario de
/// matrícula (socioeconómicos, vivienda, salud), ya que ambas pantallas usan
/// el mismo recurso del backend.
///
/// Varias columnas de `alumnos` son `varchar`/`tinyint` heredados sin cast
/// Eloquent (p.ej. `dormitorios_vivienda` es varchar, `p_m_soltero` es tinyint
/// sin cast boolean) — de ahí los parseos defensivos de [_toInt]/[_toBool].
class AlumnoPerfil {
  const AlumnoPerfil({
    required this.id,
    required this.nombres,
    required this.apellidos,
    required this.dni,
    required this.email,
    this.foto,
    required this.numero,
    required this.numeroReferencia,
    required this.departamento,
    required this.provincia,
    required this.distrito,
    required this.direccion,
    required this.programaId,
    required this.programaNombre,
    required this.cicloId,
    required this.cicloNombre,
    required this.genero,
    required this.fechaNacimiento,
    required this.permanenciaVivienda,
    required this.estadoCivil,
    required this.pMSoltero,
    required this.numHijos,
    required this.sectorSocioeconomico,
    required this.trabajas,
    required this.dondeTrabajas,
    required this.ingresoMensual,
    required this.egreso,
    required this.hrsLaboradasSem,
    required this.sectorLaboral,
    required this.ayudaEconomica,
    required this.tiempoAyuda,
    required this.tipoApoyoFormacion,
    required this.convivientes,
    required this.quienMantiene,
    required this.cantDependientesChild,
    required this.cantDependientesOld,
    required this.cantDependientesOtros,
    required this.tipoVivienda,
    required this.situacionVivienda,
    required this.dormitoriosVivienda,
    required this.banosVivienda,
    required this.materialVivienda,
    required this.bienesVivienda,
    required this.hrsDisponiblesAgua,
    required this.hrsDisponiblesDesague,
    required this.hrsDisponiblesLuz,
    required this.otrosServicios,
    required this.tipoSeguro,
    required this.estudioBeca,
    this.tutor,
    this.anioIngreso,
  });

  factory AlumnoPerfil.fromJson(Map<String, dynamic> json) {
    final tutorJson = json['tutor'] as Map<String, dynamic>?;

    return AlumnoPerfil(
      id: json['id'] as int,
      nombres: json['nombres'] as String,
      apellidos: json['apellidos'] as String,
      dni: json['dni'] as String,
      email: json['email'] as String,
      foto: json['foto'] as String?,
      tutor: tutorJson != null ? TutorInfo.fromJson(tutorJson) : null,
      anioIngreso: json['anio_ingreso']?.toString(),
      numero: json['numero'] as String,
      numeroReferencia: json['numero_referencia'] as String,
      departamento: json['departamento'] as String?,
      provincia: json['provincia'] as String?,
      distrito: json['distrito'] as String?,
      direccion: json['direccion'] as String,
      programaId: json['programa_id'] as int,
      programaNombre: json['programa_nombre'] as String?,
      cicloId: json['ciclo_id'] as int,
      cicloNombre: json['ciclo_nombre'] as String?,
      genero: json['genero'] as String?,
      fechaNacimiento: (json['fecha_nacimiento'] as String?)?.trim().isEmpty ?? true
          ? null
          : json['fecha_nacimiento'] as String,
      permanenciaVivienda: json['permanencia_vivienda'] as String?,
      estadoCivil: json['estado_civil'] as String?,
      pMSoltero: _toBool(json['p_m_soltero']),
      numHijos: _toInt(json['num_hijos']),
      sectorSocioeconomico: json['sector_socioeconomico'] as String?,
      trabajas: json['trabajas'] as String?,
      dondeTrabajas: json['donde_trabajas'] as String?,
      ingresoMensual: json['ingreso_mensual'] as String?,
      egreso: json['egreso'] as String?,
      hrsLaboradasSem: _toInt(json['hrs_laboradas_sem']),
      sectorLaboral: json['sector_laboral'] as String?,
      ayudaEconomica: _toBool(json['ayuda_economica']),
      tiempoAyuda: json['tiempo_ayuda'] as String?,
      tipoApoyoFormacion: json['tipo_apoyo_formacion'] as String?,
      convivientes: json['convivientes'] as String?,
      quienMantiene: json['quien_mantiene'] as String?,
      cantDependientesChild: json['cant_dependientes_child']?.toString(),
      cantDependientesOld: json['cant_dependientes_old']?.toString(),
      cantDependientesOtros: json['cant_dependientes_otros']?.toString(),
      tipoVivienda: json['tipo_vivienda'] as String?,
      situacionVivienda: json['situacion_vivienda'] as String?,
      dormitoriosVivienda: _toInt(json['dormitorios_vivienda']),
      banosVivienda: _toInt(json['banos_vivienda']),
      materialVivienda: json['material_vivienda'] as String?,
      bienesVivienda: List<String>.from(json['bienes_vivienda'] as List? ?? const []),
      hrsDisponiblesAgua: _toInt(json['hrs_disponibles_agua']),
      hrsDisponiblesDesague: _toInt(json['hrs_disponibles_desague']),
      hrsDisponiblesLuz: _toInt(json['hrs_disponibles_luz']),
      otrosServicios: List<String>.from(json['otros_servicios'] as List? ?? const []),
      tipoSeguro: json['tipo_seguro'] as String?,
      estudioBeca: _toBool(json['estudio_beca']),
    );
  }

  final int id;
  final String nombres;
  final String apellidos;
  final String dni;
  final String email;
  final String? foto;
  final String numero;
  final String numeroReferencia;
  final String? departamento;
  final String? provincia;
  final String? distrito;
  final String direccion;
  final int programaId;
  final String? programaNombre;
  final int cicloId;
  final String? cicloNombre;
  final String? genero;
  final String? fechaNacimiento;
  final String? permanenciaVivienda;
  final String? estadoCivil;
  final bool? pMSoltero;
  final int? numHijos;
  final String? sectorSocioeconomico;
  final String? trabajas;
  final String? dondeTrabajas;
  final String? ingresoMensual;
  final String? egreso;
  final int? hrsLaboradasSem;
  final String? sectorLaboral;
  final bool? ayudaEconomica;
  final String? tiempoAyuda;
  final String? tipoApoyoFormacion;
  final String? convivientes;
  final String? quienMantiene;
  final String? cantDependientesChild;
  final String? cantDependientesOld;
  final String? cantDependientesOtros;
  final String? tipoVivienda;
  final String? situacionVivienda;
  final int? dormitoriosVivienda;
  final int? banosVivienda;
  final String? materialVivienda;
  final List<String> bienesVivienda;
  final int? hrsDisponiblesAgua;
  final int? hrsDisponiblesDesague;
  final int? hrsDisponiblesLuz;
  final List<String> otrosServicios;
  final String? tipoSeguro;
  final bool? estudioBeca;
  final TutorInfo? tutor;
  final String? anioIngreso;

  bool get tieneFechaNacimiento => fechaNacimiento != null;

  String get nombreCompleto => '$nombres $apellidos';

  String get domicilio {
    final ubicacion = [
      departamento,
      provincia,
      distrito,
    ].where((s) => s != null && s.isNotEmpty).join(' — ');
    return ubicacion.isEmpty ? direccion : '$ubicacion, $direccion';
  }
}

bool? _toBool(dynamic v) {
  if (v == null) return null;
  if (v is bool) return v;
  if (v is num) return v != 0;
  if (v is String) {
    final s = v.trim().toLowerCase();
    if (s == '1' || s == 'true' || s.startsWith('s')) return true;
    if (s == '0' || s == 'false' || s == 'no') return false;
  }
  return null;
}

int? _toInt(dynamic v) {
  if (v == null) return null;
  if (v is int) return v;
  if (v is num) return v.toInt();
  if (v is String) return int.tryParse(v.trim());
  return null;
}

class TutorInfo {
  const TutorInfo({
    required this.id,
    required this.nombre,
    this.email,
    this.telefono,
    this.foto,
  });

  factory TutorInfo.fromJson(Map<String, dynamic> json) {
    return TutorInfo(
      id: json['id'] as int? ?? 0,
      nombre: json['nombre'] as String? ?? 'Tutor(a) asignado',
      email: json['email'] as String?,
      telefono: json['telefono'] as String?,
      foto: json['foto'] as String?,
    );
  }

  final int id;
  final String nombre;
  final String? email;
  final String? telefono;
  final String? foto;
}
