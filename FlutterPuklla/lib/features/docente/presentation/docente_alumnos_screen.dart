import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../../../core/widgets/empty_state.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../core/widgets/puklla_preloader.dart';
import '../../../routing/app_drawer.dart';
import '../application/docente_providers.dart';
import '../data/docente_alumno.dart';

/// Pantalla completa de Directorio de Estudiantes para el docente.
/// Agrupa los alumnos por curso en formato acordeón expandible (FID y PPD),
/// con buscador en tiempo real y botones directos de WhatsApp, llamada y correo
/// tanto para el número principal como para el de referencia.
class DocenteAlumnosScreen extends ConsumerStatefulWidget {
  const DocenteAlumnosScreen({super.key, this.cursoIdInicial});

  final int? cursoIdInicial;

  @override
  ConsumerState<DocenteAlumnosScreen> createState() => _DocenteAlumnosScreenState();
}

class _DocenteAlumnosScreenState extends ConsumerState<DocenteAlumnosScreen> {
  final TextEditingController _busquedaController = TextEditingController();
  String _filtroTexto = '';
  String _filtroArea = 'todos'; // 'todos' | 'fid' | 'ppd'

  @override
  void dispose() {
    _busquedaController.dispose();
    super.dispose();
  }

  String _limpiarParaWhatsapp(String telefono) {
    final digits = telefono.replaceAll(RegExp(r'\D'), '');
    if (digits.startsWith('51') && digits.length >= 11) {
      return digits;
    }
    if (digits.length == 9) {
      return '51$digits';
    }
    return digits;
  }

  Future<void> _abrirWhatsapp(BuildContext context, String telefono, String nombre) async {
    final clean = _limpiarParaWhatsapp(telefono);
    final uri = Uri.parse('https://wa.me/$clean');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('No se pudo abrir WhatsApp para $telefono')),
        );
      }
    }
  }

  Future<void> _llamarNumero(BuildContext context, String telefono) async {
    final digits = telefono.replaceAll(RegExp(r'[^\d+]'), '');
    final uri = Uri.parse('tel:$digits');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    } else {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('No se pudo realizar la llamada a $telefono')),
        );
      }
    }
  }

  Future<void> _enviarCorreo(String email) async {
    final uri = Uri.parse('mailto:$email');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  @override
  Widget build(BuildContext context) {
    final alumnosAsync = ref.watch(docenteAlumnosProvider);
    final isDark = PukllaDashboardStyles.isDark(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Directorio de Alumnos'),
        actions: const [PukllaAppBarActions()],
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(docenteAlumnosProvider);
          await ref.read(docenteAlumnosProvider.future);
        },
        child: alumnosAsync.when(
          loading: () => const Center(
            child: PukllaPreloader.inline(mensaje: 'Cargando directorio de estudiantes...'),
          ),
          error: (err, _) => Center(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: ErrorState(
                message: 'No se pudo cargar la lista de alumnos: $err',
                onRetry: () => ref.refresh(docenteAlumnosProvider),
              ),
            ),
          ),
          data: (cursosConAlumnos) {
            if (cursosConAlumnos.isEmpty) {
              return const Center(
                child: EmptyState(
                  icon: Icons.people_outline,
                  message: 'No tienes alumnos matriculados en tus cursos actualmente.',
                ),
              );
            }

            // Filtrado por área
            final cursosArea = cursosConAlumnos.where((c) {
              if (_filtroArea == 'fid') return !c.esPpd;
              if (_filtroArea == 'ppd') return c.esPpd;
              return true;
            }).toList();

            // Total de alumnos en los cursos filtrados
            int totalAlumnosMostrados = 0;
            for (final c in cursosArea) {
              totalAlumnosMostrados += c.alumnos.length;
            }

            return ListView(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
              children: [
                // Barra de Búsqueda Senior
                Container(
                  decoration: BoxDecoration(
                    color: isDark ? AppColors.darkSurface : Colors.white,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: isDark ? 0.3 : 0.05),
                        blurRadius: 10,
                        offset: const Offset(0, 3),
                      ),
                    ],
                  ),
                  child: TextField(
                    controller: _busquedaController,
                    onChanged: (val) => setState(() => _filtroTexto = val.trim().toLowerCase()),
                    style: TextStyle(
                      fontSize: 14,
                      color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                    ),
                    decoration: InputDecoration(
                      hintText: 'Buscar por nombre, apellido, DNI o teléfono...',
                      hintStyle: TextStyle(
                        fontSize: 13,
                        color: isDark ? AppColors.darkMuted : const Color(0xFF94A3B8),
                      ),
                      prefixIcon: const Icon(Icons.search, size: 20, color: AppColors.gold),
                      suffixIcon: _filtroTexto.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear, size: 18),
                              onPressed: () {
                                _busquedaController.clear();
                                setState(() => _filtroTexto = '');
                              },
                            )
                          : null,
                      border: InputBorder.none,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    ),
                  ),
                ),
                const SizedBox(height: 14),

                // Filtros de Área (Todos, FID, PPD) y Conteo
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Wrap(
                      spacing: 8,
                      children: [
                        _FiltroAreaChip(
                          label: 'Todos',
                          seleccionado: _filtroArea == 'todos',
                          onTap: () => setState(() => _filtroArea = 'todos'),
                        ),
                        _FiltroAreaChip(
                          label: 'FID',
                          seleccionado: _filtroArea == 'fid',
                          onTap: () => setState(() => _filtroArea = 'fid'),
                        ),
                        _FiltroAreaChip(
                          label: 'PPD',
                          seleccionado: _filtroArea == 'ppd',
                          onTap: () => setState(() => _filtroArea = 'ppd'),
                        ),
                      ],
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: AppColors.teal.withValues(alpha: isDark ? 0.20 : 0.10),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        '$totalAlumnosMostrados estudiantes',
                        style: const TextStyle(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w700,
                          color: AppColors.teal,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Acordeones por Curso
                for (final curso in cursosArea) ...[
                  _AcordeonCurso(
                    curso: curso,
                    filtroTexto: _filtroTexto,
                    debeExpandirInicial: widget.cursoIdInicial == curso.cursoId || _filtroTexto.isNotEmpty,
                    onLlamar: (tel) => _llamarNumero(context, tel),
                    onWhatsapp: (tel, nom) => _abrirWhatsapp(context, tel, nom),
                    onEmail: (email) => _enviarCorreo(email),
                  ),
                  const SizedBox(height: 12),
                ],
              ],
            );
          },
        ),
      ),
    );
  }
}

