import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../routing/app_drawer.dart';
import '../application/comunicados_providers.dart';
import '../data/comunicado.dart';
import '../data/comunicados_page.dart';

class ComunicadosScreen extends ConsumerStatefulWidget {
  const ComunicadosScreen({super.key});

  @override
  ConsumerState<ComunicadosScreen> createState() => _ComunicadosScreenState();
}

class _ComunicadosScreenState extends ConsumerState<ComunicadosScreen> {
  final TextEditingController _searchController = TextEditingController();
  int _filtroTipo = 0; // 0: Todos, 1: Con documento, 2: Con imagen

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final pagina = ref.watch(comunicadosProvider);
    final isDark = PukllaDashboardStyles.isDark(context);

    return Scaffold(
      backgroundColor: PukllaDashboardStyles.backgroundColor(context),
      appBar: AppBar(
        title: const Text('Comunicados'),
        actions: const [PukllaAppBarActions()],
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: pagina.when(
        data: (data) => _buildContent(data, isDark),
        loading: () => _buildSkeletonLoader(isDark),
        error: (error, _) => Padding(
          padding: const EdgeInsets.all(24),
          child: ErrorState(
            message: error is ApiException ? error.message : 'No se pudieron cargar los comunicados.',
            onRetry: () => ref.invalidate(comunicadosProvider),
          ),
        ),
      ),
    );
  }

