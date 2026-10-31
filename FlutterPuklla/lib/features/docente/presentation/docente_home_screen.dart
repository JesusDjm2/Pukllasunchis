import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/section_header.dart';
import '../../../core/widgets/stat_card.dart';
import '../../../routing/app_drawer.dart';
import '../../auth/application/auth_controller.dart';
import '../../notificaciones/presentation/notification_bell_action.dart';
import '../application/docente_providers.dart';
import '../data/docente_curso.dart';
import '../data/docente_resumen.dart';

/// Pantalla de aterrizaje de un docente: resumen del período y sus cursos
/// asignados, equivalente a `resources/views/docentes/show.blade.php` en la web.
class DocenteHomeScreen extends ConsumerWidget {
  const DocenteHomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final resumen = ref.watch(docenteResumenProvider);
    final cursos = ref.watch(docenteCursosProvider);
    final nombre = ref.watch(authControllerProvider).user?.name ?? 'Docente';

    return Scaffold(
      appBar: AppBar(
        title: const Text('Mi panel'),
        actions: const [NotificationBellAction()],
      ),
      drawer: const AppDrawer(),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(docenteResumenProvider);
          ref.invalidate(docenteCursosProvider);
          await ref.read(docenteResumenProvider.future);
        },
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            _Saludo(
              nombre: nombre,
              periodo: resumen.valueOrNull?.periodoActual,
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 20, 16, 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  resumen.when(
                    data: (data) => _TarjetasResumen(resumen: data),
                    loading: () => const _ResumenSkeleton(),
                    error: (e, _) => ErrorState(
                      message: 'No se pudo cargar tu resumen.',
                      onRetry: () => ref.invalidate(docenteResumenProvider),
                    ),
                  ),
                  const SizedBox(height: 28),
                  const SectionHeader(icon: Icons.school_outlined, title: 'Mis cursos'),
                  cursos.when(
                    data: (lista) => lista.isEmpty
                        ? const EmptyState(
                            icon: Icons.school_outlined,
                            message: 'No tienes cursos asignados todavía.',
                          )
                        : Column(
                            children: [
                              for (final curso in lista)
                                Padding(
                                  padding: const EdgeInsets.only(bottom: 12),
                                  child: _TarjetaCurso(curso: curso),
                                ),
                            ],
                          ),
                    loading: () => const Padding(
                      padding: EdgeInsets.symmetric(vertical: 24),
                      child: Center(child: CircularProgressIndicator()),
                    ),
                    error: (e, _) => ErrorState(
                      message: 'No se pudieron cargar tus cursos.',
                      onRetry: () => ref.invalidate(docenteCursosProvider),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Saludo extends StatelessWidget {
  const _Saludo({required this.nombre, required this.periodo});

  final String nombre;
  final String? periodo;

  @override
  Widget build(BuildContext context) {
    final iniciales = nombre.trim().isEmpty
        ? '?'
        : nombre.trim().split(RegExp(r'\s+')).take(2).map((p) => p[0].toUpperCase()).join();

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
          CircleAvatar(
            radius: 26,
            backgroundColor: Colors.white.withValues(alpha: 0.15),
            child: Text(
              iniciales,
              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 18),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Hola, $nombre',
                  style: const TextStyle(color: Colors.white, fontSize: 19, fontWeight: FontWeight.w700),
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 2),
                Text(
                  periodo == null ? 'Sin período académico activo' : 'Período académico $periodo',
                  style: TextStyle(color: Colors.white.withValues(alpha: 0.75), fontSize: 13),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _ResumenSkeleton extends StatelessWidget {
  const _ResumenSkeleton();

  @override
  Widget build(BuildContext context) {
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 12,
      crossAxisSpacing: 12,
      childAspectRatio: 1.5,
      children: List.generate(
        4,
        (_) => Card(
          child: Container(margin: const EdgeInsets.all(16), color: AppColors.background),
        ),
      ),
    );
  }
}

class _TarjetasResumen extends StatelessWidget {
  const _TarjetasResumen({required this.resumen});

  final DocenteResumen resumen;

  @override
  Widget build(BuildContext context) {
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 12,
      crossAxisSpacing: 12,
      childAspectRatio: 1.5,
      children: [
        StatCard(
          icon: Icons.school_outlined,
          value: '${resumen.cursos}',
          label: 'Cursos a tu cargo',
          detail: '${resumen.cursosFid} FID · ${resumen.cursosPpd} PPD',
          color: AppColors.gold,
        ),
        StatCard(
          icon: Icons.laptop_chromebook,
          value: '${resumen.cursosAsincronicos}',
          label: 'Asincrónicos',
          detail: 'A tu cargo',
          color: AppColors.teal,
        ),
        StatCard(
          icon: Icons.assignment_outlined,
          value: '${resumen.incidenciasTotal}',
          label: 'Incidencias',
          detail: '${resumen.incidenciasPendientes} pendientes',
          color: resumen.incidenciasPendientes > 0 ? AppColors.warning : AppColors.success,
          onTap: () => context.go('/docente/incidencias'),
        ),
        StatCard(
          icon: Icons.notifications_outlined,
          value: '${resumen.notificacionesSinLeer}',
          label: 'Notificaciones',
          detail: 'sin leer',
          color: AppColors.danger,
          onTap: () => context.go('/notificaciones'),
        ),
      ],
    );
  }
}

class _TarjetaCurso extends StatelessWidget {
  const _TarjetaCurso({required this.curso});

  final DocenteCurso curso;

  static const _etiquetas = {
    'parcial_1': 'Parcial 1',
    'parcial_2': 'Parcial 2',
    'desempeno': 'Desempeño',
    'calificado': 'Calificado',
  };

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        curso.nombre,
                        style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
                      ),
                      if (curso.ciclo != null || curso.programa != null)
                        Padding(
                          padding: const EdgeInsets.only(top: 2),
                          child: Text(
                            [curso.ciclo, curso.programa].whereType<String>().join(' · '),
                            style: const TextStyle(fontSize: 12, color: AppColors.muted),
                          ),
                        ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                _Etiqueta(texto: curso.esPpd ? 'PPD' : 'FID'),
              ],
            ),
            const SizedBox(height: 14),
            for (final entrada in curso.progreso.entries)
              Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: _BarraProgreso(
                  etiqueta: _etiquetas[entrada.key] ?? entrada.key,
                  valor: entrada.value,
                ),
              ),
            const SizedBox(height: 2),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: (curso.silaboListo ? AppColors.success : AppColors.warning).withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    curso.silaboListo ? Icons.check_circle : Icons.error_outline,
                    size: 15,
                    color: curso.silaboListo ? AppColors.success : AppColors.warning,
                  ),
                  const SizedBox(width: 6),
                  Text(
                    curso.silaboListo ? 'Sílabo registrado' : 'Sílabo pendiente',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: curso.silaboListo ? AppColors.success : AppColors.warning,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _BarraProgreso extends StatelessWidget {
  const _BarraProgreso({required this.etiqueta, required this.valor});

  final String etiqueta;
  final double valor;

  Color get _color {
    if (valor >= 80) return AppColors.success;
    if (valor >= 40) return AppColors.gold;
    return AppColors.danger;
  }

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        SizedBox(
          width: 86,
          child: Text(etiqueta, style: const TextStyle(fontSize: 12, color: AppColors.muted)),
        ),
        Expanded(
          child: ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: LinearProgressIndicator(
              value: (valor / 100).clamp(0, 1),
              minHeight: 8,
              backgroundColor: AppColors.background,
              color: _color,
            ),
          ),
        ),
        const SizedBox(width: 8),
        SizedBox(
          width: 36,
          child: Text(
            '${valor.toStringAsFixed(0)}%',
            textAlign: TextAlign.right,
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
          ),
        ),
      ],
    );
  }
}

class _Etiqueta extends StatelessWidget {
  const _Etiqueta({required this.texto});

  final String texto;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: AppColors.teal.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        texto,
        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.teal),
      ),
    );
  }
}
