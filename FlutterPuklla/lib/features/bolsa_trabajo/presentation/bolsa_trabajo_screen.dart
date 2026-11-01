import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../routing/app_drawer.dart';
import '../application/bolsa_trabajo_providers.dart';
import '../data/bolsa_trabajo_oferta.dart';

class BolsaTrabajoScreen extends ConsumerStatefulWidget {
  const BolsaTrabajoScreen({super.key});

  @override
  ConsumerState<BolsaTrabajoScreen> createState() => _BolsaTrabajoScreenState();
}

class _BolsaTrabajoScreenState extends ConsumerState<BolsaTrabajoScreen> {
  final TextEditingController _searchController = TextEditingController();
  int _filtroEstado = 0; // 0: Todas, 1: Vigentes, 2: Institucionales

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final ofertasAsync = ref.watch(bolsaTrabajoProvider);
    final isDark = PukllaDashboardStyles.isDark(context);

    return Scaffold(
      backgroundColor: PukllaDashboardStyles.backgroundColor(context),
      appBar: AppBar(
        title: const Text('Bolsa de trabajo'),
        actions: const [PukllaAppBarActions()],
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(bolsaTrabajoProvider.future),
        child: ofertasAsync.when(
          data: (ofertas) => _buildContent(ofertas, isDark),
          loading: () => _buildSkeletonLoader(isDark),
          error: (error, _) => Padding(
            padding: const EdgeInsets.all(24),
            child: ErrorState(
              message: 'No se pudo cargar la bolsa de trabajo.',
              onRetry: () => ref.refresh(bolsaTrabajoProvider),
            ),
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
        PukllaSkeletonCard(height: 190),
        SizedBox(height: 14),
        PukllaSkeletonCard(height: 190),
      ],
    );
  }

  Widget _buildContent(List<BolsaTrabajoOferta> ofertas, bool isDark) {
    final vigentesCount = ofertas.where((o) => o.vigente).length;
    final conContactoCount = ofertas.where((o) => o.telefonoPublicador != null && o.telefonoPublicador!.isNotEmpty).length;

    final query = _searchController.text.trim().toLowerCase();
    final ofertasFiltradas = ofertas.where((o) {
      if (query.isNotEmpty) {
        final matchesNombre = o.nombre.toLowerCase().contains(query);
        final matchesResumen = o.resumenLimpio.toLowerCase().contains(query);
        if (!matchesNombre && !matchesResumen) return false;
      }
      if (_filtroEstado == 1) return o.vigente;
      if (_filtroEstado == 2) return !o.vigente;
      return true;
    }).toList();

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
      children: [
        // ── HERO BANNER SENIOR ──
        _BolsaHeroBanner(
          totalOfertas: ofertas.length,
          vigentesCount: vigentesCount,
          isDark: isDark,
        ),
        const SizedBox(height: 16),

        // ── STATS ROW ──
        Row(
          children: [
            Expanded(
              child: _BolsaStatCard(
                icon: Icons.work_outline_rounded,
                titulo: 'Total Convocatorias',
                valor: ofertas.length.toString(),
                color: AppColors.teal,
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _BolsaStatCard(
                icon: Icons.check_circle_outline_rounded,
                titulo: 'Convocatorias Activas',
                valor: vigentesCount.toString(),
                color: const Color(0xFF10B981),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _BolsaStatCard(
                icon: Icons.phone_in_talk_outlined,
                titulo: 'Contacto Directo',
                valor: conContactoCount.toString(),
                color: AppColors.goldHover,
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
                    hintText: 'Buscar convocatorias, prácticas, vacantes...',
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
              _BolsaFilterChip(
                label: 'Todas (${ofertas.length})',
                isSelected: _filtroEstado == 0,
                onTap: () => setState(() => _filtroEstado = 0),
                isDark: isDark,
              ),
              const SizedBox(width: 8),
              _BolsaFilterChip(
                label: 'Convocatorias Activas ($vigentesCount)',
                isSelected: _filtroEstado == 1,
                onTap: () => setState(() => _filtroEstado = 1),
                isDark: isDark,
              ),
              const SizedBox(width: 8),
              _BolsaFilterChip(
                label: 'Institucionales (${ofertas.length - vigentesCount})',
                isSelected: _filtroEstado == 2,
                onTap: () => setState(() => _filtroEstado = 2),
                isDark: isDark,
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // ── LISTADO DE OFERTAS ──
        if (ofertas.isEmpty)
          Container(
            padding: const EdgeInsets.all(32),
            decoration: PukllaDashboardStyles.cardDecoration(context, radius: 18),
            child: Column(
              children: [
                const Icon(Icons.work_off_outlined, size: 48, color: AppColors.muted),
                const SizedBox(height: 12),
                Text(
                  'No hay ofertas laborales disponibles por ahora.',
                  style: TextStyle(
                    color: PukllaDashboardStyles.textPrimary(context),
                    fontSize: 15,
                    fontWeight: FontWeight.w700,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 6),
                Text(
                  'Vuelve pronto para consultar nuevas convocatorias y prácticas docentes para egresados.',
                  style: TextStyle(
                    color: PukllaDashboardStyles.textSecondary(context),
                    fontSize: 12.5,
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          )
        else if (ofertasFiltradas.isEmpty)
          Container(
            padding: const EdgeInsets.all(28),
            decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
            child: Column(
              children: [
                const Icon(Icons.search_off_rounded, size: 42, color: AppColors.muted),
                const SizedBox(height: 10),
                Text(
                  'No se encontraron ofertas con ese filtro de búsqueda.',
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
          for (final oferta in ofertasFiltradas) ...[
            _OfertaCard(oferta: oferta),
            const SizedBox(height: 16),
          ],
      ],
    );
  }
}

class _BolsaHeroBanner extends StatelessWidget {
  const _BolsaHeroBanner({
    required this.totalOfertas,
    required this.vigentesCount,
    required this.isDark,
  });

  final int totalOfertas;
  final int vigentesCount;
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
                child: const Icon(Icons.work_rounded, color: AppColors.gold, size: 22),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Bolsa Laboral Pukllasunchis',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 17,
                        fontWeight: FontWeight.w800,
                        letterSpacing: -0.2,
                      ),
                    ),
                    SizedBox(height: 2),
                    Text(
                      'Prácticas, vacantes y oportunidades',
                      style: TextStyle(color: Color(0xFFCBD5E1), fontSize: 12),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: const Color(0xFF10B981)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.fiber_manual_record, color: Color(0xFF10B981), size: 8),
                    const SizedBox(width: 5),
                    Text(
                      '$vigentesCount Activas',
                      style: const TextStyle(
                        color: Color(0xFF10B981),
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
            'Encuentra convocatorias públicas y privadas para docentes en educación inicial, primaria e intercultural bilingüe.',
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

class _BolsaStatCard extends StatelessWidget {
  const _BolsaStatCard({
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

class _BolsaFilterChip extends StatelessWidget {
  const _BolsaFilterChip({
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

class _OfertaCard extends StatelessWidget {
  const _OfertaCard({required this.oferta});

  final BolsaTrabajoOferta oferta;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final fecha = oferta.fechaPublicacion;
    final fechaTexto = fecha != null
        ? '${fecha.day.toString().padLeft(2, '0')}/${fecha.month.toString().padLeft(2, '0')}/${fecha.year}'
        : 'Reciente';

    final accentColor = oferta.vigente ? const Color(0xFF10B981) : AppColors.gold;

    return Container(
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 18),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(18),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Barra vertical lateral de estado
              Container(width: 5, color: accentColor),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (oferta.imagenUrl != null && oferta.imagenUrl!.isNotEmpty)
                      Image.network(
                        oferta.imagenUrl!,
                        height: 160,
                        width: double.infinity,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => const SizedBox.shrink(),
                      ),
                    Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                decoration: BoxDecoration(
                                  color: oferta.vigente
                                      ? const Color(0xFF10B981).withValues(alpha: 0.16)
                                      : AppColors.gold.withValues(alpha: 0.16),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text(
                                  oferta.vigente ? 'Convocatoria Activa' : 'Institucional',
                                  style: TextStyle(
                                    color: oferta.vigente
                                        ? (isDark ? const Color(0xFF34D399) : const Color(0xFF047857))
                                        : AppColors.goldHover,
                                    fontSize: 12,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                              ),
                              const Spacer(),
                              Icon(
                                Icons.calendar_today_outlined,
                                size: 12.5,
                                color: PukllaDashboardStyles.textSecondary(context),
                              ),
                              const SizedBox(width: 4),
                              Text(
                                fechaTexto,
                                style: TextStyle(
                                  fontSize: 12,
                                  color: PukllaDashboardStyles.textSecondary(context),
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Text(
                            oferta.nombre,
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w800,
                              color: PukllaDashboardStyles.textPrimary(context),
                              height: 1.25,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            oferta.resumenLimpio,
                            style: TextStyle(
                              fontSize: 13.5,
                              color: PukllaDashboardStyles.textSecondary(context),
                              height: 1.45,
                            ),
                          ),
                          if (oferta.telefonoPublicador != null && oferta.telefonoPublicador!.isNotEmpty) ...[
                            const SizedBox(height: 14),
                            InkWell(
                              onTap: () {
                                final uri = Uri.parse('tel:${oferta.telefonoPublicador}');
                                launchUrl(uri);
                              },
                              borderRadius: BorderRadius.circular(10),
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                decoration: BoxDecoration(
                                  color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9),
                                  borderRadius: BorderRadius.circular(10),
                                  border: Border.all(
                                    color: isDark ? AppColors.darkBorder : Colors.transparent,
                                  ),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(Icons.phone_outlined, size: 16, color: AppColors.teal),
                                    const SizedBox(width: 6),
                                    Text(
                                      'Contacto: ${oferta.telefonoPublicador}',
                                      style: const TextStyle(
                                        fontSize: 12.5,
                                        fontWeight: FontWeight.w700,
                                        color: AppColors.teal,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