  Widget _buildSkeletonLoader(bool isDark) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
      children: const [
        PukllaSkeletonCard(height: 150),
        SizedBox(height: 16),
        Row(
          children: [
            Expanded(child: PukllaSkeletonCard(height: 68)),
            SizedBox(width: 8),
            Expanded(child: PukllaSkeletonCard(height: 68)),
            SizedBox(width: 8),
            Expanded(child: PukllaSkeletonCard(height: 68)),
          ],
        ),
        SizedBox(height: 16),
        PukllaSkeletonCard(height: 160),
        SizedBox(height: 12),
        PukllaSkeletonCard(height: 160),
      ],
    );
  }

  Widget _buildContent(ComunicadosPage pagina, bool isDark) {
    final items = pagina.items;
    final conDocs = items.where((c) => !c.esImagen).length;
    final conImgs = items.where((c) => c.esImagen).length;

    final query = _searchController.text.trim().toLowerCase();
    final itemsFiltrados = items.where((c) {
      if (query.isNotEmpty) {
        final matchTitulo = c.titulo.toLowerCase().contains(query);
        final matchDesc = c.descripcion?.toLowerCase().contains(query) ?? false;
        if (!matchTitulo && !matchDesc) return false;
      }
      if (_filtroTipo == 1) return !c.esImagen;
      if (_filtroTipo == 2) return c.esImagen;
      return true;
    }).toList();

    return Column(
      children: [
        Expanded(
          child: RefreshIndicator(
            onRefresh: () => ref.refresh(comunicadosProvider.future),
            child: ListView(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 20),
              children: [
                // ── HERO BANNER SENIOR ──
                _ComunicadosHeroBanner(
                  totalComunicados: items.length,
                  isDark: isDark,
                ),
                const SizedBox(height: 16),

                // ── STATS ROW ──
                Row(
                  children: [
                    Expanded(
                      child: _ComunicadoStatCard(
                        icon: Icons.campaign_rounded,
                        titulo: 'Publicados',
                        valor: items.length.toString(),
                        color: AppColors.teal,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: _ComunicadoStatCard(
                        icon: Icons.picture_as_pdf_rounded,
                        titulo: 'Documentos',
                        valor: conDocs.toString(),
                        color: AppColors.goldHover,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: _ComunicadoStatCard(
                        icon: Icons.image_rounded,
                        titulo: 'Imágenes / Afiches',
                        valor: conImgs.toString(),
                        color: const Color(0xFF10B981),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // ── BUSCADOR EN VIVO ──
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  decoration: PukllaDashboardStyles.cardDecoration(context, radius: 14),
                  child: Row(
                    children: [
                      Icon(Icons.search_rounded, size: 20, color: PukllaDashboardStyles.textSecondary(context)),
                      const SizedBox(width: 10),
                      Expanded(
                        child: TextField(
                          controller: _searchController,
                          onChanged: (_) => setState(() {}),
                          style: TextStyle(
                            fontSize: 13.5,
                            color: PukllaDashboardStyles.textPrimary(context),
                          ),
                          decoration: InputDecoration(
                            hintText: 'Buscar en comunicados y avisos...',
                            hintStyle: TextStyle(
                              fontSize: 13,
                              color: PukllaDashboardStyles.textSecondary(context),
                            ),
                            border: InputBorder.none,
                            isDense: true,
                            contentPadding: const EdgeInsets.symmetric(vertical: 6),
                          ),
                        ),
                      ),
                      if (_searchController.text.isNotEmpty)
                        GestureDetector(
                          onTap: () {
                            _searchController.clear();
                            setState(() {});
                          },
                          child: Icon(Icons.close_rounded, size: 18, color: PukllaDashboardStyles.textSecondary(context)),
                        ),
                    ],
                  ),
                ),
                const SizedBox(height: 10),

                // ── FILTROS CHIPS ──
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _ComunicadoFilterChip(
                        label: 'Todos (${items.length})',
                        isSelected: _filtroTipo == 0,
                        onTap: () => setState(() => _filtroTipo = 0),
                        isDark: isDark,
                      ),
                      const SizedBox(width: 8),
                      _ComunicadoFilterChip(
                        label: 'Documentos PDF ($conDocs)',
                        isSelected: _filtroTipo == 1,
                        onTap: () => setState(() => _filtroTipo = 1),
                        isDark: isDark,
                      ),
                      const SizedBox(width: 8),
                      _ComunicadoFilterChip(
                        label: 'Imágenes ($conImgs)',
                        isSelected: _filtroTipo == 2,
                        onTap: () => setState(() => _filtroTipo = 2),
                        isDark: isDark,
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // ── LISTADO DE COMUNICADOS ──
                if (items.isEmpty)
                  Container(
                    padding: const EdgeInsets.all(32),
                    decoration: PukllaDashboardStyles.cardDecoration(context, radius: 18),
                    child: Column(
                      children: [
                        const Icon(Icons.campaign_outlined, size: 48, color: AppColors.muted),
                        const SizedBox(height: 12),
                        Text(
                          'No hay comunicados publicados.',
                          style: TextStyle(
                            color: PukllaDashboardStyles.textPrimary(context),
                            fontSize: 15,
                            fontWeight: FontWeight.w700,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 6),
                        Text(
                          'Los comunicados oficiales y avisos de rectoría aparecerán en este espacio.',
                          style: TextStyle(
                            color: PukllaDashboardStyles.textSecondary(context),
                            fontSize: 12.5,
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  )
                else if (itemsFiltrados.isEmpty)
                  Container(
                    padding: const EdgeInsets.all(28),
                    decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
                    child: Column(
                      children: [
                        const Icon(Icons.search_off_rounded, size: 42, color: AppColors.muted),
                        const SizedBox(height: 10),
                        Text(
                          'No se encontraron comunicados con ese término de búsqueda.',
                          style: TextStyle(
                            color: PukllaDashboardStyles.textPrimary(context),
                            fontSize: 13.5,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  )
                else
                  for (final comunicado in itemsFiltrados) ...[
                    _ComunicadoCard(comunicado: comunicado),
                    const SizedBox(height: 14),
                  ],
              ],
            ),
          ),
        ),
        _PaginadorBar(pagina: pagina),
      ],
    );
  }
}

class _ComunicadosHeroBanner extends StatelessWidget {
  const _ComunicadosHeroBanner({
    required this.totalComunicados,
    required this.isDark,
  });

  final int totalComunicados;
  final bool isDark;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: isDark
              ? const [Color(0xFF0F1E36), Color(0xFF1B2A4A)]
              : const [Color(0xFF1B2A4A), Color(0xFF2C3E60)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.35 : 0.15),
            blurRadius: 14,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(9),
                decoration: BoxDecoration(
                  color: AppColors.gold.withValues(alpha: 0.20),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.gold.withValues(alpha: 0.5)),
                ),
                child: const Icon(Icons.campaign_rounded, color: AppColors.gold, size: 22),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Comunicados Institucionales',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 17,
                        fontWeight: FontWeight.w800,
                        letterSpacing: -0.2,
                      ),
                    ),
                    SizedBox(height: 2),
                    Text(
                      'Avisos oficiales y resoluciones',
                      style: TextStyle(color: Color(0xFFCBD5E1), fontSize: 12),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.gold.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: AppColors.gold),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.notifications_active_outlined, color: AppColors.gold, size: 10),
                    const SizedBox(width: 5),
                    Text(
                      '$totalComunicados Avisos',
                      style: const TextStyle(
                        color: AppColors.gold,
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          const Text(
            'Mantente informado con las circulares académicas, convocatorias institucionales y avisos oficiales de Pukllasunchis.',
            style: TextStyle(
              fontSize: 12.5,
              color: Color(0xFFE2E8F0),
              height: 1.35,
            ),
          ),
        ],
      ),
    );
  }
}

class _ComunicadoStatCard extends StatelessWidget {
  const _ComunicadoStatCard({
    required this.icon,
    required this.titulo,
    required this.valor,
    required this.color,
  });

  final IconData icon;
  final String titulo;
  final String valor;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 16, color: color),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  titulo,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: PukllaDashboardStyles.textSecondary(context),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            valor,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w900,
              color: PukllaDashboardStyles.textPrimary(context),
            ),
          ),
        ],
      ),
    );
  }
}

