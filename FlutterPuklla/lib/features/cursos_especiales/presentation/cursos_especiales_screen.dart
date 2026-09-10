import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../../../routing/app_drawer.dart';
import '../application/cursos_especiales_controller.dart';
import '../application/cursos_especiales_providers.dart';
import '../data/curso_especial.dart';

class CursosEspecialesScreen extends ConsumerWidget {
  const CursosEspecialesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final catalogo = ref.watch(cursosEspecialesProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Cursos especiales')),
      drawer: const AppDrawer(),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(cursosEspecialesProvider.future),
        child: catalogo.when(
          data: (cursos) => cursos.isEmpty
              ? ListView(
                  children: const [
                    Padding(
                      padding: EdgeInsets.all(24),
                      child: Text('No hay cursos especiales disponibles por ahora.'),
                    ),
                  ],
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: cursos.length,
                  itemBuilder: (context, index) => _CursoEspecialCard(curso: cursos[index]),
                ),
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, _) => Center(
            child: Text(
              error is ApiException ? error.message : 'No se pudo cargar el catálogo.',
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
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        title: Text(curso.nombre),
        subtitle: curso.descripcion != null ? Text(curso.descripcion!) : null,
        trailing: curso.inscrito
            ? Text('${curso.porcentaje ?? 0}%')
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
