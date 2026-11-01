import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../routing/app_drawer.dart';
import '../../notificaciones/presentation/notification_bell_action.dart';
import '../application/cursos_especiales_controller.dart';
import '../application/cursos_especiales_providers.dart';
import '../data/curso_especial.dart';

class CursosEspecialesScreen extends ConsumerWidget {
  const CursosEspecialesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final catalogo = ref.watch(cursosEspecialesProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Cursos especiales'),
        actions: const [PukllaAppBarActions()],
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(cursosEspecialesProvider.future),
        child: catalogo.when(
          data: (cursos) => cursos.isEmpty
              ? ListView(
                  children: const [
                    EmptyState(
                      icon: Icons.school_outlined,
                      message: 'No hay cursos especiales disponibles por ahora.',
                    ),
                  ],
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: cursos.length,
                  itemBuilder: (context, index) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: _CursoEspecialCard(curso: cursos[index]),
                  ),
                ),
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, _) => Padding(
            padding: const EdgeInsets.all(16),
            child: ErrorState(
              message: error is ApiException ? error.message : 'No se pudo cargar el catálogo.',
              onRetry: () => ref.invalidate(cursosEspecialesProvider),
            ),
          ),
        ),
      ),
    );
  }
}

class _CursoEspecialCard extends ConsumerWidget {
  const _CursoEspecialCard({required this.curso});

  final CursoEspecial curso;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Card(
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
        leading: Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: (curso.inscrito ? AppColors.success : AppColors.gold).withValues(alpha: 0.14),
            shape: BoxShape.circle,
          ),
          child: Icon(
            curso.inscrito ? Icons.play_circle_outline : Icons.auto_stories_outlined,
            size: 20,
            color: curso.inscrito ? AppColors.success : AppColors.gold,
          ),
        ),
        title: Text(curso.nombre, style: const TextStyle(fontWeight: FontWeight.w600)),
        subtitle: curso.descripcion != null ? Text(curso.descripcion!) : null,
        trailing: curso.inscrito
            ? Text(
                '${curso.porcentaje ?? 0}%',
                style: const TextStyle(fontWeight: FontWeight.w700, color: AppColors.success),
              )
            : FilledButton(
                onPressed: () async {
                  try {
                    await ref
                        .read(cursosEspecialesControllerProvider)
                        .inscribir(curso.id);
                  } on ApiException catch (e) {
                    if (context.mounted) {
                      ScaffoldMessenger.of(context)
                          .showSnackBar(SnackBar(content: Text(e.message)));
                    }
                  }
                },
                child: const Text('Inscribirme'),
              ),
        onTap: curso.inscrito ? () => context.push('/cursos-especiales/${curso.id}') : null,
      ),
    );
  }
}
