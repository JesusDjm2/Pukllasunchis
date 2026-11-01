import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../core/widgets/puklla_preloader.dart';
import '../../../routing/app_drawer.dart';
import '../application/tutoria_providers.dart';
import '../data/tutoria_data.dart';
import '../data/tutoria_repository.dart';

class TutoriaScreen extends ConsumerStatefulWidget {
  const TutoriaScreen({super.key});

  @override
  ConsumerState<TutoriaScreen> createState() => _TutoriaScreenState();
}

class _TutoriaScreenState extends ConsumerState<TutoriaScreen> {
  int _tabIndex = 0; // 0: Tutoría, 1: Sugerencia

  // Formulario Tutoría
  final _tutoriaFormKey = GlobalKey<FormState>();
  String? _urgenciaSeleccionada;
  int? _docenteSeleccionadoId;
  final _motivoController = TextEditingController();
  bool _enviandoTutoria = false;

  // Formulario Sugerencia
  final _sugerenciaFormKey = GlobalKey<FormState>();
  final _mensajeSugerenciaController = TextEditingController();
  bool _enviandoSugerencia = false;

  @override
  void dispose() {
    _motivoController.dispose();
    _mensajeSugerenciaController.dispose();
    super.dispose();
  }

  Future<void> _submitTutoria(TutoriaData data) async {
    if (_urgenciaSeleccionada == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Por favor elige cómo deseas conversar con el tutor.'),
          backgroundColor: AppColors.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    if (_urgenciaSeleccionada == 'otro_docente' && _docenteSeleccionadoId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Debes seleccionar con qué docente deseas conversar.'),
          backgroundColor: AppColors.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    if (!_tutoriaFormKey.currentState!.validate()) return;

    setState(() => _enviandoTutoria = true);

    try {
      final repo = ref.read(tutoriaRepositoryProvider);
      final msg = await repo.solicitarTutoria(
        urgencia: _urgenciaSeleccionada!,
        docenteId: _docenteSeleccionadoId,
        motivo: _motivoController.text.trim(),
      );

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(msg),
          backgroundColor: const Color(0xFF10B981),
          behavior: SnackBarBehavior.floating,
        ),
      );

      // Limpiar campos
      _motivoController.clear();
      setState(() {
        _urgenciaSeleccionada = null;
        _docenteSeleccionadoId = null;
      });

      // Refrescar lista de historial
      ref.invalidate(tutoriaDataProvider);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error al enviar solicitud: ${e.toString()}'),
          backgroundColor: AppColors.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) setState(() => _enviandoTutoria = false);
    }
  }

  Future<void> _submitSugerencia() async {
    if (!_sugerenciaFormKey.currentState!.validate()) return;

    setState(() => _enviandoSugerencia = true);

    try {
      final repo = ref.read(tutoriaRepositoryProvider);
      final msg = await repo.enviarSugerencia(
        mensaje: _mensajeSugerenciaController.text.trim(),
      );

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(msg),
          backgroundColor: const Color(0xFF10B981),
          behavior: SnackBarBehavior.floating,
        ),
      );

      _mensajeSugerenciaController.clear();
      ref.invalidate(tutoriaDataProvider);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error al enviar sugerencia: ${e.toString()}'),
          backgroundColor: AppColors.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) setState(() => _enviandoSugerencia = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final tutoriaAsync = ref.watch(tutoriaDataProvider);

    return Scaffold(
      backgroundColor: PukllaDashboardStyles.backgroundColor(context),
      appBar: AppBar(
        title: const Text('Tutoría y Sugerencias'),
        actions: const [PukllaAppBarActions()],
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: tutoriaAsync.when(
        loading: () => const PukllaPreloader(mensaje: 'Conectando con el espacio de tutoría...'),
        error: (err, stack) => ErrorState(
          message: 'No se pudo cargar la información de tutoría.',
          onRetry: () => ref.refresh(tutoriaDataProvider),
        ),
        data: (data) {
          return RefreshIndicator(
            onRefresh: () => ref.refresh(tutoriaDataProvider.future),
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
              children: [
                // Cabecera institucional de tutor asignado (calcado de la web)
                _HeaderTutorCard(alumno: data.alumno),
                const SizedBox(height: 18),

                // Selector de pestañas elegante
                _TabSelector(
                  selectedIndex: _tabIndex,
                  onSelect: (idx) => setState(() => _tabIndex = idx),
                ),
                const SizedBox(height: 18),

                // Formulario según pestaña
                if (_tabIndex == 0)
                  _FormTutoriaIndividual(
                    formKey: _tutoriaFormKey,
                    opciones: data.opciones,
                    docentesElegibles: data.docentesElegibles,
                    urgenciaSeleccionada: _urgenciaSeleccionada,
                    docenteSeleccionadoId: _docenteSeleccionadoId,
                    motivoController: _motivoController,
                    isEnviando: _enviandoTutoria,
                    onUrgenciaChanged: (val) {
                      setState(() {
                        _urgenciaSeleccionada = val;
                        if (val != 'otro_docente') {
                          _docenteSeleccionadoId = null;
                        }
                      });
                    },
                    onDocenteChanged: (val) => setState(() => _docenteSeleccionadoId = val),
                    onSubmit: () => _submitTutoria(data),
                  )
                else
                  _FormBuzonSugerencias(
                    formKey: _sugerenciaFormKey,
                    controller: _mensajeSugerenciaController,
                    isEnviando: _enviandoSugerencia,
                    onSubmit: _submitSugerencia,
                  ),

                const SizedBox(height: 28),

                // Sección Historial: "Lo que has enviado"
                _HistorialSection(historial: data.historial),
                const SizedBox(height: 32),
              ],
            ),
          );
        },
      ),
    );
  }
}

