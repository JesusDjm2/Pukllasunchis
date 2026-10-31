import 'alumno_perfil.dart';

/// Espejo de la respuesta de `PUT /api/v1/alumno/perfil`.
class ActualizarPerfilResultado {
  const ActualizarPerfilResultado({
    required this.perfil,
    required this.matriculaCompletada,
  });

  factory ActualizarPerfilResultado.fromJson(Map<String, dynamic> json) {
    return ActualizarPerfilResultado(
      perfil: AlumnoPerfil.fromJson(json['data'] as Map<String, dynamic>),
      matriculaCompletada: json['matricula_completada'] as bool? ?? false,
    );
  }

  final AlumnoPerfil perfil;
  final bool matriculaCompletada;
}
