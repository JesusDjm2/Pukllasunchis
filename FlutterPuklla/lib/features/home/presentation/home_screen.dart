import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../../auth/application/auth_controller.dart';
import '../application/home_providers.dart';
import '../data/curso.dart';
import '../data/periodo_actual.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authControllerProvider).user;

    return Scaffold(
      appBar: AppBar(
        title: Text(user != null ? 'Hola, ${user.name}' : 'Pukllasunchis'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            tooltip: 'Cerrar sesión',
            onPressed: () => ref.read(authControllerProvider.notifier).logout(),
          ),
        ],
      ),
      body: user != null && user.isAlumno
          ? const _AlumnoDashboard()
          : const Center(
              child: Padding(
                padding: EdgeInsets.all(24),
                child: Text(
                  'Esta vista todavía solo está disponible para alumnos.',
                  textAlign: TextAlign.center,
                ),
              ),
            ),
    );
  }
}

class _AlumnoDashboard extends ConsumerWidget {
  const _AlumnoDashboard();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(periodoActualProvider);
        ref.invalidate(cursosProvider);
        await Future.wait([
          ref.read(periodoActualProvider.future),
          ref.read(cursosProvider.future),
        ]);
      },
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const _PeriodoActualCard(),
          const SizedBox(height: 24),
          Card(
            child: ListTile(
              leading: const Icon(Icons.school_outlined),
              title: const Text('Cursos especiales'),
              subtitle: const Text('Lecciones y ejercicios a tu ritmo'),
              trailing: const Icon(Icons.chevron_right),
              onTap: () => context.push('/cursos-especiales'),
            ),
          ),
          Card(
            child: ListTile(
              leading: const Icon(Icons.grade_outlined),
              title: const Text('Calificaciones y matrícula'),
              subtitle: const Text('Notas por curso e historial de matrícula'),
              trailing: const Icon(Icons.chevron_right),
              onTap: () => context.push('/calificaciones'),
            ),
          ),
          Card(
            child: ListTile(
              leading: const Icon(Icons.campaign_outlined),
              title: const Text('Comunicados'),
              subtitle: const Text('Avisos y publicaciones del instituto'),
              trailing: const Icon(Icons.chevron_right),
              onTap: () => context.push('/comunicados'),
            ),
          ),
          const SizedBox(height: 24),
          Text('Mis cursos', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          const _CursosList(),
        ],
      ),
    );
  }
}

class _PeriodoActualCard extends ConsumerWidget {
  const _PeriodoActualCard();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final periodoActual = ref.watch(periodoActualProvider);

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: periodoActual.when(
          data: (periodo) => _PeriodoActualContent(periodo: periodo),
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, _) => Text(_errorMessage(error, 'No se pudo cargar el periodo actual.')),
        ),
      ),
    );
  }
}

class _PeriodoActualContent extends StatelessWidget {
  const _PeriodoActualContent({required this.periodo});

  final PeriodoActual? periodo;

  @override
  Widget build(BuildContext context) {
    final periodo = this.periodo;
    if (periodo == null) {
      return const Text('No hay un periodo activo actualmente.');
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(periodo.nombre, style: Theme.of(context).textTheme.titleLarge),
        if (periodo.horario != null) ...[
          const SizedBox(height: 4),
          Text(periodo.horario!),
        ],
        if (periodo.fechaInicio != null && periodo.fechaCierre != null) ...[
          const SizedBox(height: 4),
          Text(
            '${_formatDate(periodo.fechaInicio!)} — ${_formatDate(periodo.fechaCierre!)}',
            style: Theme.of(context).textTheme.bodySmall,
          ),
        ],
      ],
    );
  }
}

class _CursosList extends ConsumerWidget {
  const _CursosList();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final cursos = ref.watch(cursosProvider);

    return cursos.when(
      data: (list) => list.isEmpty
          ? const Text('No tienes cursos en el periodo actual.')
          : Column(children: [for (final curso in list) _CursoTile(curso: curso)]),
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, _) => Text(_errorMessage(error, 'No se pudieron cargar los cursos.')),
    );
  }
}

class _CursoTile extends StatelessWidget {
  const _CursoTile({required this.curso});

  final Curso curso;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: ListTile(
        title: Text(curso.nombre),
        subtitle: Text('${curso.cc} · ${curso.horas} h · ${curso.creditos} créditos'),
      ),
    );
  }
}

String _errorMessage(Object error, String fallback) {
  return error is ApiException ? error.message : fallback;
}

String _formatDate(DateTime date) {
  return '${date.day.toString().padLeft(2, '0')}/'
      '${date.month.toString().padLeft(2, '0')}/'
      '${date.year}';
}