/// Tarjeta de datos del tutor y ciclo del alumno (idéntica a .at-tutor en web)
class _HeaderTutorCard extends StatelessWidget {
  const _HeaderTutorCard({required this.alumno});

  final TutoriaAlumno alumno;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final tutoresTexto = alumno.tutores.isNotEmpty
        ? alumno.tutores.join(', ')
        : 'Tu ciclo aún no tiene tutor(a) asignado';

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 16),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: AppColors.gold.withValues(alpha: 0.15),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.person_pin, color: AppColors.goldHover, size: 24),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  alumno.tutores.length > 1 ? 'TUS TUTORES' : 'TU TUTOR(A)',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 0.8,
                    color: PukllaDashboardStyles.textSecondary(context),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  tutoresTexto,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: isDark ? AppColors.goldHover : AppColors.tealDark,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  '${alumno.programa} · ${alumno.ciclo}',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                    color: PukllaDashboardStyles.textSecondary(context),
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

/// Selector de pestañas para alternar entre Tutoría y Sugerencia
class _TabSelector extends StatelessWidget {
  const _TabSelector({
    required this.selectedIndex,
    required this.onSelect,
  });

  final int selectedIndex;
  final ValueChanged<int> onSelect;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    return Container(
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: isDark
            ? AppColors.darkSurfaceElevated
            : const Color(0xFFE2E8F0).withValues(alpha: 0.6),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: isDark ? AppColors.darkBorder : Colors.transparent,
        ),
      ),
      child: Row(
        children: [
          Expanded(
            child: _TabButton(
              title: 'Solicitar tutoría',
              icon: Icons.support_agent_outlined,
              isSelected: selectedIndex == 0,
              onTap: () => onSelect(0),
              activeColor: AppColors.teal,
            ),
          ),
          const SizedBox(width: 4),
          Expanded(
            child: _TabButton(
              title: 'Buzón de sugerencias',
              icon: Icons.lightbulb_outline,
              isSelected: selectedIndex == 1,
              onTap: () => onSelect(1),
              activeColor: AppColors.goldHover,
            ),
          ),
        ],
      ),
    );
  }
}

class _TabButton extends StatelessWidget {
  const _TabButton({
    required this.title,
    required this.icon,
    required this.isSelected,
    required this.onTap,
    required this.activeColor,
  });

