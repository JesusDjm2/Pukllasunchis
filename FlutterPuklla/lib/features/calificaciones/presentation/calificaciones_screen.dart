import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../application/calificaciones_providers.dart';
import '../data/calificacion.dart';
import '../data/matricula.dart';

/// Solo lectura: notas por curso y el historial de matrícula del alumno.
class CalificacionesScreen extends StatelessWidget {
  const CalificacionesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Calificaciones y matrícula'),
          bottom: const TabBar(
            tabs: [Tab(text: 'Calificaciones'), Tab(text: 'Matrícula')],
          ),
        ),
        body: const TabBarView(
          children: [_CalificacionesTab(), _MatriculaTab()],
        ),
      ),
    );
  }
}

class _CalificacionesTab extends ConsumerWidget {
  const _CalificacionesTab();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final calificaciones = ref.watch(calificacionesProvider);

    return RefreshIndicator(
      onRefresh: () => ref.refresh(calificacionesProvider.future),
      child: calificaciones.when(
        data: (list) => list.isEmpty
            ? _EmptyMessage(text: 'Todavía no tienes calificaciones registradas.')
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: list.length,
                itemBuilder: (context, index) => _CalificacionCard(calificacion: list[index]),
              ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => _ErrorMessage(error: error, fallback: 'No se pudieron cargar las calificaciones.'),
      ),
    );
  }
}

class _CalificacionCard extends StatelessWidget {
  const _CalificacionCard({required this.calificacion});

  final Calificacion calificacion;

  @override
  Widget build(BuildContext context) {
    final filas = <MapEntry<String, String>>[
      if (calificacion.valoracion1 != null) MapEntry('Valoración 1', calificacion.valoracion1!),
      if (calificacion.valoracion2 != null) MapEntry('Valoración 2', calificacion.valoracion2!),
      if (calificacion.valoracion3 != null) MapEntry('Valoración 3', calificacion.valoracion3!),
      if (calificacion.valoracionCurso != null)
        MapEntry('Valoración del curso', calificacion.valoracionCurso!),
      if (calificacion.calificacionCurso != null)
        MapEntry('Calificación del curso', calificacion.calificacionCurso!),
      if (calificacion.calificacionSistema != null)
        MapEntry('Calificación del sistema', calificacion.calificacionSistema!),
    ];

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              calificacion.cursoNombre ?? 'Curso #${calificacion.cursoId}',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            if (filas.isEmpty) ...[
              const SizedBox(height: 8),
              const Text('Sin calificaciones registradas todavía.'),
            ] else ...[
              const SizedBox(height: 8),
              for (final fila in filas)
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 2),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [Text(fila.key), Text(fila.value)],
                  ),
                ),
            ],
          ],
        ),
      ),
    );
  }
}

class _MatriculaTab extends ConsumerWidget {
  const _MatriculaTab();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final matriculas = ref.watch(matriculasProvider);

    return RefreshIndicator(
      onRefresh: () => ref.refresh(matriculasProvider.future),
      child: matriculas.when(
        data: (list) => list.isEmpty
            ? _EmptyMessage(text: 'No tienes matrículas registradas.')
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: list.length,
                itemBuilder: (context, index) => _MatriculaCard(matricula: list[index]),
              ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => _ErrorMessage(error: error, fallback: 'No se pudieron cargar las matrículas.'),
      ),
    );
  }
}

class _MatriculaCard extends StatelessWidget {
  const _MatriculaCard({required this.matricula});

  final Matricula matricula;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        title: Text(matricula.periodoNombre ?? 'Periodo #${matricula.periodoActualId}'),
        subtitle: Text(
          matricula.fechaCompletado != null
              ? 'Completada el ${_formatDate(matricula.fechaCompletado!)}'
              : 'Registrada el ${_formatDate(matricula.createdAt)}',
        ),
        trailing: Chip(label: Text(matricula.estado)),
      ),
    );
  }
}

class _EmptyMessage extends StatelessWidget {
  const _EmptyMessage({required this.text});

  final String text;

  @override
  Widget build(BuildContext context) {
    return ListView(
      children: [
        Padding(padding: const EdgeInsets.all(24), child: Text(text, textAlign: TextAlign.center)),
      ],
    );
  }
}

class _ErrorMessage extends StatelessWidget {
  const _ErrorMessage({required this.error, required this.fallback});

  final Object error;
  final String fallback;

  @override
  Widget build(BuildContext context) {
    return Center(child: Text(error is ApiException ? (error as ApiException).message : fallback));
  }
}

String _formatDate(DateTime date) {
  return '${date.day.toString().padLeft(2, '0')}/'
      '${date.month.toString().padLeft(2, '0')}/'
      '${date.year}';
}
