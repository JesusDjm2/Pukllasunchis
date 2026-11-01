import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../routing/app_drawer.dart';
import '../../notificaciones/presentation/notification_bell_action.dart';

class FormatosBibliotecaScreen extends ConsumerWidget {
  const FormatosBibliotecaScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Guías, Tesis & Biblioteca'),
        actions: const [PukllaAppBarActions()],
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: ListView(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
        children: [
          // Banner Biblioteca
          InkWell(
            onTap: () {
              final uri = Uri.parse('https://sites.google.com/pukllavirtual.edu.pe/bibliotecaeesppuklla/inicio');
              launchUrl(uri, mode: LaunchMode.externalApplication);
            },
            borderRadius: BorderRadius.circular(20),
            child: Container(
              padding: const EdgeInsets.all(22),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF854D0E), Color(0xFFCA8A04)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF854D0E).withValues(alpha: 0.2),
                    blurRadius: 18,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: const Icon(Icons.local_library_outlined, color: Colors.white, size: 32),
                  ),
                  const SizedBox(width: 16),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Biblioteca Virtual',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        SizedBox(height: 4),
                        Text(
                          'Accede a miles de libros digitales, revistas indexadas y repositorios pedagógicos.',
                          style: TextStyle(
                            color: Color(0xFFFEF08A),
                            fontSize: 12.5,
                            height: 1.35,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Icon(Icons.open_in_new, color: Colors.white, size: 20),
                ],
              ),
            ),
          ),
          const SizedBox(height: 26),
          const Text(
            'Formatos Oficiales y Guías de Titulación',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w700,
              color: Color(0xFF1E293B),
            ),
          ),
          const SizedBox(height: 12),
          _RecursoItem(
            icon: Icons.description_outlined,
            color: const Color(0xFF2563EB),
            titulo: 'Guía de Trabajo de Investigación (TI)',
            subtitulo: 'Estructura formal, formato APA 7ma edición y cronograma de entregas.',
            formato: 'PDF',
          ),
          const SizedBox(height: 12),
          _RecursoItem(
            icon: Icons.school_outlined,
            color: const Color(0xFF059669),
            titulo: 'Reglamento de Grados y Tesis',
            subtitulo: 'Procedimientos de sustentación, requisitos y conformación de jurados.',
            formato: 'PDF',
          ),
          const SizedBox(height: 12),
          _RecursoItem(
            icon: Icons.assignment_turned_in_outlined,
            color: const Color(0xFF7C3AED),
            titulo: 'Plantilla de Proyecto Integrador Taq\'e',
            subtitulo: 'Matriz de consistencia, plan de acción y rúbrica de evaluación.',
            formato: 'DOCX',
          ),
        ],
      ),
    );
  }
}

class _RecursoItem extends StatelessWidget {
  const _RecursoItem({
    required this.icon,
    required this.color,
    required this.titulo,
    required this.subtitulo,
    required this.formato,
  });

  final IconData icon;
  final Color color;
  final String titulo;
  final String subtitulo;
  final String formato;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E293B).withValues(alpha: 0.04),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
        border: Border.all(color: Colors.black.withValues(alpha: 0.04)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Icon(icon, color: color, size: 24),
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
                        titulo,
                        style: const TextStyle(
                          fontSize: 14.5,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF1E293B),
                        ),
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: color.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        formato,
                        style: TextStyle(
                          color: color,
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                Text(
                  subtitulo,
                  style: TextStyle(
                    fontSize: 12.5,
                    color: Colors.grey.shade600,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