class _FiltroAreaChip extends StatelessWidget {
  const _FiltroAreaChip({
    required this.label,
    required this.seleccionado,
    required this.onTap,
  });

  final String label;
  final bool seleccionado;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        decoration: BoxDecoration(
          color: seleccionado
              ? AppColors.gold
              : (isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9)),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: seleccionado
                ? AppColors.gold
                : PukllaDashboardStyles.borderColor(context),
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 12.5,
            fontWeight: FontWeight.w700,
            color: seleccionado
                ? Colors.white
                : (isDark ? AppColors.darkText : const Color(0xFF475569)),
          ),
        ),
      ),
    );
  }
}

class _AcordeonCurso extends StatelessWidget {
  const _AcordeonCurso({
    required this.curso,
    required this.filtroTexto,
    required this.debeExpandirInicial,
    required this.onLlamar,
    required this.onWhatsapp,
    required this.onEmail,
  });

  final DocenteCursoAlumnos curso;
  final String filtroTexto;
  final bool debeExpandirInicial;
  final ValueChanged<String> onLlamar;
  final void Function(String telefono, String nombre) onWhatsapp;
  final ValueChanged<String> onEmail;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);

    // Filtrar alumnos según texto de búsqueda
    final alumnosFiltrados = curso.alumnos.where((a) {
      if (filtroTexto.isEmpty) return true;
      final nombre = a.nombreCompleto.toLowerCase();
      final dni = a.dni?.toLowerCase() ?? '';
      final email = a.email?.toLowerCase() ?? '';
      final tel = a.telefono?.toLowerCase() ?? '';
      final ref = a.telefonoReferencia?.toLowerCase() ?? '';
      return nombre.contains(filtroTexto) ||
          dni.contains(filtroTexto) ||
          email.contains(filtroTexto) ||
          tel.contains(filtroTexto) ||
          ref.contains(filtroTexto);
    }).toList();

    // Si hay búsqueda activa y ningún alumno coincide en este curso, omitir
    if (filtroTexto.isNotEmpty && alumnosFiltrados.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: PukllaDashboardStyles.borderColor(context)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.25 : 0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Theme(
        data: Theme.of(context).copyWith(
          dividerColor: Colors.transparent,
        ),
        child: ExpansionTile(
          initiallyExpanded: debeExpandirInicial,
          tilePadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          leading: Container(
            padding: const EdgeInsets.all(9),
            decoration: BoxDecoration(
              color: curso.esPpd
                  ? (isDark ? const Color(0xFF581C87).withValues(alpha: 0.35) : const Color(0xFFF3E8FF))
                  : AppColors.teal.withValues(alpha: isDark ? 0.25 : 0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              Icons.school_rounded,
              color: curso.esPpd
                  ? (isDark ? const Color(0xFFC084FC) : const Color(0xFF7E22CE))
                  : AppColors.teal,
              size: 22,
            ),
          ),
          title: Text(
            curso.cursoNombre,
            style: TextStyle(
              fontWeight: FontWeight.w800,
              fontSize: 14.5,
              color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
            ),
          ),
          subtitle: Padding(
            padding: const EdgeInsets.only(top: 4),
            child: Wrap(
              spacing: 6,
              runSpacing: 4,
              children: [
                if (curso.programa != null)
                  _MiniBadge(texto: curso.programa!, isDark: isDark),
                if (curso.ciclo != null)
                  _MiniBadge(texto: 'Ciclo ${curso.ciclo}', isDark: isDark),
                _MiniBadge(
                  texto: curso.esPpd ? 'PPD' : 'FID',
                  isDark: isDark,
                  color: curso.esPpd ? const Color(0xFF7E22CE) : AppColors.teal,
                ),
                _MiniBadge(
                  texto: '${alumnosFiltrados.length} alumnos',
                  isDark: isDark,
                  color: AppColors.gold,
                ),
              ],
            ),
          ),
          children: [
            Divider(height: 1, color: PukllaDashboardStyles.borderColor(context)),
            const SizedBox(height: 12),
            if (alumnosFiltrados.isEmpty)
              Padding(
                padding: const EdgeInsets.all(16),
                child: Text(
                  'No hay estudiantes registrados en este curso.',
                  style: TextStyle(
                    fontSize: 12.5,
                    color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                  ),
                ),
              )
            else
              for (final alumno in alumnosFiltrados) ...[
                _TarjetaAlumnoDirectorio(
                  alumno: alumno,
                  onLlamar: onLlamar,
                  onWhatsapp: onWhatsapp,
                  onEmail: onEmail,
                ),
                const SizedBox(height: 10),
              ],
          ],
        ),
      ),
    );
  }
}

