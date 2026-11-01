import 'dart:io';

import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_avatar.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../core/widgets/section_header.dart';
import '../../../routing/app_drawer.dart';
import '../../home/application/home_providers.dart';
import '../../home/data/curso.dart';
import '../../notificaciones/presentation/notification_bell_action.dart';
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

    return Scaffold(
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
          loading: () => const Center(child: CircularProgressIndicator()),
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
    return ListView(
      padding: EdgeInsets.zero,
      children: [
        _Hero(perfil: perfil),
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 20, 16, 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _EstadoMatriculaBanner(perfil: perfil),
              const SizedBox(height: 20),
              const SectionHeader(icon: Icons.badge_outlined, title: 'Mis datos'),
              _DatosPersonalesCard(perfil: perfil),
              const SizedBox(height: 20),
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
    final iniciales = [perfil.nombres, perfil.apellidos]
        .where((s) => s.isNotEmpty)
        .map((s) => s[0].toUpperCase())
        .take(2)
        .join();

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 24, 20, 32),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [AppColors.teal, AppColors.tealDark],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Row(
        children: [
          PukllaAvatar(
            fotoUrl: perfil.foto,
            iniciales: iniciales,
            size: 52,
            borderWidth: 2,
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  perfil.nombreCompleto,
                  style: const TextStyle(color: Colors.white, fontSize: 19, fontWeight: FontWeight.w700),
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 2),
                Text(
                  [perfil.programaNombre, perfil.cicloNombre].whereType<String>().join(' · '),
                  style: TextStyle(color: Colors.white.withValues(alpha: 0.75), fontSize: 13),
                  overflow: TextOverflow.ellipsis,
                ),
              ],
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
        return Row(
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
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: AppColors.successBg,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Row(
        children: [
          const Icon(Icons.check_circle, color: AppColors.success, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              'Matriculado · $periodoNombre',
              style: const TextStyle(color: AppColors.success, fontWeight: FontWeight.w600),
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
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.warningBg,
        borderRadius: BorderRadius.circular(14),
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
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _DatoRow(icon: Icons.badge_outlined, label: 'DNI', value: perfil.dni),
            _DatoRow(icon: Icons.mail_outline, label: 'Correo', value: perfil.email),
            _DatoRow(icon: Icons.phone_outlined, label: 'Teléfono', value: perfil.numero),
            _DatoRow(icon: Icons.contact_phone_outlined, label: 'Emergencia', value: perfil.numeroReferencia),
            _DatoRow(icon: Icons.home_outlined, label: 'Domicilio', value: perfil.domicilio, isLast: true),
          ],
        ),
      ),
    );
  }
}

class _DatoRow extends StatelessWidget {
  const _DatoRow({
    required this.icon,
    required this.label,
    required this.value,
    this.isLast = false,
  });

  final IconData icon;
  final String label;
  final String value;
  final bool isLast;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: isLast ? 0 : 14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 18, color: AppColors.muted),
          const SizedBox(width: 12),
          SizedBox(
            width: 84,
            child: Text(label, style: const TextStyle(color: AppColors.muted, fontSize: 13)),
          ),
          Expanded(child: Text(value, style: const TextStyle(fontWeight: FontWeight.w500))),
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
          ? const Padding(
              padding: EdgeInsets.symmetric(vertical: 8),
              child: Text('No tienes cursos en el periodo actual.', style: TextStyle(color: AppColors.muted)),
            )
          : Column(children: [for (final curso in list) _CursoDelSemestreTile(curso: curso)]),
      loading: () => const Center(child: CircularProgressIndicator()),
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
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        leading: Container(
          width: 38,
          height: 38,
          decoration: BoxDecoration(
            color: AppColors.gold.withValues(alpha: 0.14),
            shape: BoxShape.circle,
          ),
          child: const Icon(Icons.menu_book_outlined, size: 18, color: AppColors.gold),
        ),
        title: Text(curso.nombre, style: const TextStyle(fontWeight: FontWeight.w600)),
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
