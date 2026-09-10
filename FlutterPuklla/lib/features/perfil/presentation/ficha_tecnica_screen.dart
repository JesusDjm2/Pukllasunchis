import 'dart:io';

import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_colors.dart';
import '../../home/application/home_providers.dart';
import '../../home/data/curso.dart';
import '../application/perfil_providers.dart';
import '../data/alumno_perfil.dart';
import '../data/perfil_repository.dart';

/// Pantalla de aterrizaje del alumno tras iniciar sesión — replica
/// `alumnos/vistasAlumnos/index.blade.php`: datos personales, programa y
/// ciclo, estado de matrícula del período activo, y cursos del semestre.
class FichaTecnicaScreen extends ConsumerWidget {
  const FichaTecnicaScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final perfil = ref.watch(perfilProvider);

    return RefreshIndicator(
      onRefresh: () async {
        ref.invalidate(perfilProvider);
        ref.invalidate(periodoActualProvider);
        ref.invalidate(cursosProvider);
        ref.invalidate(yaMatriculadoProvider);
        await ref.read(perfilProvider.future);
      },
      child: perfil.when(
        data: (data) => _FichaBody(perfil: data),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => ListView(
          children: [
            Padding(
              padding: const EdgeInsets.all(24),
              child: Text(
                error is ApiException ? error.message : 'No se pudo cargar tu ficha técnica.',
                textAlign: TextAlign.center,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _FichaBody extends ConsumerWidget {
  const _FichaBody({required this.perfil});

  final AlumnoPerfil perfil;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        _EstadoMatriculaBanner(perfil: perfil),
        const SizedBox(height: 16),
        _DatosPersonalesCard(perfil: perfil),
        const SizedBox(height: 16),
        _ProgramaCicloCard(perfil: perfil),
        const SizedBox(height: 16),
        Text('Cursos del semestre', style: Theme.of(context).textTheme.titleMedium),
        const SizedBox(height: 8),
        const _CursosDelSemestre(),
      ],
    );
  }
}

class _EstadoMatriculaBanner extends ConsumerWidget {
  const _EstadoMatriculaBanner({required this.perfil});

  final AlumnoPerfil perfil;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final periodoActual = ref.watch(periodoActualProvider);
    final yaMatriculado = ref.watch(yaMatriculadoProvider);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        periodoActual.when(
          data: (periodo) {
            if (periodo == null) return const SizedBox.shrink();

            return yaMatriculado.when(
              data: (matriculado) => matriculado
                  ? _MatriculadoBadge(periodoNombre: periodo.nombre)
                  : (periodo.formularioHabilitado
                      ? _MatriculaPendienteAviso(periodoNombre: periodo.nombre)
                      : const SizedBox.shrink()),
              loading: () => const SizedBox.shrink(),
              error: (_, _) => const SizedBox.shrink(),
            );
          },
          loading: () => const SizedBox.shrink(),
          error: (_, _) => const SizedBox.shrink(),
        ),
        const SizedBox(height: 12),
        Align(
          alignment: Alignment.centerRight,
          child: OutlinedButton.icon(
            onPressed: () => _descargarFicha(context, ref),
            icon: const Icon(Icons.picture_as_pdf_outlined),
            label: const Text('Descargar ficha (PDF)'),
          ),
        ),
      ],
    );
  }

  Future<void> _descargarFicha(BuildContext context, WidgetRef ref) async {
    final messenger = ScaffoldMessenger.of(context);
    try {
      final bytes = await ref.read(perfilRepositoryProvider).descargarFichaPdf();

      if (kIsWeb) {
        messenger.showSnackBar(
          const SnackBar(content: Text('La descarga de PDF está disponible en la app instalada (Android/iOS).')),
        );
        return;
      }

      final file = File('${Directory.systemTemp.path}/ficha_${perfil.dni}.pdf');
      await file.writeAsBytes(bytes);
      final abierto = await launchUrl(Uri.file(file.path), mode: LaunchMode.externalApplication);
      if (!abierto) {
        messenger.showSnackBar(SnackBar(content: Text('Ficha guardada en ${file.path}')));
      }
    } on ApiException catch (e) {
      messenger.showSnackBar(SnackBar(content: Text(e.message)));
    }
  }
}

class _MatriculadoBadge extends StatelessWidget {
  const _MatriculadoBadge({required this.periodoNombre});