class _MiniBadge extends StatelessWidget {
  const _MiniBadge({required this.texto, required this.isDark, this.color});

  final String texto;
  final bool isDark;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    final c = color ?? (isDark ? AppColors.darkMuted : const Color(0xFF64748B));
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
      decoration: BoxDecoration(
        color: c.withValues(alpha: isDark ? 0.20 : 0.10),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Text(
        texto,
        style: TextStyle(
          fontSize: 10.5,
          fontWeight: FontWeight.w700,
          color: c,
        ),
      ),
    );
  }
}

class _TarjetaAlumnoDirectorio extends StatelessWidget {
  const _TarjetaAlumnoDirectorio({
    required this.alumno,
    required this.onLlamar,
    required this.onWhatsapp,
    required this.onEmail,
  });

  final DocenteAlumno alumno;
  final ValueChanged<String> onLlamar;
  final void Function(String telefono, String nombre) onWhatsapp;
  final ValueChanged<String> onEmail;

  @override
  Widget build(BuildContext context) {
    final isDark = PukllaDashboardStyles.isDark(context);
    final tienePrincipal = alumno.telefono != null && alumno.telefono!.trim().isNotEmpty;
    final tieneRef = alumno.telefonoReferencia != null && alumno.telefonoReferencia!.trim().isNotEmpty;
    final tieneEmail = alumno.email != null && alumno.email!.trim().isNotEmpty;

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurfaceElevated : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isDark ? AppColors.darkBorder.withValues(alpha: 0.6) : const Color(0xFFE2E8F0),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Fila del Alumno: Avatar + Nombre + DNI
          Row(
            children: [
              CircleAvatar(
                radius: 18,
                backgroundColor: AppColors.teal.withValues(alpha: isDark ? 0.28 : 0.15),
                child: Text(
                  alumno.nombreCompleto.isNotEmpty ? alumno.nombreCompleto[0].toUpperCase() : '?',
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w800,
                    color: AppColors.teal,
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      alumno.nombreCompleto,
                      style: TextStyle(
                        fontSize: 13.5,
                        fontWeight: FontWeight.w700,
                        color: isDark ? AppColors.darkText : const Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Wrap(
                      spacing: 6,
                      children: [
                        if (alumno.dni != null && alumno.dni!.isNotEmpty)
                          Text(
                            'DNI: ${alumno.dni}',
                            style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w600,
                              color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                            ),
                          ),
                        if (alumno.email != null && alumno.email!.isNotEmpty)
                          Text(
                            '· ${alumno.email}',
                            style: TextStyle(
                              fontSize: 11,
                              color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                            ),
                          ),
                      ],
                    ),
                  ],
                ),
              ),
              if (tieneEmail)
                IconButton(
                  icon: const Icon(Icons.email_outlined, size: 18, color: AppColors.teal),
                  tooltip: 'Enviar correo a ${alumno.email}',
                  onPressed: () => onEmail(alumno.email!),
                ),
            ],
          ),

