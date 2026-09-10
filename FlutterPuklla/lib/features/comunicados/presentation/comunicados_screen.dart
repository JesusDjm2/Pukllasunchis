import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/network/api_exception.dart';
import '../../../routing/app_drawer.dart';
import '../application/comunicados_providers.dart';
import '../data/comunicado.dart';
import '../data/comunicados_page.dart';

class ComunicadosScreen extends ConsumerWidget {
  const ComunicadosScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final pagina = ref.watch(comunicadosProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Comunicados')),
      drawer: const AppDrawer(),
      body: pagina.when(
        data: (data) => data.items.isEmpty
            ? const Center(child: Text('No hay comunicados publicados.'))
            : Column(
                children: [
                  Expanded(
                    child: RefreshIndicator(
                      onRefresh: () => ref.refresh(comunicadosProvider.future),
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: data.items.length,
                        itemBuilder: (context, index) => _ComunicadoCard(comunicado: data.items[index]),
                      ),
                    ),
                  ),
                  _PaginadorBar(pagina: data),
                ],
              ),
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => Center(
          child: Text(error is ApiException ? error.message : 'No se pudieron cargar los comunicados.'),
        ),
      ),
    );
  }
}

class _ComunicadoCard extends StatelessWidget {
  const _ComunicadoCard({required this.comunicado});

  final Comunicado comunicado;

  Future<void> _abrirArchivo(BuildContext context) async {
    final uri = Uri.parse(comunicado.archivoUrl);
    final abierto = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!abierto && context.mounted) {
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('No se pudo abrir el archivo.')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(comunicado.titulo, style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 4),
            Text(
              _formatDate(comunicado.fechaPublicacion),
              style: Theme.of(context).textTheme.bodySmall,
            ),
            if (comunicado.descripcion != null) ...[
              const SizedBox(height: 8),
              Text(comunicado.descripcion!),
            ],
            if (comunicado.esImagen) ...[
              const SizedBox(height: 12),
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: Image.network(
                  comunicado.archivoUrl,
                  errorBuilder: (context, error, stackTrace) => const SizedBox.shrink(),
                ),
              ),
            ],
            const SizedBox(height: 8),
            Align(
              alignment: Alignment.centerRight,
              child: TextButton.icon(
                onPressed: () => _abrirArchivo(context),
                icon: Icon(comunicado.esImagen ? Icons.image_outlined : Icons.picture_as_pdf_outlined),
                label: Text(comunicado.esImagen ? 'Ver imagen completa' : 'Ver documento'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _PaginadorBar extends ConsumerWidget {
  const _PaginadorBar({required this.pagina});

  final ComunicadosPage pagina;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          TextButton(
            onPressed: pagina.hayPaginaAnterior
                ? () => ref.read(comunicadosPageIndexProvider.notifier).state--
                : null,
            child: const Text('Anterior'),
          ),
          Text('Página ${pagina.currentPage} de ${pagina.lastPage}'),
          TextButton(
            onPressed: pagina.hayPaginaSiguiente
                ? () => ref.read(comunicadosPageIndexProvider.notifier).state++
                : null,
            child: const Text('Siguiente'),
          ),
        ],
      ),
    );
  }
}

String _formatDate(DateTime date) {
  return '${date.day.toString().padLeft(2, '0')}/'
      '${date.month.toString().padLeft(2, '0')}/'
      '${date.year}';
}