  final String periodoNombre;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: AppColors.successBg,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.check_circle, color: AppColors.success, size: 18),
          const SizedBox(width: 8),
          Text('Matriculado · $periodoNombre', style: const TextStyle(color: AppColors.success)),
        ],
      ),
    );
  }
}

class _MatriculaPendienteAviso extends StatelessWidget {
  const _MatriculaPendienteAviso({required this.periodoNombre});

  final String periodoNombre;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.warningBg,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          const Icon(Icons.error_outline, color: AppColors.warning),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              'El período $periodoNombre está abierto. Aún no completas tu ficha de matrícula.',
              style: const TextStyle(color: AppColors.warning),
            ),
          ),
        ],
      ),
    );
  }
}

class _DatosPersonalesCard extends StatelessWidget {
  const _DatosPersonalesCard({required this.perfil});

  final AlumnoPerfil perfil;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const CircleAvatar(
                  radius: 28,
                  backgroundColor: AppColors.background,
                  child: Icon(Icons.person_outline, color: AppColors.muted, size: 28),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Text(perfil.nombreCompleto, style: Theme.of(context).textTheme.titleLarge),
                ),
              ],
            ),
            const SizedBox(height: 16),
            _DatoRow(label: 'DNI', value: perfil.dni),
            _DatoRow(label: 'Correo', value: perfil.email),
            _DatoRow(label: 'Teléfono', value: perfil.numero),
            _DatoRow(label: 'Núm. de referencia', value: perfil.numeroReferencia),
            _DatoRow(label: 'Domicilio', value: perfil.domicilio),
          ],
        ),
      ),
    );
  }
}

class _DatoRow extends StatelessWidget {
  const _DatoRow({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(label, style: const TextStyle(color: AppColors.muted)),
          ),
          Expanded(child: Text(value)),
        ],
      ),
    );
  }
}

class _ProgramaCicloCard extends StatelessWidget {
  const _ProgramaCicloCard({required this.perfil});

  final AlumnoPerfil perfil;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Programa', style: TextStyle(color: AppColors.muted)),
                  Text(perfil.programaNombre ?? '—', style: Theme.of(context).textTheme.titleMedium),
                ],
              ),
            ),
            if (perfil.cicloNombre != null)
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Ciclo', style: TextStyle(color: AppColors.muted)),
                    Text(perfil.cicloNombre!, style: Theme.of(context).textTheme.titleMedium),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _CursosDelSemestre extends ConsumerWidget {
  const _CursosDelSemestre();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final cursos = ref.watch(cursosProvider);

    return cursos.when(
      data: (list) => list.isEmpty
          ? const Text('No tienes cursos en el periodo actual.')
          : Column(children: [for (final curso in list) _CursoDelSemestreTile(curso: curso)]),
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, _) => Text(error is ApiException ? error.message : 'No se pudieron cargar los cursos.'),
    );
  }
}

class _CursoDelSemestreTile extends StatelessWidget {
  const _CursoDelSemestreTile({required this.curso});

  final Curso curso;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        title: Text(curso.nombre),
        subtitle: Text('${curso.cc} · ${curso.horas} h'),
        trailing: curso.classroom != null
            ? IconButton(
                icon: const Icon(Icons.open_in_new),
                tooltip: 'Abrir classroom',
                onPressed: () => launchUrl(Uri.parse(curso.classroom!), mode: LaunchMode.externalApplication),
              )
            : null,
      ),
    );
  }
}
