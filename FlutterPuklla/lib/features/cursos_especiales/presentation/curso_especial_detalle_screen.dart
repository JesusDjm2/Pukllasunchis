import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../application/cursos_especiales_controller.dart';
import '../application/cursos_especiales_providers.dart';
import '../data/curso_especial_detalle.dart';

class CursoEspecialDetalleScreen extends ConsumerWidget {
  const CursoEspecialDetalleScreen({super.key, required this.cursoId});

  final int cursoId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detalle = ref.watch(cursoEspecialDetalleProvider(cursoId));

    return Scaffold(
      appBar: AppBar(title: Text(detalle.valueOrNull?.nombre ?? 'Curso especial')),
      body: detalle.when(
        data: (curso) => _DetalleBody(cursoId: cursoId, curso: curso),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => Center(
          child: Text(error is ApiException ? error.message : 'No se pudo cargar el curso.'),
        ),
      ),
    );
  }
}

class _DetalleBody extends StatelessWidget {
  const _DetalleBody({required this.cursoId, required this.curso});

  final int cursoId;
  final CursoEspecialDetalle curso;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        LinearProgressIndicator(value: curso.porcentaje / 100),
        const SizedBox(height: 4),
        Text('${curso.porcentaje}% completado'),
        const SizedBox(height: 16),
        for (final nivel in curso.niveles) _NivelSection(cursoId: cursoId, nivel: nivel),
      ],
    );
  }
}

class _NivelSection extends StatelessWidget {
  const _NivelSection({required this.cursoId, required this.nivel});

  final int cursoId;
  final Nivel nivel;

  @override
  Widget build(BuildContext context) {
    return ExpansionTile(
      title: Text(nivel.nombre),
      initiallyExpanded: true,
      children: [for (final unidad in nivel.unidades) _UnidadSection(cursoId: cursoId, unidad: unidad)],
    );
  }
}

class _UnidadSection extends StatelessWidget {
  const _UnidadSection({required this.cursoId, required this.unidad});

  final int cursoId;
  final Unidad unidad;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 12),
      child: ExpansionTile(
        title: Text(unidad.nombre),
        subtitle: unidad.descripcion != null ? Text(unidad.descripcion!) : null,
        children: [
          for (final leccion in unidad.lecciones) _LeccionTile(cursoId: cursoId, leccion: leccion),
          for (final ejercicio in unidad.ejercicios) _EjercicioTile(cursoId: cursoId, ejercicio: ejercicio),
        ],
      ),
    );
  }
}

class _LeccionTile extends ConsumerWidget {
  const _LeccionTile({required this.cursoId, required this.leccion});

  final int cursoId;
  final Leccion leccion;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return ListTile(
      leading: Icon(leccion.completada ? Icons.check_circle : Icons.play_circle_outline),
      title: Text(leccion.nombre),
      subtitle: leccion.duracionMin != null ? Text('${leccion.duracionMin} min') : null,
      trailing: leccion.completada
          ? null
          : TextButton(
              onPressed: () async {
                try {
                  await ref
                      .read(cursosEspecialesControllerProvider)
                      .completarLeccion(cursoId: cursoId, leccionId: leccion.id);
                } on ApiException catch (e) {
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
                  }
                }
              },
              child: const Text('Marcar completada'),
            ),
    );
  }
}

class _EjercicioTile extends ConsumerWidget {
  const _EjercicioTile({required this.cursoId, required this.ejercicio});

  final int cursoId;
  final Ejercicio ejercicio;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return ListTile(
      leading: Icon(ejercicio.completado ? Icons.check_circle : Icons.quiz_outlined),
      title: Text(ejercicio.pregunta),
      trailing: ejercicio.completado ? null : const Icon(Icons.chevron_right),
      onTap: ejercicio.completado
          ? null
          : () => _showResponderSheet(context, ref, cursoId: cursoId, ejercicio: ejercicio),
    );
  }
}

Future<void> _showResponderSheet(
  BuildContext context,
  WidgetRef ref, {
  required int cursoId,
  required Ejercicio ejercicio,
}) {
  return showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    builder: (context) => _ResponderEjercicioSheet(cursoId: cursoId, ejercicio: ejercicio),
  );
}

class _ResponderEjercicioSheet extends ConsumerStatefulWidget {
  const _ResponderEjercicioSheet({required this.cursoId, required this.ejercicio});

  final int cursoId;
  final Ejercicio ejercicio;

  @override
  ConsumerState<_ResponderEjercicioSheet> createState() => _ResponderEjercicioSheetState();
}

class _ResponderEjercicioSheetState extends ConsumerState<_ResponderEjercicioSheet> {
  final _respuestaController = TextEditingController();
  String? _opcionSeleccionada;
  bool _enviando = false;

  @override
  void dispose() {
    _respuestaController.dispose();
    super.dispose();
  }

  Future<void> _enviar() async {
    final respuesta = widget.ejercicio.opciones != null
        ? _opcionSeleccionada
        : _respuestaController.text.trim();
    if (respuesta == null || respuesta.isEmpty) return;

    setState(() => _enviando = true);
    try {
      final (correcta, puntaje) = await ref.read(cursosEspecialesControllerProvider).responderEjercicio(
            cursoId: widget.cursoId,
            ejercicioId: widget.ejercicio.id,
            respuesta: respuesta,
          );
      if (!mounted) return;
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(correcta ? '¡Correcto! +$puntaje pts' : 'Incorrecto.'),
        ),
      );
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      }
    } finally {
      if (mounted) setState(() => _enviando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final opciones = widget.ejercicio.opciones;

    return Padding(
      padding: EdgeInsets.only(
        left: 24,
        right: 24,
        top: 24,
        bottom: MediaQuery.of(context).viewInsets.bottom + 24,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(widget.ejercicio.pregunta, style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 16),
          if (opciones != null)
            RadioGroup<String>(
              groupValue: _opcionSeleccionada,
              onChanged: (value) => setState(() => _opcionSeleccionada = value),
              child: Column(
                children: [
                  for (final opcion in opciones)
                    RadioListTile<String>(title: Text(opcion), value: opcion),
                ],
              ),
            )
          else
            TextField(
              controller: _respuestaController,
              decoration: const InputDecoration(labelText: 'Tu respuesta'),
            ),
          const SizedBox(height: 16),
          FilledButton(
            onPressed: _enviando ? null : _enviar,
            child: _enviando
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Text('Responder'),
          ),
        ],
      ),
    );
  }
}
