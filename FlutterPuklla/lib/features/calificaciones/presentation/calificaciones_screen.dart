import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_colors.dart';
import '../../../routing/app_drawer.dart';
import '../application/calificaciones_providers.dart';
import '../data/calificacion.dart';
import '../data/matricula.dart';

/// Solo lectura: notas por curso agrupadas por período (igual que
/// `AlumnoController@calificaciones` en la web) y el historial de matrícula.
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
        drawer: const AppDrawer(),
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
        data: (data) {
          // Más recientes primero — la web las agrupa ascendente (sortKeys),
          // el orden de lectura en el móvil se decide aquí.
          final anterioresRecientesPrimero = data.anteriores.reversed.toList();

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _PeriodoActualSection(periodoActual: data.periodoActual),
              const SizedBox(height: 24),
              if (anterioresRecientesPrimero.isNotEmpty) ...[
                Text('Períodos anteriores', style: Theme.of(context).textTheme.titleMedium),
                const SizedBox(height: 8),
                for (final grupo in anterioresRecientesPrimero) _PeriodoAnteriorSection(grupo: grupo),
              ],
            ],
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => _ErrorMessage(error: error, fallback: 'No se pudieron cargar las calificaciones.'),
      ),
    );
  }
}

class _PeriodoActualSection extends StatelessWidget {
  const _PeriodoActualSection({required this.periodoActual});

  final PeriodoActualCalificaciones periodoActual;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Text('Período actual', style: Theme.of(context).textTheme.titleMedium),
            if (periodoActual.periodoNombre != null) ...[
              const SizedBox(width: 8),
              Chip(label: Text(periodoActual.periodoNombre!)),
            ],
          ],
        ),
        const SizedBox(height: 8),
        if (periodoActual.cursos.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 8),
            child: Text('No hay cursos asignados para este período.'),
          )
        else
          for (final curso in periodoActual.cursos) _CursoConNotasCard(curso: curso),
      ],
    );
  }
}

class _CursoConNotasCard extends StatelessWidget {
  const _CursoConNotasCard({required this.curso});

  final CursoConNotas curso;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(curso.cursoNombre, style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            _ParcialRow(label: 'Parcial 1', nota: curso.parcial1),
            _ParcialRow(label: 'Parcial 2', nota: curso.parcial2),
            _ParcialRow(label: 'Promedio', nota: curso.promedio),
            if (curso.observaciones != null && curso.observaciones!.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text('Obs.: ${curso.observaciones}', style: Theme.of(context).textTheme.bodySmall),
            ],
          ],
        ),
      ),
    );
  }
}

class _ParcialRow extends StatelessWidget {
  const _ParcialRow({required this.label, required this.nota});

  final String label;
  final NotaParcial? nota;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          SizedBox(width: 90, child: Text(label, style: const TextStyle(color: AppColors.muted))),
          Expanded(
            child: nota == null
                ? const Text('Sin datos aún', style: TextStyle(color: AppColors.muted, fontStyle: FontStyle.italic))
                : Text(
                    [
                      if (nota!.valoracionCurso != null) nota!.valoracionCurso!,
                      if (nota!.calificacionCurso != null) nota!.calificacionCurso!,
                      if (nota!.calificacionSistema != null) 'Sistema: ${nota!.calificacionSistema}',
                    ].join(' · '),
                  ),
          ),
        ],
      ),
    );
  }
}

class _PeriodoAnteriorSection extends StatelessWidget {
  const _PeriodoAnteriorSection({required this.grupo});

  final CalificacionesPorPeriodo grupo;

  @override
  Widget build(BuildContext context) {
    return ExpansionTile(
      title: Text(grupo.periodoNombre, style: Theme.of(context).textTheme.titleMedium),
      children: [for (final calificacion in grupo.calificaciones) _CalificacionCard(calificacion: calificacion)],
    );
  }
}

class _CalificacionCard extends StatelessWidget {
  const _CalificacionCard({required this.calificacion});

  final Calificacion calificacion;

  @override
  Widget build(BuildContext context) {
    final filas = <MapEntry<String, String>>[
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
              const Text('Sin datos aún'),
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
