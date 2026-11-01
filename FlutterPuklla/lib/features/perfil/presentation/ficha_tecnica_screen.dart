import 'dart:io';

import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_avatar.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../core/widgets/section_header.dart';
import '../../../core/widgets/puklla_preloader.dart';
import '../../../routing/app_drawer.dart';
import '../../home/application/home_providers.dart';
import '../../home/data/curso.dart';
import '../../home/presentation/curso_detalle_sheet.dart';
import '../application/perfil_providers.dart';
import '../data/alumno_perfil.dart';
import '../data/perfil_repository.dart';
import 'carnet_digital_sheet.dart';

class FichaTecnicaScreen extends ConsumerWidget {
  const FichaTecnicaScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final perfil = ref.watch(perfilProvider);

    return Scaffold(
      backgroundColor: PukllaDashboardStyles.backgroundColor(context),
      appBar: AppBar(
        title: const Text('Ficha técnica'),
        actions: const [PukllaAppBarActions()],
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(perfilProvider);
          ref.invalidate(periodoActualProvider);
          ref.invalidate(cursosProvider);
          ref.invalidate(yaMatriculadoProvider);
          await ref.read(perfilProvider.future);
        },
        child: perfil.when(
          data: (data) => _FichaBody(perfil: data),
          loading: () => ListView(
            padding: const EdgeInsets.all(16),
            children: const [
              PukllaSkeletonCard(height: 160),
              SizedBox(height: 16),
              Row(
                children: [
                  Expanded(child: PukllaSkeletonCard(height: 70)),
                  SizedBox(width: 8),
                  Expanded(child: PukllaSkeletonCard(height: 70)),
                  SizedBox(width: 8),
                  Expanded(child: PukllaSkeletonCard(height: 70)),
                ],
              ),
              SizedBox(height: 16),
              PukllaSkeletonCard(height: 180),
              SizedBox(height: 16),
              PukllaSkeletonCard(height: 120),
            ],
          ),
          error: (error, _) => ListView(
            children: [
              Padding(
                padding: const EdgeInsets.all(24),
                child: ErrorState(
                  message: error is ApiException ? error.message : 'No se pudo cargar tu ficha técnica.',
                ),
              ),
            ],
          ),
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
    final cursosAsync = ref.watch(cursosProvider);
    final periodoAsync = ref.watch(periodoActualProvider);
    final matriculadoAsync = ref.watch(yaMatriculadoProvider);

    final totalCursos = cursosAsync.valueOrNull?.length ?? 0;
    final periodoNombre = periodoAsync.valueOrNull?.nombre ?? '2026-I';
    final estaMatriculado = matriculadoAsync.valueOrNull ?? false;

    return ListView(
      padding: EdgeInsets.zero,
      children: [
        _Hero(perfil: perfil),
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ── KPI STATS ROW ──
              Row(
                children: [
                  Expanded(
                    child: _FichaStatCard(
                      icon: Icons.menu_book_rounded,
                      titulo: 'Cursos Ciclo',
                      valor: totalCursos.toString(),
                      color: AppColors.teal,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _FichaStatCard(
                      icon: Icons.calendar_month_rounded,
                      titulo: 'Periodo',
                      valor: periodoNombre,
                      color: AppColors.goldHover,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _FichaStatCard(
                      icon: estaMatriculado ? Icons.verified_user_rounded : Icons.pending_actions_rounded,
                      titulo: 'Matrícula',
                      valor: estaMatriculado ? 'Activa' : 'Pendiente',
                      color: estaMatriculado ? const Color(0xFF10B981) : const Color(0xFFF59E0B),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              _EstadoMatriculaBanner(perfil: perfil),
              const SizedBox(height: 20),

              const SectionHeader(icon: Icons.badge_outlined, title: 'Datos del Estudiante'),
              _DatosPersonalesCard(perfil: perfil),
              const SizedBox(height: 20),

              if (perfil.tutor != null) ...[
                const SectionHeader(icon: Icons.support_agent_outlined, title: 'Mi Tutor Asignado'),
                _TutorAsignadoCard(tutor: perfil.tutor!),
                const SizedBox(height: 20),
              ],

              const SectionHeader(icon: Icons.menu_book_outlined, title: 'Cursos del semestre'),
              const _CursosDelSemestre(),
            ],
          ),
        ),
      ],
    );
  }
}

class _Hero extends StatelessWidget {
  const _Hero({required this.perfil});

  final AlumnoPerfil perfil;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final iniciales = [perfil.nombres, perfil.apellidos]
        .where((s) => s.isNotEmpty)
        .map((s) => s[0].toUpperCase())
        .take(2)
        .join();

    final cicloRaw = perfil.cicloNombre;
    final cicloFormatted = (cicloRaw != null && cicloRaw.isNotEmpty)
        ? (cicloRaw.toLowerCase().startsWith('ciclo') ? cicloRaw : 'Ciclo $cicloRaw')
        : 'Ciclo';

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 24, 20, 24),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: isDark
              ? const [Color(0xFF0F1E36), Color(0xFF1B2A4A)]
              : const [Color(0xFF1B2A4A), Color(0xFF2C3E60)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
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
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: AppColors.gold, width: 2.2),
                ),
                child: PukllaAvatar(
                  fotoUrl: perfil.foto,
                  iniciales: iniciales,
                  size: 58,
                  borderWidth: 0,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      perfil.nombreCompleto,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 18.5,
                        fontWeight: FontWeight.w800,
                        letterSpacing: -0.2,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 5),
                    Wrap(
                      spacing: 6,
                      runSpacing: 4,
                      children: [
                        if (perfil.programaNombre != null)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: AppColors.gold.withValues(alpha: 0.22),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              perfil.programaNombre!,
                              style: const TextStyle(
                                color: AppColors.gold,
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.16),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            cicloFormatted,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          // Pill con DNI copiable
          InkWell(
            onTap: () {
              Clipboard.setData(ClipboardData(text: perfil.dni));
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('DNI ${perfil.dni} copiado al portapapeles'),
                  duration: const Duration(seconds: 2),
                  behavior: SnackBarBehavior.floating,
                ),
              );
            },
            borderRadius: BorderRadius.circular(10),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.white.withValues(alpha: 0.14)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.fingerprint_rounded, size: 16, color: Color(0xFFFDE68A)),
                  const SizedBox(width: 6),
                  Text(
                    'DNI: ${perfil.dni}',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 0.4,
                    ),
                  ),
                  const SizedBox(width: 6),
                  const Icon(Icons.copy_rounded, size: 12, color: Color(0xFFCBD5E1)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _FichaStatCard extends StatelessWidget {
  const _FichaStatCard({
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
              fontSize: 16,
              fontWeight: FontWeight.w900,
              color: PukllaDashboardStyles.textPrimary(context),
            ),
          ),
        ],
      ),
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
                  ? Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: _MatriculadoBadge(periodoNombre: periodo.nombre),
                    )
                  : (periodo.formularioHabilitado
                      ? Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: _MatriculaPendienteAviso(periodoNombre: periodo.nombre),
                        )
                      : const SizedBox.shrink()),
              loading: () => const SizedBox.shrink(),
              error: (_, _) => const SizedBox.shrink(),
            );
          },
          loading: () => const SizedBox.shrink(),
          error: (_, _) => const SizedBox.shrink(),
        ),
        _AccionesFicha(perfil: perfil),
      ],
    );
  }
}