  final String title;
  final IconData icon;
  final bool isSelected;
  final VoidCallback onTap;
  final Color activeColor;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
        decoration: BoxDecoration(
          color: isSelected
              ? (isDark ? AppColors.darkSurface : Colors.white)
              : Colors.transparent,
          borderRadius: BorderRadius.circular(10),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: isDark ? 0.25 : 0.06),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              icon,
              size: 18,
              color: isSelected
                  ? activeColor
                  : (isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
            ),
            const SizedBox(width: 8),
            Flexible(
              child: Text(
                title,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 12.5,
                  fontWeight: isSelected ? FontWeight.w700 : FontWeight.w600,
                  color: isSelected
                      ? activeColor
                      : (isDark ? AppColors.darkMuted : const Color(0xFF64748B)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Formulario para solicitar Tutoría Individual con los 5 campos de la web
class _FormTutoriaIndividual extends StatelessWidget {
  const _FormTutoriaIndividual({
    required this.formKey,
    required this.opciones,
    required this.docentesElegibles,
    required this.urgenciaSeleccionada,
    required this.docenteSeleccionadoId,
    required this.motivoController,
    required this.isEnviando,
    required this.onUrgenciaChanged,
    required this.onDocenteChanged,
    required this.onSubmit,
  });

  final GlobalKey<FormState> formKey;
  final List<TutoriaOpcion> opciones;
  final List<DocenteElegible> docentesElegibles;
  final String? urgenciaSeleccionada;
  final int? docenteSeleccionadoId;
  final TextEditingController motivoController;
  final bool isEnviando;
  final ValueChanged<String?> onUrgenciaChanged;
  final ValueChanged<int?> onDocenteChanged;
  final VoidCallback onSubmit;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final pideOtroDocente = urgenciaSeleccionada == 'otro_docente';

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 18),
      child: Form(
        key: formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppColors.teal.withValues(alpha: isDark ? 0.2 : 0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.record_voice_over_outlined, color: AppColors.teal, size: 20),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Solicitar tutoría individual',
                    style: TextStyle(
                      fontSize: 15.5,
                      fontWeight: FontWeight.w700,
                      color: isDark ? AppColors.goldHover : AppColors.tealDark,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              'Tu tutor(a) lo recibe al instante y se comunicará contigo.',
              style: TextStyle(
                fontSize: 12.5,
                color: PukllaDashboardStyles.textSecondary(context),
              ),
            ),
            const SizedBox(height: 18),

            // Pregunta central
            Text(
              '¿Te gustaría conversar individualmente con el tutor sobre alguna situación? *',
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: PukllaDashboardStyles.textPrimary(context),
              ),
            ),
            const SizedBox(height: 8),

            // Dropdown de opciones exactas
            DropdownButtonFormField<String>(
              initialValue: urgenciaSeleccionada,
              isExpanded: true,
              dropdownColor: isDark ? AppColors.darkSurfaceElevated : Colors.white,
              hint: Text(
                '— Elige una opción —',
                style: TextStyle(fontSize: 13, color: PukllaDashboardStyles.textSecondary(context)),
              ),
              items: opciones.map((opc) {
                return DropdownMenuItem<String>(
                  value: opc.clave,
                  child: Text(
                    opc.texto,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                      color: PukllaDashboardStyles.textPrimary(context),
                    ),
                  ),
                );
              }).toList(),
              onChanged: onUrgenciaChanged,
              validator: (val) => val == null ? 'Por favor elige una opción' : null,
              decoration: InputDecoration(
                contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                filled: true,
                fillColor: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(
                    color: isDark ? AppColors.darkBorder : const Color(0xFFCBD5E1),
                  ),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(
                    color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
                  ),
                ),
              ),
            ),

            // Campo condicional para elegir otro docente si pide "otro_docente"
            if (pideOtroDocente) ...[
              const SizedBox(height: 16),
              Text(
                '¿Con qué docente te gustaría conversar? *',
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: PukllaDashboardStyles.textPrimary(context),
                ),
              ),
              const SizedBox(height: 8),
              DropdownButtonFormField<int>(
                initialValue: docenteSeleccionadoId,
                isExpanded: true,
                dropdownColor: isDark ? AppColors.darkSurfaceElevated : Colors.white,
                hint: Text(
                  '— Elige un docente —',
                  style: TextStyle(fontSize: 13, color: PukllaDashboardStyles.textSecondary(context)),
                ),
                items: docentesElegibles.map((doc) {
                  final detalle = doc.detalle != null ? ' (${doc.detalle})' : '';
                  return DropdownMenuItem<int>(
                    value: doc.id,
                    child: Text(
                      '${doc.nombre}$detalle',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 12.5,
                        color: PukllaDashboardStyles.textPrimary(context),
                      ),
                    ),
                  );
                }).toList(),
                onChanged: onDocenteChanged,
                validator: (val) {
                  if (pideOtroDocente && val == null) {
                    return 'Selecciona un docente de la lista';
                  }
                  return null;
                },
                decoration: InputDecoration(
                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  filled: true,
                  fillColor: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide(
                      color: isDark ? AppColors.darkBorder : const Color(0xFFCBD5E1),
                    ),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide(
                      color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
                    ),
                  ),
                ),
              ),
            ],

            const SizedBox(height: 16),
            Row(
              children: [
                Text(
                  'Motivo ',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                    color: PukllaDashboardStyles.textPrimary(context),
                  ),
                ),
                Text(
                  '(opcional)',
                  style: TextStyle(fontSize: 12, color: PukllaDashboardStyles.textSecondary(context)),
                ),
              ],
            ),
            const SizedBox(height: 8),
            TextFormField(
              controller: motivoController,
              maxLines: 4,
              maxLength: 3000,
              style: TextStyle(
                fontSize: 13,
                color: PukllaDashboardStyles.textPrimary(context),
              ),
              decoration: InputDecoration(
                hintText: 'Si quieres, cuéntanos brevemente de qué te gustaría conversar…',
                hintStyle: TextStyle(fontSize: 12.5, color: PukllaDashboardStyles.textSecondary(context)),
                filled: true,
                fillColor: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(
                    color: isDark ? AppColors.darkBorder : const Color(0xFFCBD5E1),
                  ),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(
                    color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
                  ),
                ),
              ),
            ),

            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: FilledButton.icon(
                onPressed: isEnviando ? null : onSubmit,
                icon: isEnviando
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Icon(Icons.send_rounded, size: 18),
                label: Text(
                  isEnviando ? 'Enviando...' : 'Enviar solicitud',
                  style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w700),
                ),
                style: FilledButton.styleFrom(
                  backgroundColor: AppColors.teal,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Formulario para Buzón de Sugerencias
class _FormBuzonSugerencias extends StatelessWidget {
  const _FormBuzonSugerencias({
    required this.formKey,
    required this.controller,
    required this.isEnviando,
    required this.onSubmit,
  });

  final GlobalKey<FormState> formKey;
  final TextEditingController controller;
  final bool isEnviando;
  final VoidCallback onSubmit;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 18),
      child: Form(
        key: formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppColors.gold.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.lightbulb_outline, color: AppColors.goldHover, size: 20),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Buzón de sugerencias',
                    style: TextStyle(
                      fontSize: 15.5,
                      fontWeight: FontWeight.w700,
                      color: isDark ? AppColors.goldHover : AppColors.tealDark,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              '¿Una idea para mejorar las clases o un tema que te gustaría trabajar en tutoría? Compártelo con tu tutor(a).',
              style: TextStyle(
                fontSize: 12.5,
                color: PukllaDashboardStyles.textSecondary(context),
                height: 1.35,
              ),
            ),
            const SizedBox(height: 18),

            Text(
              'Tu sugerencia *',
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: PukllaDashboardStyles.textPrimary(context),
              ),
            ),
            const SizedBox(height: 8),
            TextFormField(
              controller: controller,
              maxLines: 6,
              maxLength: 3000,
              style: TextStyle(
                fontSize: 13,
                color: PukllaDashboardStyles.textPrimary(context),
              ),
              validator: (val) {
                if (val == null || val.trim().length < 10) {
                  return 'Escribe al menos 10 caracteres para que tu tutor(a) entienda la idea.';
                }
                return null;
              },
              decoration: InputDecoration(
                hintText: 'Ej.: Me gustaría que en tutoría habláramos sobre técnicas de estudio o talleres pedagógicos…',
                hintStyle: TextStyle(fontSize: 12.5, color: PukllaDashboardStyles.textSecondary(context)),
                filled: true,
                fillColor: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(
                    color: isDark ? AppColors.darkBorder : const Color(0xFFCBD5E1),
                  ),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(
                    color: isDark ? AppColors.darkBorder : const Color(0xFFE2E8F0),
                  ),
                ),
              ),
            ),

            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: FilledButton.icon(
                onPressed: isEnviando ? null : onSubmit,
                icon: isEnviando
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Icon(Icons.send_rounded, size: 18),
                label: Text(
                  isEnviando ? 'Enviando...' : 'Enviar sugerencia',
                  style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w700),
                ),
                style: FilledButton.styleFrom(
                  backgroundColor: AppColors.gold,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Sección Historial: "Lo que has enviado"
class _HistorialSection extends StatelessWidget {
  const _HistorialSection({required this.historial});

  final List<TutoriaHistorialItem> historial;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.history_rounded, size: 19, color: AppColors.teal),
              const SizedBox(width: 8),
              Text(
                'Lo que has enviado',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: isDark ? AppColors.goldHover : AppColors.tealDark,
                ),
              ),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Text(
                  '${historial.length}',
                  style: TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w700,
                    color: PukllaDashboardStyles.textSecondary(context),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          if (historial.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 20),
              child: Center(
                child: Column(
                  children: [
                    Icon(
                      Icons.inbox_outlined,
                      size: 36,
                      color: isDark ? AppColors.darkMuted : Colors.grey.shade400,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Aún no has enviado solicitudes ni sugerencias.',
                      style: TextStyle(
                        fontSize: 13,
                        color: PukllaDashboardStyles.textSecondary(context),
                      ),
                    ),
                  ],
                ),
              ),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: historial.length,
              separatorBuilder: (_, __) => Divider(
                color: PukllaDashboardStyles.borderColor(context),
                height: 20,
              ),
              itemBuilder: (context, index) {
                final item = historial[index];
                final esTutoria = item.tipo == 'tutoria';

                return Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Badge tipo (Tutoría / Sugerencia)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: esTutoria
                            ? AppColors.teal.withValues(alpha: isDark ? 0.22 : 0.12)
                            : AppColors.gold.withValues(alpha: isDark ? 0.22 : 0.15),
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Text(
                        item.etiqueta,
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w800,
                          color: esTutoria ? AppColors.teal : AppColors.goldHover,
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            item.texto,
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: PukllaDashboardStyles.textPrimary(context),
                              height: 1.35,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            item.extra != null
                                ? '${item.fecha} · ${item.extra}'
                                : item.fecha,
                            style: TextStyle(
                              fontSize: 11.5,
                              color: PukllaDashboardStyles.textSecondary(context),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    // Badge estado (Pendiente / Atendida / Revisada)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: item.listo
                            ? (isDark ? const Color(0xFF064E3B).withValues(alpha: 0.5) : const Color(0xFFE6F4EC))
                            : (isDark ? const Color(0xFF78350F).withValues(alpha: 0.4) : const Color(0xFFFFF4D6)),
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            item.listo ? Icons.check_circle_outline : Icons.access_time_rounded,
                            size: 13,
                            color: item.listo
                                ? (isDark ? const Color(0xFF34D399) : const Color(0xFF0B7A43))
                                : (isDark ? const Color(0xFFFBBF24) : const Color(0xFF9A6700)),
                          ),
                          const SizedBox(width: 4),
                          Text(
                            item.estado,
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                              color: item.listo
                                  ? (isDark ? const Color(0xFF34D399) : const Color(0xFF0B7A43))
                                  : (isDark ? const Color(0xFFFBBF24) : const Color(0xFF9A6700)),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                );
              },
            ),
        ],
      ),
    );
  }
}