class _ComunicadoFilterChip extends StatelessWidget {
  const _ComunicadoFilterChip({
    required this.label,
    required this.isSelected,
    required this.onTap,
    required this.isDark,
  });

  final String label;
  final bool isSelected;
  final VoidCallback onTap;
  final bool isDark;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
        decoration: BoxDecoration(
          color: isSelected
              ? AppColors.teal.withValues(alpha: isDark ? 0.25 : 0.12)
              : (isDark ? AppColors.darkSurface : Colors.white),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected
                ? AppColors.teal
                : (isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0)),
            width: isSelected ? 1.5 : 1,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
            color: isSelected
                ? (isDark ? const Color(0xFF5EEAD4) : AppColors.teal)
                : PukllaDashboardStyles.textSecondary(context),
          ),
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
    final isDark = PukllaDashboardStyles.isDark(context);
    final accentColor = comunicado.esImagen ? const Color(0xFF10B981) : AppColors.teal;

    return Container(
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(width: 5, color: accentColor),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            width: 38,
                            height: 38,
                            decoration: BoxDecoration(
                              color: accentColor.withValues(alpha: isDark ? 0.22 : 0.10),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Icon(
                              comunicado.esImagen ? Icons.image_outlined : Icons.campaign_outlined,
                              size: 20,
                              color: accentColor,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  comunicado.titulo,
                                  style: TextStyle(
                                    fontSize: 15,
                                    fontWeight: FontWeight.w800,
                                    color: PukllaDashboardStyles.textPrimary(context),
                                    height: 1.25,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Row(
                                  children: [
                                    Icon(
                                      Icons.calendar_today_outlined,
                                      size: 12,
                                      color: PukllaDashboardStyles.textSecondary(context),
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      _formatDate(comunicado.fechaPublicacion),
                                      style: TextStyle(
                                        fontSize: 12,
                                        color: PukllaDashboardStyles.textSecondary(context),
                                        fontWeight: FontWeight.w500,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      if (comunicado.descripcion != null && comunicado.descripcion!.trim().isNotEmpty) ...[
                        const SizedBox(height: 12),
                        Text(
                          comunicado.descripcion!,
                          style: TextStyle(
                            fontSize: 13.5,
                            height: 1.45,
                            color: isDark ? Colors.white70 : const Color(0xFF334155),
                          ),
                        ),
                      ],
                      if (comunicado.esImagen) ...[
                        const SizedBox(height: 12),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(10),
                          child: Image.network(
                            comunicado.archivoUrl,
                            fit: BoxFit.cover,
                            errorBuilder: (context, error, stackTrace) => const SizedBox.shrink(),
                          ),
                        ),
                      ],
                      const SizedBox(height: 10),
                      Align(
                        alignment: Alignment.centerRight,
                        child: OutlinedButton.icon(
                          onPressed: () => _abrirArchivo(context),
                          style: OutlinedButton.styleFrom(
                            side: BorderSide(
                              color: isDark ? AppColors.darkBorder : const Color(0xFFCBD5E1),
                            ),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                          ),
                          icon: Icon(
                            comunicado.esImagen ? Icons.fullscreen_rounded : Icons.file_download_outlined,
                            size: 16,
                            color: accentColor,
                          ),
                          label: Text(
                            comunicado.esImagen ? 'Ver afiche completo' : 'Abrir documento',
                            style: TextStyle(
                              fontSize: 12.5,
                              fontWeight: FontWeight.w700,
                              color: accentColor,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
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
    return Container(
      decoration: BoxDecoration(
        color: PukllaDashboardStyles.surfaceColor(context),
        border: Border(top: BorderSide(color: PukllaDashboardStyles.borderColor(context))),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          TextButton.icon(
            onPressed: pagina.hayPaginaAnterior
                ? () => ref.read(comunicadosPageIndexProvider.notifier).state--
                : null,
            icon: const Icon(Icons.chevron_left, size: 18),
            label: const Text('Anterior'),
          ),
          Text(
            'Página ${pagina.currentPage} de ${pagina.lastPage}',
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: PukllaDashboardStyles.textSecondary(context),
            ),
          ),
          TextButton.icon(
            onPressed: pagina.hayPaginaSiguiente
                ? () => ref.read(comunicadosPageIndexProvider.notifier).state++
                : null,
            label: const Text('Siguiente'),
            icon: const Icon(Icons.chevron_right, size: 18),
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