class _AccionesFicha extends StatefulWidget {
  const _AccionesFicha({required this.perfil});

  final AlumnoPerfil perfil;

  @override
  State<_AccionesFicha> createState() => _AccionesFichaState();
}

class _AccionesFichaState extends State<_AccionesFicha> {
  bool _descargandoPdf = false;

  Future<void> _descargarFicha(WidgetRef ref) async {
    final messenger = ScaffoldMessenger.of(context);
    setState(() => _descargandoPdf = true);
    try {
      final bytes = await ref.read(perfilRepositoryProvider).descargarFichaPdf();

      if (kIsWeb) {
        final uri = Uri.dataFromBytes(bytes, mimeType: 'application/pdf');
        await launchUrl(uri);
      } else {
        final file = File('${Directory.systemTemp.path}/ficha_${widget.perfil.dni}.pdf');
        await file.writeAsBytes(bytes);
        final abierto = await launchUrl(Uri.file(file.path), mode: LaunchMode.externalApplication);
        if (!abierto) {
          messenger.showSnackBar(SnackBar(
            content: Text('Ficha guardada en ${file.path}'),
            behavior: SnackBarBehavior.floating,
          ));
        }
      }
    } on ApiException catch (e) {
      messenger.showSnackBar(SnackBar(
        content: Text(e.message),
        backgroundColor: AppColors.danger,
        behavior: SnackBarBehavior.floating,
      ));
    } catch (e) {
      messenger.showSnackBar(SnackBar(
        content: Text('Error al obtener la ficha PDF: $e'),
        backgroundColor: AppColors.danger,
        behavior: SnackBarBehavior.floating,
      ));
    } finally {
      if (mounted) setState(() => _descargandoPdf = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer(
      builder: (context, ref, _) {
        return Column(
          children: [
            Row(
              children: [
                Expanded(
                  child: Container(
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: [
                        BoxShadow(
                          color: AppColors.gold.withValues(alpha: 0.28),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: FilledButton.icon(
                      onPressed: () => context.push('/matricula/formulario'),
                      icon: const Icon(Icons.edit_note_rounded, size: 20),
                      label: const Text(
                        'Actualizar mis datos',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          letterSpacing: 0.1,
                        ),
                      ),
                      style: FilledButton.styleFrom(
                        backgroundColor: AppColors.gold,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        elevation: 0,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Container(
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: [
                        BoxShadow(
                          color: AppColors.teal.withValues(alpha: 0.22),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: FilledButton.icon(
                      onPressed: _descargandoPdf ? null : () => _descargarFicha(ref),
                      icon: _descargandoPdf
                          ? const SizedBox(
                              width: 16,
                              height: 16,
                              child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                            )
                          : const Icon(Icons.picture_as_pdf_rounded, size: 19),
                      label: Text(
                        _descargandoPdf ? 'Descargando...' : 'Ficha oficial (PDF)',
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                          letterSpacing: 0.1,
                        ),
                      ),
                      style: FilledButton.styleFrom(
                        backgroundColor: AppColors.teal,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        elevation: 0,
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Container(
              width: double.infinity,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(14),
                gradient: const LinearGradient(
                  colors: [Color(0xFFB96328), Color(0xFFA05220)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFFB96328).withValues(alpha: 0.28),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: FilledButton.icon(
                onPressed: () => CarnetDigitalSheet.show(context, widget.perfil),
                icon: const Icon(Icons.badge_rounded, size: 20, color: Colors.white),
                label: const Text(
                  'Mi Carnet Digital (ID Estudiante & Biblioteca)',
                  style: TextStyle(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                    letterSpacing: 0.2,
                  ),
                ),
                style: FilledButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  shadowColor: Colors.transparent,
                  padding: const EdgeInsets.symmetric(vertical: 13),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
              ),
            ),
          ],
        );
      },
    );
  }
}

class _MatriculadoBadge extends StatelessWidget {
  const _MatriculadoBadge({required this.periodoNombre});

  final String periodoNombre;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF064E3B) : AppColors.successBg,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: isDark ? const Color(0xFF059669) : Colors.transparent),
      ),
      child: Row(
        children: [
          Icon(Icons.check_circle, color: isDark ? const Color(0xFF34D399) : AppColors.success, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              'Matriculado · Periodo $periodoNombre',
              style: TextStyle(
                color: isDark ? const Color(0xFF34D399) : AppColors.success,
                fontWeight: FontWeight.w800,
                fontSize: 13.5,
              ),
            ),
          ),
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
    final isDark = PukllaDashboardStyles.isDark(context);
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF451A03) : AppColors.warningBg,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: isDark ? const Color(0xFFD97706) : Colors.transparent),
      ),
      child: Row(
        children: [
          Icon(Icons.error_outline, color: isDark ? const Color(0xFFFBBF24) : AppColors.warning),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              'El período $periodoNombre está abierto. Aún no completas tu ficha de matrícula.',
              style: TextStyle(
                color: isDark ? const Color(0xFFFDE68A) : AppColors.warning,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          const SizedBox(width: 8),
          FilledButton(
            onPressed: () => context.push('/matricula/formulario'),
            child: const Text('Completar'),
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
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _DatoRow(icon: Icons.fingerprint_rounded, label: 'DNI / Código', value: perfil.dni, copyable: true),
          _DatoRow(icon: Icons.alternate_email_rounded, label: 'Correo', value: perfil.email, copyable: true),
          _DatoRow(icon: Icons.phone_outlined, label: 'Teléfono', value: perfil.numero, copyable: true),
          _DatoRow(icon: Icons.contact_phone_outlined, label: 'Emergencia', value: perfil.numeroReferencia),
          _DatoRow(icon: Icons.home_outlined, label: 'Domicilio', value: perfil.domicilio, isLast: true),
        ],
      ),
    );
  }
}

class _DatoRow extends StatelessWidget {
  const _DatoRow({
    required this.icon,
    required this.label,
    required this.value,
    this.copyable = false,
    this.isLast = false,
  });

  final IconData icon;
  final String label;
  final String value;
  final bool copyable;
  final bool isLast;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: isLast ? 0 : 14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 18, color: PukllaDashboardStyles.textSecondary(context)),
          const SizedBox(width: 12),
          SizedBox(
            width: 90,
            child: Text(
              label,
              style: TextStyle(color: PukllaDashboardStyles.textSecondary(context), fontSize: 13),
            ),
          ),
          Expanded(
            child: Text(
              value.isNotEmpty ? value : '—',
              style: TextStyle(
                fontWeight: FontWeight.w700,
                color: PukllaDashboardStyles.textPrimary(context),
                fontSize: 13.5,
              ),
            ),
          ),
          if (copyable && value.isNotEmpty)
            GestureDetector(
              onTap: () {
                Clipboard.setData(ClipboardData(text: value));
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text('$label copiado al portapapeles'),
                    duration: const Duration(seconds: 2),
                    behavior: SnackBarBehavior.floating,
                  ),
                );
              },
              child: Padding(
                padding: const EdgeInsets.only(left: 6),
                child: Icon(Icons.copy_rounded, size: 14, color: PukllaDashboardStyles.textSecondary(context)),
              ),
            ),
        ],
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
          ? Padding(
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: Text(
                'No tienes cursos en el periodo actual.',
                style: TextStyle(color: PukllaDashboardStyles.textSecondary(context)),
              ),
            )
          : Column(children: [for (final curso in list) _CursoDelSemestreTile(curso: curso)]),
      loading: () => const Padding(
        padding: EdgeInsets.symmetric(vertical: 24),
        child: PukllaPreloader.inline(mensaje: 'Cargando asignaturas...'),
      ),
      error: (error, _) => ErrorState(
        message: error is ApiException ? error.message : 'No se pudieron cargar los cursos.',
        onRetry: () => ref.invalidate(cursosProvider),
      ),
    );
  }
}

class _CursoDelSemestreTile extends StatelessWidget {
  const _CursoDelSemestreTile({required this.curso});

  final Curso curso;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 14),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(14),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(width: 4, color: AppColors.teal),
              Expanded(
                child: ListTile(
                  onTap: () => CursoDetalleSheet.show(context, curso),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                  leading: Container(
                    width: 38,
                    height: 38,
                    decoration: BoxDecoration(
                      color: AppColors.teal.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.menu_book_rounded, size: 18, color: AppColors.teal),
                  ),
                  title: Text(
                    curso.nombre,
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      color: PukllaDashboardStyles.textPrimary(context),
                      fontSize: 14,
                    ),
                  ),
                  subtitle: Text(
                    '${curso.cc} · ${curso.horas} h · ${curso.creditos} cr.',
                    style: TextStyle(
                      color: PukllaDashboardStyles.textSecondary(context),
                      fontSize: 12,
                    ),
                  ),
                  trailing: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (curso.classroom != null)
                        IconButton(
                          icon: Icon(Icons.open_in_new_rounded,
                              color: PukllaDashboardStyles.textSecondary(context), size: 18),
                          tooltip: 'Abrir classroom',
                          onPressed: () =>
                              launchUrl(Uri.parse(curso.classroom!), mode: LaunchMode.externalApplication),
                        ),
                      const Icon(Icons.chevron_right_rounded, size: 20, color: Colors.grey),
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

class _TutorAsignadoCard extends StatelessWidget {
  const _TutorAsignadoCard({required this.tutor});

  final TutorInfo tutor;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(width: 4, color: AppColors.gold),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Row(
                    children: [
                      Container(
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: AppColors.gold, width: 2),
                        ),
                        child: CircleAvatar(
                          radius: 22,
                          backgroundColor: AppColors.gold.withValues(alpha: 0.15),
                          backgroundImage: tutor.foto != null ? NetworkImage(tutor.foto!) : null,
                          child: tutor.foto == null
                              ? const Icon(Icons.person_rounded, color: AppColors.goldHover, size: 24)
                              : null,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              tutor.nombre,
                              style: TextStyle(
                                fontSize: 14.5,
                                fontWeight: FontWeight.w800,
                                color: PukllaDashboardStyles.textPrimary(context),
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'Tutor(a) pedagógico de ciclo',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w500,
                                color: PukllaDashboardStyles.textSecondary(context),
                              ),
                            ),
                            if (tutor.email != null) ...[
                              const SizedBox(height: 2),
                              Text(
                                tutor.email!,
                                style: TextStyle(
                                  fontSize: 11.5,
                                  color: isDark ? AppColors.goldHover : AppColors.teal,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                      if (tutor.email != null)
                        IconButton(
                          icon: const Icon(Icons.mail_outline_rounded, size: 20),
                          tooltip: 'Enviar correo al tutor',
                          color: AppColors.teal,
                          onPressed: () => launchUrl(Uri.parse('mailto:${tutor.email}'),
                              mode: LaunchMode.externalApplication),
                        ),
                      if (tutor.telefono != null)
                        IconButton(
                          icon: const Icon(Icons.phone_outlined, size: 20),
                          tooltip: 'Llamar al tutor',
                          color: const Color(0xFF10B981),
                          onPressed: () => launchUrl(Uri.parse('tel:${tutor.telefono}'),
                              mode: LaunchMode.externalApplication),
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
