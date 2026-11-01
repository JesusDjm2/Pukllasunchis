import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../data/curso.dart';

/// Modal bottom sheet con el detalle completo de la asignatura:
/// - Ficha curricular (créditos, horas, componente curricular CC)
/// - Calificaciones vigentes en el curso (Parcial 1, Parcial 2, Promedio)
/// - Sumilla oficial (depurada de HTML)
/// - Plana docente con contacto directo (email / teléfono)
/// - Competencias y capacidades curriculares (parseadas limpiamente)
/// - Acceso al Sílabo (PDF) y Google Classroom (con clave copiable)
class CursoDetalleSheet extends StatelessWidget {
  const CursoDetalleSheet({super.key, required this.curso});

  final Curso curso;

  static Future<void> show(BuildContext context, Curso curso) {
    return showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => CursoDetalleSheet(curso: curso),
    );
  }

  void _abrirUrl(BuildContext context, String url) async {
    final uri = Uri.parse(url);
    try {
      final ok = await launchUrl(uri, mode: LaunchMode.externalApplication);
      if (!ok && context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('No se pudo abrir el enlace.')),
        );
      }
    } catch (_) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Enlace inválido o no disponible.')),
        );
      }
    }
  }

  void _copiarClave(BuildContext context, String clave) {
    Clipboard.setData(ClipboardData(text: clave));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Código copiado al portapapeles: $clave'),
        duration: const Duration(seconds: 2),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final size = MediaQuery.of(context).size;

    return Container(
      constraints: BoxConstraints(maxHeight: size.height * 0.90),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(26)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.5 : 0.2),
            blurRadius: 20,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Drag handle
          Container(
            width: 44,
            height: 4.5,
            margin: const EdgeInsets.only(top: 12, bottom: 8),
            decoration: BoxDecoration(
              color: isDark ? Colors.white24 : const Color(0xFFCBD5E1),
              borderRadius: BorderRadius.circular(3),
            ),
          ),

          // Header con título y botón de cierre
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 4, 12, 10),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Detalle de la Asignatura',
                        style: TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w600,
                          letterSpacing: 0.3,
                          color: isDark ? AppColors.goldHover : AppColors.teal,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        curso.nombre,
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w800,
                          color: PukllaDashboardStyles.textPrimary(context),
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close_rounded),
                  color: PukllaDashboardStyles.textSecondary(context),
                  onPressed: () => Navigator.of(context).pop(),
                ),
              ],
            ),
          ),

          const Divider(height: 1),

          // Contenido scrollable
          Flexible(
            child: ListView(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
              children: [
                // ── KPI ROW DE CRÉDITOS, HORAS Y CC ──
                _buildMetricsRow(context, isDark),
                const SizedBox(height: 16),

                // ── MIS CALIFICACIONES EN ESTE CURSO ──
                _buildCalificacionesCard(context, isDark),
                const SizedBox(height: 16),

                // ── ACCESOS RÁPIDOS: SÍLABO Y CLASSROOM ──
                _buildRecursosCard(context, isDark),
                const SizedBox(height: 16),

                // ── SUMILLA ──
                _buildSumillaCard(context, isDark),
                const SizedBox(height: 16),

                // ── PLANA DOCENTE ──
                _buildDocentesCard(context, isDark),
                const SizedBox(height: 16),

                // ── COMPETENCIAS Y CAPACIDADES (SIN HTML) ──
                _buildCompetenciasCard(context, isDark),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMetricsRow(BuildContext context, bool isDark) {
    return Row(
      children: [
        Expanded(
          child: _buildMetricItem(
            context,
            isDark: isDark,
            icon: Icons.layers_outlined,
            title: 'Componente (CC)',
            value: curso.cc.isNotEmpty ? curso.cc : 'General',
            iconColor: AppColors.teal,
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: _buildMetricItem(
            context,
            isDark: isDark,
            icon: Icons.workspace_premium_outlined,
            title: 'Créditos',
            value: '${curso.creditos} cr.',
            iconColor: AppColors.goldHover,
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: _buildMetricItem(
            context,
            isDark: isDark,
            icon: Icons.schedule_outlined,
            title: 'Horas sem.',
            value: '${curso.horas} h',
            iconColor: const Color(0xFF6366F1),
          ),
        ),
      ],
    );
  }

  Widget _buildMetricItem(
    BuildContext context, {
    required bool isDark,
    required IconData icon,
    required String title,
    required String value,
    required Color iconColor,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkCard : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 14, color: iconColor),
              const SizedBox(width: 4),
              Expanded(
                child: Text(
                  title,
                  style: TextStyle(
                    fontSize: 10.5,
                    color: PukllaDashboardStyles.textSecondary(context),
                    fontWeight: FontWeight.w500,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w800,
              color: PukllaDashboardStyles.textPrimary(context),
            ),
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  // ── SECCIÓN DE CALIFICACIONES EN ESTE CURSO ──
  Widget _buildCalificacionesCard(BuildContext context, bool isDark) {
    final calif = curso.calificaciones;

    final p1 = calif?.parcial1;
    final p2 = calif?.parcial2;
    final prom = calif?.promedio;
    final obs = calif?.observaciones;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkCard : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.grade_rounded, size: 18, color: Color(0xFF059669)),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Mis Calificaciones en este Curso',
                  style: TextStyle(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w700,
                    color: PukllaDashboardStyles.textPrimary(context),
                  ),
                ),
              ),
              InkWell(
                onTap: () {
                  Navigator.of(context).pop();
                  context.push('/calificaciones');
                },
                child: const Row(
                  children: [
                    Text(
                      'Récord',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: AppColors.teal,
                      ),
                    ),
                    Icon(Icons.arrow_forward_ios_rounded, size: 11, color: AppColors.teal),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _buildNotaChip(
                  context,
                  label: 'Parcial 1',
                  nota: p1,
                  isDark: isDark,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _buildNotaChip(
                  context,
                  label: 'Parcial 2',
                  nota: p2,
                  isDark: isDark,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _buildNotaChip(
                  context,
                  label: 'Promedio',
                  nota: prom,
                  isPromedio: true,
                  isDark: isDark,
                ),
              ),
            ],
          ),
          if (obs != null && obs.trim().isNotEmpty) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
              decoration: BoxDecoration(
                color: isDark ? Colors.black26 : const Color(0xFFFEF3C7),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: isDark ? const Color(0xFFB45309) : const Color(0xFFFDE68A),
                ),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.info_outline_rounded, size: 14, color: Color(0xFFD97706)),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      obs,
                      style: TextStyle(
                        fontSize: 11.5,
                        color: isDark ? const Color(0xFFFCD34D) : const Color(0xFF92400E),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildNotaChip(
    BuildContext context, {
    required String label,
    required String? nota,
    bool isPromedio = false,
    required bool isDark,
  }) {
    final tieneNota = nota != null && nota.trim().isNotEmpty && nota != '—';
    final notaNum = tieneNota ? double.tryParse(nota.trim()) : null;
    final esAprobado = notaNum != null && notaNum >= 11;
    final esDesaprobado = notaNum != null && notaNum < 11;

    Color badgeColor;
    Color textColor;
    if (tieneNota) {
      if (esAprobado) {
        badgeColor = isDark ? const Color(0xFF064E3B) : const Color(0xFFD1FAE5);
        textColor = isDark ? const Color(0xFF34D399) : const Color(0xFF065F46);
      } else if (esDesaprobado) {
        badgeColor = isDark ? const Color(0xFF7F1D1D) : const Color(0xFFFEE2E2);
        textColor = isDark ? const Color(0xFFF87171) : const Color(0xFF991B1B);
      } else {
        badgeColor = isDark ? const Color(0xFF78350F) : const Color(0xFFFEF3C7);
        textColor = isDark ? const Color(0xFFFBBF24) : const Color(0xFF92400E);
      }
    } else {
      badgeColor = isDark ? AppColors.darkSurface : const Color(0xFFE2E8F0);
      textColor = PukllaDashboardStyles.textSecondary(context);
    }

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 6),
      decoration: BoxDecoration(
        color: badgeColor,
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w600,
              color: textColor.withValues(alpha: 0.85),
            ),
          ),
          const SizedBox(height: 2),
          Text(
            tieneNota ? nota! : '—',
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w900,
              color: textColor,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRecursosCard(BuildContext context, bool isDark) {
    final tieneClassroom = curso.classroom != null && curso.classroom!.isNotEmpty;
    final tieneSilabo = curso.silaboUrl != null && curso.silaboUrl!.isNotEmpty;
    final tieneClave = curso.clave != null && curso.clave!.isNotEmpty;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkCard : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.link_rounded, size: 18, color: AppColors.teal),
              const SizedBox(width: 8),
              Text(
                'Recursos y Plataformas Digitales',
                style: TextStyle(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  color: PukllaDashboardStyles.textPrimary(context),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              // Botón Sílabo
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: tieneSilabo ? () => _abrirUrl(context, curso.silaboUrl!) : null,
                  icon: const Icon(Icons.picture_as_pdf_outlined, size: 16),
                  label: Text(tieneSilabo ? 'Ver Sílabo' : 'Sin sílabo'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.teal,
                    side: BorderSide(
                      color: tieneSilabo
                          ? (isDark ? AppColors.tealDark : AppColors.teal)
                          : Colors.grey.withValues(alpha: 0.3),
                    ),
                    padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              // Botón Classroom
              Expanded(
                child: FilledButton.icon(
                  onPressed: tieneClassroom ? () => _abrirUrl(context, curso.classroom!) : null,
                  icon: const Icon(Icons.open_in_new_rounded, size: 16),
                  label: const Text('Classroom'),
                  style: FilledButton.styleFrom(
                    backgroundColor: AppColors.teal,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
            ],
          ),
          if (tieneClave) ...[
            const SizedBox(height: 10),
            InkWell(
              onTap: () => _copiarClave(context, curso.clave!),
              borderRadius: BorderRadius.circular(8),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
                decoration: BoxDecoration(
                  color: isDark ? Colors.black26 : const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: const Color(0xFFBFDBFE)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.key_rounded, size: 14, color: Color(0xFF2563EB)),
                    const SizedBox(width: 6),
                    Text(
                      'Código de Classroom: ',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: isDark ? Colors.white70 : const Color(0xFF1E3A8A),
                      ),
                    ),
                    SelectableText(
                      curso.clave!,
                      style: const TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w800,
                        fontFamily: 'monospace',
                        color: Color(0xFF2563EB),
                      ),
                    ),
                    const Spacer(),
                    const Icon(Icons.copy_rounded, size: 14, color: Color(0xFF2563EB)),
                  ],
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildSumillaCard(BuildContext context, bool isDark) {
    final sumillaLimpia = _cleanHtml(curso.sumilla);
    final tieneSumilla = sumillaLimpia.isNotEmpty;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkCard : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.menu_book_outlined, size: 18, color: AppColors.goldHover),
              const SizedBox(width: 8),
              Text(
                'Sumilla Oficial',
                style: TextStyle(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  color: PukllaDashboardStyles.textPrimary(context),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            tieneSumilla
                ? sumillaLimpia
                : 'La sumilla oficial se encuentra en proceso de validación curricular.',
            style: TextStyle(
              fontSize: 13,
              height: 1.45,
              color: tieneSumilla
                  ? PukllaDashboardStyles.textSecondary(context)
                  : Colors.grey.withValues(alpha: 0.7),
              fontStyle: tieneSumilla ? FontStyle.normal : FontStyle.italic,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDocentesCard(BuildContext context, bool isDark) {
    final docentes = curso.docentes;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkCard : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.school_outlined, size: 18, color: Color(0xFF0EA5E9)),
              const SizedBox(width: 8),
              Text(
                'Docente(s) a Cargo',
                style: TextStyle(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  color: PukllaDashboardStyles.textPrimary(context),
                ),
              ),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFF0EA5E9).withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  '${docentes.length} asignado${docentes.length == 1 ? '' : 's'}',
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF0284C7),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          if (docentes.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: Text(
                'Docente pendiente de asignación para este semestre.',
                style: TextStyle(
                  fontSize: 12.5,
                  fontStyle: FontStyle.italic,
                  color: PukllaDashboardStyles.textSecondary(context),
                ),
              ),
            )
          else
            ...docentes.map((d) => _buildDocenteItem(context, d, isDark)),
        ],
      ),
    );
  }

  Widget _buildDocenteItem(BuildContext context, DocenteCurso docente, bool isDark) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
        ),
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 20,
            backgroundColor: AppColors.teal.withValues(alpha: 0.15),
            backgroundImage: docente.fotoUrl != null ? NetworkImage(docente.fotoUrl!) : null,
            child: docente.fotoUrl == null
                ? const Icon(Icons.person_outline_rounded, color: AppColors.teal, size: 20)
                : null,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  docente.nombre,
                  style: TextStyle(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w700,
                    color: PukllaDashboardStyles.textPrimary(context),
                  ),
                ),
                if (docente.email != null) ...[
                  const SizedBox(height: 2),
                  Text(
                    docente.email!,
                    style: TextStyle(
                      fontSize: 11.5,
                      color: PukllaDashboardStyles.textSecondary(context),
                    ),
                  ),
                ],
              ],
            ),
          ),
          if (docente.email != null)
            IconButton(
              icon: const Icon(Icons.mail_outline_rounded, size: 18),
              tooltip: 'Enviar correo',
              color: AppColors.teal,
              onPressed: () => _abrirUrl(context, 'mailto:${docente.email}'),
            ),
          if (docente.telefono != null)
            IconButton(
              icon: const Icon(Icons.phone_outlined, size: 18),
              tooltip: 'Llamar',
              color: const Color(0xFF10B981),
              onPressed: () => _abrirUrl(context, 'tel:${docente.telefono}'),
            ),
        ],
      ),
    );
  }

  // ── SECCIÓN DE COMPETENCIAS (TOTALMENTE DEPURA DE HTML) ──
  Widget _buildCompetenciasCard(BuildContext context, bool isDark) {
    final competencias = curso.competencias;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkCard : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.stars_outlined, size: 18, color: AppColors.gold),
              const SizedBox(width: 8),
              Text(
                'Competencias y Capacidades',
                style: TextStyle(
                  fontSize: 13.5,
                  fontWeight: FontWeight.w700,
                  color: PukllaDashboardStyles.textPrimary(context),
                ),
              ),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: AppColors.gold.withValues(alpha: 0.14),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  '${competencias.length} desarrollo${competencias.length == 1 ? '' : 's'}',
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFFD97706),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          if (competencias.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: Text(
                'Competencias asociadas en estructuración según el currículo nacional.',
                style: TextStyle(
                  fontSize: 12.5,
                  fontStyle: FontStyle.italic,
                  color: PukllaDashboardStyles.textSecondary(context),
                ),
              ),
            )
          else
            ...competencias.asMap().entries.map((entry) {
              final index = entry.key + 1;
              final comp = entry.value;

              final nombreLimpio = _cleanHtml(comp.nombre);
              final descLimpia = _cleanHtml(comp.descripcion);
              final capacidadesList = _extractCapacidadesList(comp.capacidades);

              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: isDark ? AppColors.darkSurface : Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          width: 22,
                          height: 22,
                          alignment: Alignment.center,
                          decoration: BoxDecoration(
                            color: AppColors.gold.withValues(alpha: 0.18),
                            shape: BoxShape.circle,
                          ),
                          child: Text(
                            '$index',
                            style: const TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w800,
                              color: AppColors.goldHover,
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            nombreLimpio,
                            style: TextStyle(
                              fontSize: 13.5,
                              fontWeight: FontWeight.w700,
                              color: PukllaDashboardStyles.textPrimary(context),
                            ),
                          ),
                        ),
                      ],
                    ),
                    if (descLimpia.isNotEmpty) ...[
                      const SizedBox(height: 8),
                      Text(
                        descLimpia,
                        style: TextStyle(
                          fontSize: 12.5,
                          height: 1.4,
                          color: PukllaDashboardStyles.textSecondary(context),
                        ),
                      ),
                    ],
                    if (capacidadesList.isNotEmpty) ...[
                      const SizedBox(height: 10),
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: isDark ? Colors.black26 : const Color(0xFFF1F5F9),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
                          ),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                const Icon(Icons.check_circle_outline_rounded,
                                    size: 14, color: AppColors.teal),
                                const SizedBox(width: 6),
                                Text(
                                  'Capacidades:',
                                  style: TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w700,
                                    color: isDark ? AppColors.goldHover : AppColors.teal,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            ...capacidadesList.map((cap) => Padding(
                                  padding: const EdgeInsets.only(bottom: 6),
                                  child: Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Container(
                                        width: 5,
                                        height: 5,
                                        margin: const EdgeInsets.only(top: 6, right: 8),
                                        decoration: const BoxDecoration(
                                          color: AppColors.goldHover,
                                          shape: BoxShape.circle,
                                        ),
                                      ),
                                      Expanded(
                                        child: Text(
                                          cap,
                                          style: TextStyle(
                                            fontSize: 12,
                                            height: 1.35,
                                            color: isDark
                                                ? Colors.white70
                                                : const Color(0xFF334155),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                )),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }
}

// ── UTILIDADES DE LIMPIEZA DE HTML Y ENTIDADES ──
String _cleanHtml(String? text) {
  if (text == null || text.trim().isEmpty) return '';
  var result = text;

  // Reemplazar saltos y párrafos por saltos de línea legibles
  result = result.replaceAll(RegExp(r'<br\s*/?>', caseSensitive: false), '\n');
  result = result.replaceAll(RegExp(r'</p>', caseSensitive: false), '\n');
  result = result.replaceAll(RegExp(r'</li>', caseSensitive: false), '\n');
  result = result.replaceAll(RegExp(r'</div>', caseSensitive: false), '\n');

  // Eliminar cualquier etiqueta HTML remanente
  result = result.replaceAll(RegExp(r'<[^>]*>'), '');

  // Decodificar entidades HTML comunes
  result = _decodeHtmlEntities(result);

  // Normalizar saltos de línea y espacios en blanco
  result = result.replaceAll(RegExp(r'\r\n|\r'), '\n');
  result = result.replaceAll(RegExp(r'\n{3,}'), '\n\n');
  return result.trim();
}

List<String> _extractCapacidadesList(String? raw) {
  if (raw == null || raw.trim().isEmpty) return const [];

  // Si contiene <li>, extraer los elementos individuales
  final liRegex = RegExp(r'<li[^>]*>(.*?)</li>', caseSensitive: false, dotAll: true);
  final matches = liRegex.allMatches(raw);

  if (matches.isNotEmpty) {
    return matches
        .map((m) => _cleanHtml(m.group(1)))
        .where((s) => s.isNotEmpty)
        .toList();
  }

  // Si no contiene <li>, limpiar HTML y separar por saltos de línea o viñetas
  final cleaned = _cleanHtml(raw);
  final lines = cleaned
      .split('\n')
      .map((l) => l.replaceFirst(RegExp(r'^[-•*]\s*'), '').trim())
      .where((l) => l.isNotEmpty)
      .toList();

  return lines.isNotEmpty ? lines : [cleaned];
}

String _decodeHtmlEntities(String text) {
  return text
      .replaceAll('&aacute;', 'á')
      .replaceAll('&eacute;', 'é')
      .replaceAll('&iacute;', 'í')
      .replaceAll('&oacute;', 'ó')
      .replaceAll('&uacute;', 'ú')
      .replaceAll('&Aacute;', 'Á')
      .replaceAll('&Eacute;', 'É')
      .replaceAll('&Iacute;', 'Í')
      .replaceAll('&Oacute;', 'Ó')
      .replaceAll('&Uacute;', 'Ú')
      .replaceAll('&ntilde;', 'ñ')
      .replaceAll('&Ntilde;', 'Ñ')
      .replaceAll('&nbsp;', ' ')
      .replaceAll('&amp;', '&')
      .replaceAll('&quot;', '"')
      .replaceAll('&#39;', "'")
      .replaceAll('&apos;', "'")
      .replaceAll('&lt;', '<')
      .replaceAll('&gt;', '>')
      .replaceAll('&deg;', '°')
      .replaceAll('&bull;', '•')
      .replaceAll('&middot;', '·');
}
