import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/env/env.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../routing/app_drawer.dart';
import '../../auth/application/auth_controller.dart';
import '../../perfil/application/perfil_providers.dart';

/// Pantalla completa de Guías de TI, Tesis y Biblioteca Virtual.
/// Replica al 100% las funciones del portal web:
/// 1. Banner de la NUEVA Biblioteca Virtual oficial y acceso a Google Sites histórico.
/// 2. Pestaña de Trabajo de Investigación (TI) con selector FID / PPD y descargas Word / PDF.
/// 3. Pestaña de Tesis con selector FID / PPD y descargas Word / PDF.
/// 4. Pestaña de Videos tutoriales paso a paso (5 videos de YouTube) con miniaturas y reproducción.
class FormatosBibliotecaScreen extends ConsumerStatefulWidget {
  const FormatosBibliotecaScreen({super.key});

  @override
  ConsumerState<FormatosBibliotecaScreen> createState() => _FormatosBibliotecaScreenState();
}

class _FormatosBibliotecaScreenState extends ConsumerState<FormatosBibliotecaScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabController;

  // URL oficial de la nueva Biblioteca Virtual institucional
  static const String _urlBibliotecaVirtual = 'https://pukllavirtual.eesppukllasunchis.edu.pe/';
  static const String _urlBibliotecaHistorica =
      'https://sites.google.com/pukllavirtual.edu.pe/bibliotecaeesppuklla/inicio';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  String _resolverUrl(String archivo) {
    final apiBase = Env.apiBaseUrl;
    final host = apiBase.replaceAll('/api/v1', '');
    return '$host/pdf/$archivo';
  }

  void _abrirUrl(String url) async {
    final uri = Uri.parse(url);
    try {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final authUser = ref.watch(authControllerProvider).user;
    final perfil = ref.watch(perfilProvider).valueOrNull;

    final esPpd = (authUser?.roles.contains('alumnoB') ?? false) ||
        (perfil?.programaNombre?.toUpperCase().contains('PPD') ?? false) ||
        (perfil?.programaNombre?.toLowerCase().contains('profesionalización') ?? false);

    return Scaffold(
      backgroundColor: PukllaDashboardStyles.backgroundColor(context),
      appBar: AppBar(
        title: const Text(
          'Guías de TI, Tesis & Biblioteca',
          style: TextStyle(fontWeight: FontWeight.w800, fontSize: 18),
        ),
        actions: const [PukllaAppBarActions()],
        elevation: 0,
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: NestedScrollView(
        headerSliverBuilder: (context, innerBoxIsScrolled) {
          return [
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // ── BANNER DE LA NUEVA BIBLIOTECA VIRTUAL ──
                    _BibliotecaVirtualHeroBanner(
                      onIngresarBiblioteca: () => _abrirUrl(_urlBibliotecaVirtual),
                      onIngresarHistorica: () => _abrirUrl(_urlBibliotecaHistorica),
                    ),
                    const SizedBox(height: 18),

                    // ── BADGE INFORMATIVO DE MODALIDAD (FID o PPD) ──
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                      decoration: BoxDecoration(
                        color: isDark ? AppColors.darkSurface : const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            esPpd ? Icons.workspace_premium_outlined : Icons.school_outlined,
                            size: 16,
                            color: AppColors.goldHover,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            esPpd
                                ? 'Documentos para Profesionalización (PPD)'
                                : 'Documentos para Formación Inicial (FID)',
                            style: TextStyle(
                              fontSize: 12.5,
                              fontWeight: FontWeight.w700,
                              color: isDark ? Colors.white70 : const Color(0xFF334155),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),

                    // ── TABBAR DE NAVEGACIÓN MODULAR ──
                    Container(
                      decoration: BoxDecoration(
                        color: isDark ? AppColors.darkSurface : Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
                        ),
                      ),
                      child: TabBar(
                        controller: _tabController,
                        labelColor: AppColors.gold,
                        unselectedLabelColor:
                            isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                        indicatorColor: AppColors.gold,
                        indicatorSize: TabBarIndicatorSize.tab,
                        labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12.5),
                        unselectedLabelStyle:
                            const TextStyle(fontWeight: FontWeight.w600, fontSize: 12),
                        tabs: const [
                          Tab(
                            icon: Icon(Icons.science_outlined, size: 20),
                            text: 'Investigación (TI)',
                          ),
                          Tab(
                            icon: Icon(Icons.menu_book_rounded, size: 20),
                            text: 'Tesis',
                          ),
                          Tab(
                            icon: Icon(Icons.play_circle_outline_rounded, size: 20),
                            text: 'Videos (5)',
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ];
        },
        body: TabBarView(
          controller: _tabController,
          children: [
            // ── TAB 1: TRABAJO DE INVESTIGACIÓN (TI) ──
            _ListaDocumentosTab(
              intro:
                  'Para el grado de Bachiller: plantilla Word, carátula institucional y guías metodológicas del Trabajo de Investigación (TI).',
              documentos: [
                _DocumentoRecurso(
                  titulo: 'Formato de TI',
                  descripcion:
                      'Plantilla editable en Word con márgenes oficiales, tipografía, estructura por capítulos y numeración.',
                  tipo: 'Word',
                  archivo: 'FormatoTI.docx',
                  url: _resolverUrl('FormatoTI.docx'),
                ),
                _DocumentoRecurso(
                  titulo: esPpd ? 'Carátula de TI (PPD)' : 'Carátula de TI (FID)',
                  descripcion: esPpd
                      ? 'Carátula oficial para el Programa de Profesionalización Docente.'
                      : 'Carátula oficial para el Programa de Formación Inicial Docente.',
                  tipo: 'Word',
                  archivo: esPpd ? 'CaratulaTI-2025 PPD.docx' : 'CaratulaTI-2025.docx',
                  url: _resolverUrl(
                      esPpd ? 'CaratulaTI-2025 PPD.docx' : 'CaratulaTI-2025.docx'),
                ),
                _DocumentoRecurso(
                  titulo: 'Guía de Trabajo de Investigación',
                  descripcion:
                      'Directrices de investigación, pasos y contenido detallado de cada capítulo para tu TI.',
                  tipo: 'PDF',
                  archivo: 'Guia-Trabajo-de-Investigacion.pdf',
                  url: _resolverUrl('Guia-Trabajo-de-Investigacion.pdf'),
                ),
                _DocumentoRecurso(
                  titulo: 'Guía de Redacción y Citas 2025',
                  descripcion:
                      'Normas de redacción académica, estilo APA 7ma edición, citas textuales y elaboración de referencias.',
                  tipo: 'PDF',
                  archivo: 'Guia-de-Investigacion-2025.pdf',
                  url: _resolverUrl('Guia-de-Investigacion-2025.pdf'),
                ),
              ],
              onAbrir: _abrirUrl,
            ),

            // ── TAB 2: TESIS ──
            _ListaDocumentosTab(
              intro:
                  'Para la obtención de la Licenciatura: plantilla Word, carátula oficial y guías de elaboración de la Tesis de grado.',
              documentos: [
                _DocumentoRecurso(
                  titulo: 'Formato de Tesis',
                  descripcion:
                      'Plantilla editable en Word con márgenes reglamentarios, fuentes y formato estructurado para tesis.',
                  tipo: 'Word',
                  archivo: 'FormatoTesis.docx',
                  url: _resolverUrl('FormatoTesis.docx'),
                ),
                _DocumentoRecurso(
                  titulo: esPpd ? 'Carátula de Tesis (PPD)' : 'Carátula de Tesis (FID)',
                  descripcion: esPpd
                      ? 'Carátula oficial para sustentación de tesis en Profesionalización.'
                      : 'Carátula oficial para sustentación de tesis en Formación Inicial.',
                  tipo: 'Word',
                  archivo:
                      esPpd ? 'CaratulaTesis-2025-PPD.docx' : 'CaratulaTesis-2025.docx',
                  url: _resolverUrl(
                      esPpd ? 'CaratulaTesis-2025-PPD.docx' : 'CaratulaTesis-2025.docx'),
                ),
                _DocumentoRecurso(
                  titulo: 'Guía de Elaboración de Tesis',
                  descripcion:
                      'Manual institucional con los pasos, marco conceptual, metodología, resultados y conclusiones.',
                  tipo: 'PDF',
                  archivo: 'Guia-de-Elaboracion-de-Tesis.pdf',
                  url: _resolverUrl('Guia-de-Elaboracion-de-Tesis.pdf'),
                ),
                _DocumentoRecurso(
                  titulo: 'Guía de Redacción y Citas 2025',
                  descripcion:
                      'Normas de redacción académica, pautas de estilo APA 7ma edición y sustentación bibliográfica.',
                  tipo: 'PDF',
                  archivo: 'Guia-de-Investigacion-2025.pdf',
                  url: _resolverUrl('Guia-de-Investigacion-2025.pdf'),
                ),
              ],
              onAbrir: _abrirUrl,
            ),

            // ── TAB 3: VIDEOS PASO A PASO (5 VIDEOS DE LA WEB) ──
            _ListaVideosTab(
              intro:
                  'Videos tutoriales paso a paso para aplicar correctamente cada formato, sangría y norma en tu trabajo de investigación.',
              videos: const [
                _VideoTutorial(
                  id: 'FT-c2DfvNn8',
                  guia: 'Guía 1',
                  tema: 'Carátula Institucional',
                  descripcion:
                      'Configuración de datos personales, logos institucionales y formato oficial de la portada.',
                ),
                _VideoTutorial(
                  id: 'HqDAIgnL538',
                  guia: 'Guía 2',
                  tema: 'Formato General del Documento',
                  descripcion:
                      'Ajuste de márgenes, espaciado interlineal, tipografía formal y numeración de páginas.',
                ),
                _VideoTutorial(
                  id: 'd07cUzQLQwE',
                  guia: 'Guía 3',
                  tema: 'Resumen y Palabras Clave',
                  descripcion:
                      'Cómo redactar un resumen conciso y seleccionar palabras clave para tu investigación.',
                ),
                _VideoTutorial(
                  id: 'sBopJwQav-o',
                  guia: 'Guía 4',
                  tema: 'Títulos, Subtítulos e Índice',
                  descripcion:
                      'Jerarquía de estilos en Word y generación automática de la tabla de contenidos e índices.',
                ),
                _VideoTutorial(
                  id: '58ODcJy84h8',
                  guia: 'Guía 5',
                  tema: 'Referencias y Bibliografía APA',
                  descripcion:
                      'Formato de citación en el texto y construcción de referencias bibliográficas en formato APA 7.',
                ),
              ],
              onVerVideo: (id) => _abrirUrl('https://www.youtube.com/watch?v=$id'),
            ),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// COMPONENTES DE UI & MICRO-INTERACCIONES
// ─────────────────────────────────────────────────────────────────────────────

/// Hero Banner de la NUEVA Biblioteca Virtual
class _BibliotecaVirtualHeroBanner extends StatelessWidget {
  const _BibliotecaVirtualHeroBanner({
    required this.onIngresarBiblioteca,
    required this.onIngresarHistorica,
  });

  final VoidCallback onIngresarBiblioteca;
  final VoidCallback onIngresarHistorica;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1B2A4A), Color(0xFF2C3E60)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1B2A4A).withValues(alpha: 0.25),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: AppColors.gold.withValues(alpha: 0.20),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(
                    Icons.local_library_rounded,
                    color: Color(0xFFFDE68A),
                    size: 26,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: AppColors.gold.withValues(alpha: 0.25),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: const Text(
                          'NUEVA PLATAFORMA VIRTUAL',
                          style: TextStyle(
                            color: Color(0xFFFDE68A),
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ),
                      const SizedBox(height: 3),
                      const Text(
                        'Biblioteca Virtual Pukllasunchis',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 16.5,
                          fontWeight: FontWeight.w800,
                          letterSpacing: -0.2,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              'Explora el catálogo digital oficial, repositorio institucional, libros electrónicos y recursos pedagógicos en línea.',
              style: TextStyle(
                color: Colors.white.withValues(alpha: 0.85),
                fontSize: 12.5,
                height: 1.35,
              ),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: PukllaScaleTap(
                    onTap: onIngresarBiblioteca,
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      decoration: BoxDecoration(
                        color: AppColors.gold,
                        borderRadius: BorderRadius.circular(10),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.15),
                            blurRadius: 6,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.open_in_new_rounded, size: 16, color: Colors.white),
                          SizedBox(width: 6),
                          Text(
                            'Ingresar a la Biblioteca',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 13,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                PukllaScaleTap(
                  onTap: onIngresarHistorica,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: Colors.white.withValues(alpha: 0.2)),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.history_rounded,
                          size: 15,
                          color: Colors.white.withValues(alpha: 0.9),
                        ),
                        const SizedBox(width: 5),
                        Text(
                          'Google Sites',
                          style: TextStyle(
                            color: Colors.white.withValues(alpha: 0.9),
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}


/// Modelo de datos de un documento descargable
class _DocumentoRecurso {
  const _DocumentoRecurso({
    required this.titulo,
    required this.descripcion,
    required this.tipo,
    required this.archivo,
    required this.url,
  });

  final String titulo;
  final String descripcion;
  final String tipo; // 'Word' o 'PDF'
  final String archivo;
  final String url;
}

/// Vista de lista de documentos para las pestañas de TI y Tesis
class _ListaDocumentosTab extends StatelessWidget {
  const _ListaDocumentosTab({
    required this.intro,
    required this.documentos,
    required this.onAbrir,
  });

  final String intro;
  final List<_DocumentoRecurso> documentos;
  final void Function(String url) onAbrir;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 32),
      children: [
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          decoration: BoxDecoration(
            color: isDark ? AppColors.darkSurface : const Color(0xFFF8FAFC),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
            ),
          ),
          child: Row(
            children: [
              const Icon(Icons.info_outline_rounded, size: 18, color: AppColors.goldHover),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  intro,
                  style: TextStyle(
                    fontSize: 12,
                    color: isDark ? AppColors.darkMuted : const Color(0xFF475569),
                    height: 1.35,
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),
        ...documentos.map((doc) {
          final esWord = doc.tipo.toUpperCase() == 'WORD';
          final formatoColor = esWord ? const Color(0xFF2563EB) : const Color(0xFFE11D48);
          final formatoBg = formatoColor.withValues(alpha: isDark ? 0.20 : 0.10);
          final formatoIcon = esWord ? Icons.description_rounded : Icons.picture_as_pdf_rounded;

          return Container(
            margin: const EdgeInsets.only(bottom: 12),
            decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: formatoBg,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(formatoIcon, color: formatoColor, size: 24),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                doc.titulo,
                                style: TextStyle(
                                  fontSize: 14.5,
                                  fontWeight: FontWeight.w800,
                                  color: PukllaDashboardStyles.textPrimary(context),
                                ),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                              decoration: BoxDecoration(
                                color: formatoBg,
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                esWord ? 'DOCX' : 'PDF',
                                style: TextStyle(
                                  fontSize: 10.5,
                                  fontWeight: FontWeight.w800,
                                  color: formatoColor,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          doc.descripcion,
                          style: TextStyle(
                            fontSize: 12,
                            color: PukllaDashboardStyles.textSecondary(context),
                            height: 1.35,
                          ),
                        ),
                        const SizedBox(height: 10),
                        PukllaScaleTap(
                          onTap: () => onAbrir(doc.url),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: AppColors.tealDark,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  esWord ? Icons.download_rounded : Icons.visibility_outlined,
                                  size: 14,
                                  color: Colors.white,
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  esWord ? 'Descargar plantilla' : 'Ver guía PDF',
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 11.5,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          );
        }),
      ],
    );
  }
}

/// Modelo de datos de un video tutorial de YouTube
class _VideoTutorial {
  const _VideoTutorial({
    required this.id,
    required this.guia,
    required this.tema,
    required this.descripcion,
  });

  final String id;
  final String guia;
  final String tema;
  final String descripcion;
}

/// Vista de lista de videos tutoriales oficiales
class _ListaVideosTab extends StatelessWidget {
  const _ListaVideosTab({
    required this.intro,
    required this.videos,
    required this.onVerVideo,
  });

  final String intro;
  final List<_VideoTutorial> videos;
  final void Function(String id) onVerVideo;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 32),
      children: [
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          decoration: BoxDecoration(
            color: isDark ? AppColors.darkSurface : const Color(0xFFF8FAFC),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
            ),
          ),
          child: Row(
            children: [
              const Icon(Icons.ondemand_video_rounded, size: 18, color: AppColors.goldHover),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  intro,
                  style: TextStyle(
                    fontSize: 12,
                    color: isDark ? AppColors.darkMuted : const Color(0xFF475569),
                    height: 1.35,
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),
        ...videos.map((vid) {
          final thumbnailUrl = 'https://img.youtube.com/vi/${vid.id}/hqdefault.jpg';

          return PukllaScaleTap(
            onTap: () => onVerVideo(vid.id),
            child: Container(
              margin: const EdgeInsets.only(bottom: 14),
              decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
              clipBehavior: Clip.antiAlias,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Miniatura del video con overlay de Play
                  Stack(
                    alignment: Alignment.center,
                    children: [
                      AspectRatio(
                        aspectRatio: 16 / 9,
                        child: Image.network(
                          thumbnailUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (context, error, stackTrace) => Container(
                            color: const Color(0xFF0F172A),
                            child: const Center(
                              child: Icon(Icons.play_circle_fill_rounded,
                                  size: 48, color: AppColors.gold),
                            ),
                          ),
                        ),
                      ),
                      Container(
                        color: Colors.black.withValues(alpha: 0.28),
                      ),
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: AppColors.gold.withValues(alpha: 0.90),
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.3),
                              blurRadius: 10,
                              offset: const Offset(0, 3),
                            ),
                          ],
                        ),
                        child: const Icon(
                          Icons.play_arrow_rounded,
                          color: Colors.white,
                          size: 30,
                        ),
                      ),
                      Positioned(
                        top: 10,
                        left: 10,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.75),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            vid.guia,
                            style: const TextStyle(
                              color: Color(0xFFFDE68A),
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  Padding(
                    padding: const EdgeInsets.all(14),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          vid.tema,
                          style: TextStyle(
                            fontSize: 14.5,
                            fontWeight: FontWeight.w800,
                            color: PukllaDashboardStyles.textPrimary(context),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          vid.descripcion,
                          style: TextStyle(
                            fontSize: 12,
                            color: PukllaDashboardStyles.textSecondary(context),
                            height: 1.35,
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Row(
                          children: [
                            Icon(Icons.open_in_new_rounded, size: 14, color: AppColors.goldHover),
                            SizedBox(width: 4),
                            Text(
                              'Reproducir en YouTube',
                              style: TextStyle(
                                fontSize: 11.5,
                                fontWeight: FontWeight.w700,
                                color: AppColors.goldHover,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          );
        }),
      ],
    );
  }
}