          if (tienePrincipal || tieneRef) ...[
            const SizedBox(height: 10),
            Divider(height: 1, color: PukllaDashboardStyles.borderColor(context)),
            const SizedBox(height: 8),

            // Bloque de Teléfono Principal
            if (tienePrincipal)
              _FilaTelefono(
                tipo: 'Teléfono Principal',
                numero: alumno.telefono!,
                nombre: alumno.nombreCompleto,
                isDark: isDark,
                onLlamar: () => onLlamar(alumno.telefono!),
                onWhatsapp: () => onWhatsapp(alumno.telefono!, alumno.nombreCompleto),
              ),

            // Bloque de Teléfono de Referencia
            if (tieneRef) ...[
              if (tienePrincipal) const SizedBox(height: 6),
              _FilaTelefono(
                tipo: 'Teléfono Referencia',
                numero: alumno.telefonoReferencia!,
                nombre: alumno.nombreCompleto,
                isDark: isDark,
                esReferencia: true,
                onLlamar: () => onLlamar(alumno.telefonoReferencia!),
                onWhatsapp: () => onWhatsapp(alumno.telefonoReferencia!, alumno.nombreCompleto),
              ),
            ],
          ] else ...[
            const SizedBox(height: 6),
            Text(
              'Sin números telefónicos registrados',
              style: TextStyle(
                fontSize: 11,
                fontStyle: FontStyle.italic,
                color: isDark ? AppColors.darkMuted : const Color(0xFF94A3B8),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _FilaTelefono extends StatelessWidget {
  const _FilaTelefono({
    required this.tipo,
    required this.numero,
    required this.nombre,
    required this.isDark,
    this.esReferencia = false,
    required this.onLlamar,
    required this.onWhatsapp,
  });

  final String tipo;
  final String numero;
  final String nombre;
  final bool isDark;
  final bool esReferencia;
  final VoidCallback onLlamar;
  final VoidCallback onWhatsapp;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: isDark ? AppColors.darkSurface : Colors.white,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: isDark ? AppColors.darkBorder.withValues(alpha: 0.4) : const Color(0xFFF1F5F9),
        ),
      ),
      child: Row(
        children: [
          Icon(
            esReferencia ? Icons.contact_phone_outlined : Icons.phone_android_rounded,
            size: 16,
            color: esReferencia ? AppColors.gold : AppColors.teal,
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  tipo,
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                    color: isDark ? AppColors.darkMuted : const Color(0xFF64748B),
                  ),
                ),
                Text(
                  numero,
                  style: TextStyle(
                    fontSize: 12.5,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 0.3,
                    color: isDark ? AppColors.darkText : const Color(0xFF1E293B),
                  ),
                ),
              ],
            ),
          ),

          // Botón Llamar
          InkWell(
            onTap: onLlamar,
            borderRadius: BorderRadius.circular(6),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
              decoration: BoxDecoration(
                color: AppColors.teal.withValues(alpha: isDark ? 0.25 : 0.12),
                borderRadius: BorderRadius.circular(6),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.phone, size: 14, color: AppColors.teal),
                  SizedBox(width: 4),
                  Text(
                    'Llamar',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: AppColors.teal,
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(width: 6),

          // Botón WhatsApp Verde Oficial
          InkWell(
            onTap: onWhatsapp,
            borderRadius: BorderRadius.circular(6),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 5),
              decoration: BoxDecoration(
                color: const Color(0xFF25D366).withValues(alpha: isDark ? 0.25 : 0.15),
                borderRadius: BorderRadius.circular(6),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.chat_bubble_outline, size: 14, color: Color(0xFF16A34A)),
                  SizedBox(width: 4),
                  Text(
                    'WhatsApp',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF16A34A),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
