import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../routing/app_drawer.dart';
import '../application/taqe_providers.dart';
import '../data/taqe_data.dart';
import '../data/taqe_repository.dart';

class TaqeScreen extends ConsumerStatefulWidget {
  const TaqeScreen({super.key});

  @override
  ConsumerState<TaqeScreen> createState() => _TaqeScreenState();
}

class _TaqeScreenState extends ConsumerState<TaqeScreen> {
  Future<void> _responderInvitacion(int grupoId, String respuesta) async {
    try {
      final repo = ref.read(taqeRepositoryProvider);
      final msg = await repo.responderInvitacion(grupoId: grupoId, respuesta: respuesta);

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(msg),
          backgroundColor: respuesta == 'aceptado' ? const Color(0xFF10B981) : AppColors.teal,
          behavior: SnackBarBehavior.floating,
        ),
      );
      ref.invalidate(taqeDataProvider);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Error al responder: ${e.toString()}'),
          backgroundColor: AppColors.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  void _mostrarDialogoAyuda(TaqeGrupo grupo) {
    String motivo = 'otro';
    final mensajeController = TextEditingController();
    bool enviando = false;

    showDialog(
      context: context,
      builder: (dialogCtx) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppColors.gold.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.help_outline, color: AppColors.goldHover, size: 22),
                  ),
                  const SizedBox(width: 10),
                  const Expanded(
                    child: Text(
                      'Pedir ayuda / Asesoría',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
                    ),
                  ),
                ],
              ),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Grupo: ${grupo.nombre}',
                      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: AppColors.tealDark),
                    ),
                    const SizedBox(height: 12),
                    const Text('Motivo de la solicitud:', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5)),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<String>(
                      value: motivo,
                      isExpanded: true,
                      decoration: InputDecoration(
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      items: const [
                        DropdownMenuItem(value: 'cambiar_organizador', child: Text('Cambiar organizador')),
                        DropdownMenuItem(value: 'disolver_grupo', child: Text('Disolver grupo')),
                        DropdownMenuItem(value: 'otro', child: Text('Consulta metodológica o técnica')),
                      ],
                      onChanged: (v) => setDialogState(() => motivo = v ?? 'otro'),
                    ),
                    if (motivo == 'otro') ...[
                      const SizedBox(height: 12),
                      const Text('Describe tu consulta:', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5)),
                      const SizedBox(height: 6),
                      TextField(
                        controller: mensajeController,
                        maxLines: 3,
                        decoration: InputDecoration(
                          hintText: 'Escribe el motivo o duda...',
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: enviando ? null : () => Navigator.pop(dialogCtx),
                  child: const Text('Cancelar'),
                ),
                FilledButton(
                  onPressed: enviando
                      ? null
                      : () async {
                          setDialogState(() => enviando = true);
                          try {
                            final repo = ref.read(taqeRepositoryProvider);
                            final msg = await repo.solicitarAyuda(
                              grupoId: grupo.id,
                              motivo: motivo,
                              mensaje: motivo == 'otro' ? mensajeController.text.trim() : null,
                            );
                            if (dialogCtx.mounted) Navigator.pop(dialogCtx);
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(msg),
                                  backgroundColor: const Color(0xFF10B981),
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            }
                          } catch (e) {
                            setDialogState(() => enviando = false);
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text('Error: ${e.toString()}'),
                                  backgroundColor: AppColors.danger,
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            }
                          }
                        },
                  style: FilledButton.styleFrom(
                    backgroundColor: AppColors.gold,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: enviando
                      ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Text('Enviar solicitud'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _mostrarDialogoGuia(List<TaqeInvestigador> investigadores) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppColors.gold.withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.route_outlined, color: AppColors.goldHover, size: 22),
            ),
            const SizedBox(width: 10),
            const Expanded(
              child: Text(
                "¿Cómo funciona Taq'e?",
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
              ),
            ),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              _GuiaPasoItem(
                numero: '1',
                titulo: 'Crea tu grupo',
                descripcion: 'Elige un curso de tu ciclo y ponle un nombre. Tú quedas como organizador(a). Solo puedes estar en un grupo por periodo.',
              ),
              _GuiaPasoItem(
                numero: '2',
                titulo: 'Invita a tu equipo',
                descripcion: 'Solo compañeros de tu mismo Proyecto Integrador. Aceptan la invitación desde su intranet o app móvil.',
              ),
              _GuiaPasoItem(
                numero: '3',
                titulo: 'Sube evidencias',
                descripcion: 'Fotos, audios, videos, documentos o enlaces (YouTube, Canva, Drive). Cualquier integrante puede subirlas.',
              ),
              _GuiaPasoItem(
                numero: '4',
                titulo: 'Arma y entrega el trabajo final',
                descripcion: 'Todo el equipo puede subir archivos o pasar evidencias al trabajo final; el organizador lo entrega y el grupo queda Finalizado.',
              ),
              const Divider(height: 24),
              Row(
                children: const [
                  Icon(Icons.support_agent_rounded, size: 18, color: AppColors.teal),
                  SizedBox(width: 6),
                  Text(
                    'Docente Investigador(a)',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: AppColors.tealDark),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              if (investigadores.isEmpty)
                const Text(
                  'No hay un investigador asignado por ahora. Puedes usar "Pedir ayuda" desde tu grupo.',
                  style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                )
              else
                ...investigadores.map(
                  (inv) => Container(
                    margin: const EdgeInsets.only(bottom: 6),
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(inv.nombre, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700)),
                        if (inv.email != null) ...[
                          const SizedBox(height: 2),
                          Text(inv.email!, style: const TextStyle(fontSize: 11.5, color: Color(0xFF0284C7))),
                        ],
                      ],
                    ),
                  ),
                ),
            ],
          ),
        ),
        actions: [
          FilledButton(
            onPressed: () => Navigator.pop(ctx),
            style: FilledButton.styleFrom(backgroundColor: AppColors.teal),
            child: const Text('Entendido'),
          ),
        ],
      ),
    );
  }

  void _mostrarDialogoCrearGrupo(List<TaqeCursoOpcion> cursos) {
    if (cursos.isEmpty) return;

    int? cursoSeleccionado = cursos.first.id;
    final nombreController = TextEditingController();
    List<TaqeCompanero> companerosDisponibles = [];
    final Set<int> seleccionados = {};
    bool cargandoCompaneros = false;
    bool enviando = false;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogCtx) {
        return StatefulBuilder(
          builder: (ctx, setModalState) {
            void cargarCompaneros(int cursoId) async {
              setModalState(() => cargandoCompaneros = true);
              try {
                final repo = ref.read(taqeRepositoryProvider);
                final lista = await repo.fetchCompaneros(cursoId);
                setModalState(() {
                  companerosDisponibles = lista;
                  seleccionados.clear();
                  cargandoCompaneros = false;
                });
              } catch (_) {
                setModalState(() => cargandoCompaneros = false);
              }
            }

            if (companerosDisponibles.isEmpty && !cargandoCompaneros && cursoSeleccionado != null) {
              cargarCompaneros(cursoSeleccionado!);
            }

            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppColors.gold.withValues(alpha: 0.16),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.group_add_rounded, color: AppColors.goldHover, size: 22),
                  ),
                  const SizedBox(width: 10),
                  const Expanded(
                    child: Text(
                      "Crear grupo Taq'e",
                      style: TextStyle(fontSize: 16.5, fontWeight: FontWeight.w800),
                    ),
                  ),
                ],
              ),
              content: SizedBox(
                width: double.maxFinite,
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Text(
                        'Elige el curso, ponle un nombre y suma a tus compañeros para enviarles la invitación:',
                        style: TextStyle(fontSize: 12.5, color: Color(0xFF64748B), height: 1.35),
                      ),
                      const SizedBox(height: 14),

                      // Selector de curso
                      const Text('Curso del periodo:', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5)),
                      const SizedBox(height: 6),
                      DropdownButtonFormField<int>(
                        value: cursoSeleccionado,
                        isExpanded: true,
                        decoration: InputDecoration(
                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        items: cursos.map((c) {
                          return DropdownMenuItem<int>(
                            value: c.id,
                            child: Text(c.nombre, maxLines: 1, overflow: TextOverflow.ellipsis),
                          );
                        }).toList(),
                        onChanged: (val) {
                          if (val != null) {
                            setModalState(() => cursoSeleccionado = val);
                            cargarCompaneros(val);
                          }
                        },
                      ),
                      const SizedBox(height: 14),

                      // Nombre del grupo
                      const Text('Nombre del grupo:', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5)),
                      const SizedBox(height: 6),
                      TextField(
                        controller: nombreController,
                        decoration: InputDecoration(
                          hintText: 'Ej. Los Investigadores del Saber',
                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Selección de compañeros a invitar
                      Row(
                        children: [
                          const Text('Invitar compañeros:', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5)),
                          const Spacer(),
                          if (cargandoCompaneros)
                            const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2))
                          else
                            Text(
                              '${seleccionados.length} elegidos',
                              style: const TextStyle(fontSize: 11.5, color: AppColors.goldHover, fontWeight: FontWeight.w700),
                            ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Container(
                        constraints: const BoxConstraints(maxHeight: 180),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: cargandoCompaneros
                            ? const Center(child: Padding(padding: EdgeInsets.all(16), child: Text('Cargando alumnos del curso...')))
                            : companerosDisponibles.isEmpty
                                ? const Center(
                                    child: Padding(
                                      padding: EdgeInsets.all(16),
                                      child: Text(
                                        'No hay más compañeros disponibles en este curso.',
                                        textAlign: TextAlign.center,
                                        style: TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                                      ),
                                    ),
                                  )
                                : ListView.separated(
                                    shrinkWrap: true,
                                    itemCount: companerosDisponibles.length,
                                    separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFE2E8F0)),
                                    itemBuilder: (c, idx) {
                                      final comp = companerosDisponibles[idx];
                                      final isChecked = seleccionados.contains(comp.id);
                                      return CheckboxListTile(
                                        dense: true,
                                        title: Text(comp.nombre, style: const TextStyle(fontSize: 12.5)),
                                        value: isChecked,
                                        onChanged: (bool? val) {
                                          setModalState(() {
                                            if (val == true) {
                                              seleccionados.add(comp.id);
                                            } else {
                                              seleccionados.remove(comp.id);
                                            }
                                          });
                                        },
                                      );
                                    },
                                  ),
                      ),
                    ],
                  ),
                ),
              ),
              actions: [
                TextButton(
                  onPressed: enviando ? null : () => Navigator.pop(dialogCtx),
                  child: const Text('Cancelar'),
                ),
                FilledButton.icon(
                  onPressed: enviando
                      ? null
                      : () async {
                          final nombre = nombreController.text.trim();
                          if (nombre.isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Escribe un nombre para el grupo.')),
                            );
                            return;
                          }
                          if (cursoSeleccionado == null) return;

                          setModalState(() => enviando = true);
                          try {
                            final repo = ref.read(taqeRepositoryProvider);
                            final msg = await repo.crearGrupo(
                              nombre: nombre,
                              cursoId: cursoSeleccionado!,
                              integrantes: seleccionados.toList(),
                            );
                            if (dialogCtx.mounted) Navigator.pop(dialogCtx);
                            ref.invalidate(taqeDataProvider);
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(msg),
                                  backgroundColor: const Color(0xFF10B981),
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            }
                          } catch (e) {
                            setModalState(() => enviando = false);
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text('Error: ${e.toString()}'),
                                  backgroundColor: AppColors.danger,
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            }
                          }
                        },
                  style: FilledButton.styleFrom(backgroundColor: AppColors.goldHover),
                  icon: enviando
                      ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Icon(Icons.check_rounded, size: 18),
                  label: const Text('Crear grupo'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _mostrarDialogoSubirTrabajo(TaqeGrupo grupo, String destinoInicial) {
    int modo = 0; // 0: Enlace, 1: Archivo
    String destino = destinoInicial; // 'evidencia' | 'final'
    final nombreController = TextEditingController();
    final enlaceController = TextEditingController();
    bool enviando = false;

    showDialog(
      context: context,
      builder: (dialogCtx) {
        return StatefulBuilder(
          builder: (ctx, setModalState) {
            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              title: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: (destino == 'final' ? AppColors.teal : AppColors.gold).withValues(alpha: 0.16),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(
                      destino == 'final' ? Icons.assignment_turned_in_rounded : Icons.photo_library_rounded,
                      color: destino == 'final' ? AppColors.teal : AppColors.goldHover,
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      destino == 'final' ? 'Agregar al Trabajo Final' : 'Subir Evidencia de Proceso',
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
                    ),
                  ),
                ],
              ),
              content: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // Destino selector
                    Row(
                      children: [
                        Expanded(
                          child: ChoiceChip(
                            label: const Text('Evidencia'),
                            selected: destino == 'evidencia',
                            onSelected: (val) {
                              if (val) setModalState(() => destino = 'evidencia');
                            },
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: ChoiceChip(
                            label: const Text('Trabajo Final'),
                            selected: destino == 'final',
                            onSelected: (val) {
                              if (val) setModalState(() => destino = 'final');
                            },
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Selector de modo: Enlace vs Archivo
                    Row(
                      children: [
                        Expanded(
                          child: InkWell(
                            onTap: () => setModalState(() => modo = 0),
                            borderRadius: BorderRadius.circular(10),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 8),
                              decoration: BoxDecoration(
                                color: modo == 0 ? AppColors.teal.withValues(alpha: 0.1) : const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: modo == 0 ? AppColors.teal : Colors.transparent),
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.link_rounded, size: 16, color: modo == 0 ? AppColors.teal : const Color(0xFF64748B)),
                                  const SizedBox(width: 6),
                                  Text(
                                    'Enlace Web',
                                    style: TextStyle(
                                      fontSize: 12.5,
                                      fontWeight: modo == 0 ? FontWeight.w800 : FontWeight.w600,
                                      color: modo == 0 ? AppColors.teal : const Color(0xFF64748B),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: InkWell(
                            onTap: () => setModalState(() => modo = 1),
                            borderRadius: BorderRadius.circular(10),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 8),
                              decoration: BoxDecoration(
                                color: modo == 1 ? AppColors.teal.withValues(alpha: 0.1) : const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: modo == 1 ? AppColors.teal : Colors.transparent),
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.upload_file_rounded, size: 16, color: modo == 1 ? AppColors.teal : const Color(0xFF64748B)),
                                  const SizedBox(width: 6),
                                  Text(
                                    'Archivo / Drive',
                                    style: TextStyle(
                                      fontSize: 12.5,
                                      fontWeight: modo == 1 ? FontWeight.w800 : FontWeight.w600,
                                      color: modo == 1 ? AppColors.teal : const Color(0xFF64748B),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Título / Nombre
                    const Text('Nombre o título:', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5)),
                    const SizedBox(height: 6),
                    TextField(
                      controller: nombreController,
                      decoration: InputDecoration(
                        hintText: destino == 'final' ? 'Ej. Informe Final, Podcast, Video' : 'Ej. Encuesta a docentes',
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                    const SizedBox(height: 12),

                    // URL o Archivo
                    if (modo == 0) ...[
                      const Text('URL o Enlace web:', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5)),
                      const SizedBox(height: 6),
                      TextField(
                        controller: enlaceController,
                        keyboardType: TextInputType.url,
                        decoration: InputDecoration(
                          hintText: 'https://... (YouTube, Canva, Google Drive)',
                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'Asegúrate de que el enlace sea público o accesible ("Cualquier persona con el enlace").',
                        style: TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                      ),
                    ] else ...[
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: const [
                            Row(
                              children: [
                                Icon(Icons.info_outline, size: 16, color: AppColors.teal),
                                SizedBox(width: 6),
                                Text(
                                  'Carga de archivos:',
                                  style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: AppColors.tealDark),
                                ),
                              ],
                            ),
                            SizedBox(height: 6),
                            Text(
                              'Formatos permitidos: PDF, Word (DOCX), imágenes (JPG, PNG), audios (MP3) y videos (MP4).\nPuedes pegar el enlace de tu archivo en Google Drive en la pestaña "Enlace Web" para una entrega instantánea sin límite de peso.',
                              style: TextStyle(fontSize: 11.5, color: Color(0xFF475569), height: 1.35),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: enviando ? null : () => Navigator.pop(dialogCtx),
                  child: const Text('Cancelar'),
                ),
                FilledButton(
                  onPressed: enviando
                      ? null
                      : () async {
                          final nombre = nombreController.text.trim();
                          if (nombre.isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Ingresa un título para el trabajo.')),
                            );
                            return;
                          }

                          final enlace = enlaceController.text.trim();
                          if (modo == 0 && enlace.isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Ingresa el enlace web.')),
                            );
                            return;
                          }

                          setModalState(() => enviando = true);
                          try {
                            final repo = ref.read(taqeRepositoryProvider);
                            final msg = await repo.subirTrabajo(
                              grupoId: grupo.id,
                              nombre: nombre,
                              destino: destino,
                              modo: modo == 0 ? 'enlace' : 'enlace', // en móvil los enlaces directos o drive
                              enlace: enlace.isNotEmpty ? enlace : 'https://drive.google.com',
                            );
                            if (dialogCtx.mounted) Navigator.pop(dialogCtx);
                            ref.invalidate(taqeDataProvider);
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(msg),
                                  backgroundColor: const Color(0xFF10B981),
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            }
                          } catch (e) {
                            setModalState(() => enviando = false);
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text('Error: ${e.toString()}'),
                                  backgroundColor: AppColors.danger,
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            }
                          }
                        },
                  style: FilledButton.styleFrom(
                    backgroundColor: destino == 'final' ? AppColors.teal : AppColors.goldHover,
                  ),
                  child: enviando
                      ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : Text(destino == 'final' ? 'Agregar al final' : 'Agregar evidencia'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _mostrarDialogoInvitar(TaqeGrupo grupo) {
    if (grupo.cursoId == null) return;
    List<TaqeCompanero> companeros = [];
    final Set<int> seleccionados = {};
    bool cargando = true;
    bool enviando = false;

    showDialog(
      context: context,
      builder: (dialogCtx) {
        return StatefulBuilder(
          builder: (ctx, setModalState) {
            if (companeros.isEmpty && cargando) {
              ref.read(taqeRepositoryProvider).fetchCompaneros(grupo.cursoId!).then((lista) {
                setModalState(() {
                  companeros = lista;
                  cargando = false;
                });
              }).catchError((_) {
                setModalState(() => cargando = false);
              });
            }

            return AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              title: const Text('Invitar más compañeros', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
              content: SizedBox(
                width: double.maxFinite,
                child: cargando
                    ? const Center(child: Padding(padding: EdgeInsets.all(20), child: CircularProgressIndicator()))
                    : companeros.isEmpty
                        ? const Padding(
                            padding: EdgeInsets.all(16),
                            child: Text(
                              'No hay más compañeros disponibles para invitar en este curso.',
                              textAlign: TextAlign.center,
                              style: TextStyle(color: Color(0xFF64748B)),
                            ),
                          )
                        : ListView.separated(
                            shrinkWrap: true,
                            itemCount: companeros.length,
                            separatorBuilder: (_, __) => const Divider(height: 1),
                            itemBuilder: (c, idx) {
                              final comp = companeros[idx];
                              final isChecked = seleccionados.contains(comp.id);
                              return CheckboxListTile(
                                dense: true,
                                title: Text(comp.nombre, style: const TextStyle(fontSize: 12.5)),
                                value: isChecked,
                                onChanged: (val) {
                                  setModalState(() {
                                    if (val == true) {
                                      seleccionados.add(comp.id);
                                    } else {
                                      seleccionados.remove(comp.id);
                                    }
                                  });
                                },
                              );
                            },
                          ),
              ),
              actions: [
                TextButton(
                  onPressed: enviando ? null : () => Navigator.pop(dialogCtx),
                  child: const Text('Cancelar'),
                ),
                FilledButton(
                  onPressed: (enviando || seleccionados.isEmpty)
                      ? null
                      : () async {
                          setModalState(() => enviando = true);
                          try {
                            final repo = ref.read(taqeRepositoryProvider);
                            final msg = await repo.agregarIntegrantes(
                              grupoId: grupo.id,
                              integrantes: seleccionados.toList(),
                            );
                            if (dialogCtx.mounted) Navigator.pop(dialogCtx);
                            ref.invalidate(taqeDataProvider);
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(msg),
                                  backgroundColor: const Color(0xFF10B981),
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            }
                          } catch (e) {
                            setModalState(() => enviando = false);
                            if (mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text('Error: ${e.toString()}'),
                                  backgroundColor: AppColors.danger,
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            }
                          }
                        },
                  style: FilledButton.styleFrom(backgroundColor: AppColors.goldHover),
                  child: enviando
                      ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Text('Enviar invitaciones'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _confirmarEntregar(TaqeGrupo grupo) {
    showDialog(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: const Text('¿Entregar trabajo final?', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
        content: Text(
          '¿Estás seguro de entregar el trabajo final del grupo "${grupo.nombre}" con ${grupo.finales.length} elemento(s)? El grupo quedará en estado Finalizado.',
          style: const TextStyle(fontSize: 13, color: Color(0xFF334155)),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogCtx), child: const Text('Cancelar')),
          FilledButton.icon(
            onPressed: () async {
              Navigator.pop(dialogCtx);
              try {
                final repo = ref.read(taqeRepositoryProvider);
                final msg = await repo.entregar(grupoId: grupo.id);
                ref.invalidate(taqeDataProvider);
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text(msg), backgroundColor: const Color(0xFF10B981), behavior: SnackBarBehavior.floating),
                  );
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Error: ${e.toString()}'), backgroundColor: AppColors.danger, behavior: SnackBarBehavior.floating),
                  );
                }
              }
            },
            style: FilledButton.styleFrom(backgroundColor: const Color(0xFF10B981)),
            icon: const Icon(Icons.send_rounded, size: 16),
            label: const Text('Sí, entregar'),
          ),
        ],
      ),
    );
  }

  void _confirmarReabrir(TaqeGrupo grupo) {
    showDialog(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: const Text('¿Reabrir entrega?', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
        content: const Text(
          'El grupo volverá a estar En proceso para corregir o agregar nuevos archivos. Recuerda entregar de nuevo cuando terminen.',
          style: TextStyle(fontSize: 13, color: Color(0xFF334155)),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogCtx), child: const Text('Cancelar')),
          FilledButton.icon(
            onPressed: () async {
              Navigator.pop(dialogCtx);
              try {
                final repo = ref.read(taqeRepositoryProvider);
                final msg = await repo.reabrir(grupoId: grupo.id);
                ref.invalidate(taqeDataProvider);
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text(msg), backgroundColor: AppColors.teal, behavior: SnackBarBehavior.floating),
                  );
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Error: ${e.toString()}'), backgroundColor: AppColors.danger, behavior: SnackBarBehavior.floating),
                  );
                }
              }
            },
            style: FilledButton.styleFrom(backgroundColor: AppColors.teal),
            icon: const Icon(Icons.lock_open_rounded, size: 16),
            label: const Text('Reabrir'),
          ),
        ],
      ),
    );
  }

  void _confirmarSalir(TaqeGrupo grupo) {
    showDialog(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: const Text('¿Salir del grupo?', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
        content: Text(
          '¿Estás seguro de salir de "${grupo.nombre}"? Si eres el organizador, el rol pasará a otro compañero.',
          style: const TextStyle(fontSize: 13, color: Color(0xFF334155)),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogCtx), child: const Text('Cancelar')),
          FilledButton(
            onPressed: () async {
              Navigator.pop(dialogCtx);
              try {
                final repo = ref.read(taqeRepositoryProvider);
                final msg = await repo.salirDelGrupo(grupoId: grupo.id);
                ref.invalidate(taqeDataProvider);
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text(msg), backgroundColor: AppColors.danger, behavior: SnackBarBehavior.floating),
                  );
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Error: ${e.toString()}'), backgroundColor: AppColors.danger, behavior: SnackBarBehavior.floating),
                  );
                }
              }
            },
            style: FilledButton.styleFrom(backgroundColor: AppColors.danger),
            child: const Text('Salir'),
          ),
        ],
      ),
    );
  }

  void _confirmarRetirar(TaqeGrupo grupo, TaqeIntegrante integrante) {
    showDialog(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: const Text('¿Retirar integrante?', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
        content: Text(
          '¿Deseas retirar a ${integrante.nombre} del grupo?',
          style: const TextStyle(fontSize: 13, color: Color(0xFF334155)),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogCtx), child: const Text('Cancelar')),
          FilledButton(
            onPressed: () async {
              Navigator.pop(dialogCtx);
              try {
                final repo = ref.read(taqeRepositoryProvider);
                final msg = await repo.retirarIntegrante(grupoId: grupo.id, integranteId: integrante.id);
                ref.invalidate(taqeDataProvider);
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text(msg), backgroundColor: AppColors.teal, behavior: SnackBarBehavior.floating),
                  );
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Error: ${e.toString()}'), backgroundColor: AppColors.danger, behavior: SnackBarBehavior.floating),
                  );
                }
              }
            },
            style: FilledButton.styleFrom(backgroundColor: AppColors.danger),
            child: const Text('Retirar'),
          ),
        ],
      ),
    );
  }

  void _moverTrabajo(int trabajoId) async {
    try {
      final repo = ref.read(taqeRepositoryProvider);
      final msg = await repo.moverTrabajo(trabajoId: trabajoId);
      ref.invalidate(taqeDataProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(msg), backgroundColor: const Color(0xFF10B981), behavior: SnackBarBehavior.floating),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: ${e.toString()}'), backgroundColor: AppColors.danger, behavior: SnackBarBehavior.floating),
        );
      }
    }
  }

  void _eliminarTrabajo(int trabajoId, String nombre) async {
    final confirmar = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: const Text('¿Eliminar elemento?', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 16)),
        content: Text('¿Seguro que deseas eliminar "$nombre"?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: FilledButton.styleFrom(backgroundColor: AppColors.danger),
            child: const Text('Eliminar'),
          ),
        ],
      ),
    );

    if (confirmar != true) return;

    try {
      final repo = ref.read(taqeRepositoryProvider);
      final msg = await repo.eliminarTrabajo(trabajoId: trabajoId);
      ref.invalidate(taqeDataProvider);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(msg), backgroundColor: const Color(0xFF10B981), behavior: SnackBarBehavior.floating),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: ${e.toString()}'), backgroundColor: AppColors.danger, behavior: SnackBarBehavior.floating),
        );
      }
    }
  }

  void _mostrarDetalleProyecto(TaqeProyecto proyecto) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return Container(
          padding: const EdgeInsets.all(24),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(color: const Color(0xFFCBD5E1), borderRadius: BorderRadius.circular(2)),
                ),
              ),
              const SizedBox(height: 18),
              Row(
                children: [
                  const Icon(Icons.school_outlined, color: AppColors.gold, size: 20),
                  const SizedBox(width: 8),
                  Text(
                    proyecto.ciclo ?? 'Proyecto Integrador',
                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13, color: AppColors.goldHover),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                proyecto.nombre,
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.tealDark),
              ),
              if (proyecto.producto != null && proyecto.producto!.isNotEmpty) ...[
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFFBEB),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFFDE68A)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.inventory_2_outlined, color: Color(0xFFD97706), size: 20),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'Producto esperado: ${proyecto.producto}',
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFF92400E)),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
              if (proyecto.descripcion != null && proyecto.descripcion!.isNotEmpty) ...[
                const SizedBox(height: 16),
                const Text('Descripción y Orientaciones:', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 13.5)),
                const SizedBox(height: 6),
                Text(
                  proyecto.descripcion!,
                  style: const TextStyle(fontSize: 13, color: Color(0xFF334155), height: 1.45),
                ),
              ],
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final taqeAsync = ref.watch(taqeDataProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text("Taq'e · Proyectos"),
        actions: const [PukllaAppBarActions()],
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: taqeAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, stack) => ErrorState(
          message: "No se pudo cargar el espacio Taq'e.",
          onRetry: () => ref.refresh(taqeDataProvider),
        ),
        data: (data) {
          final finalizados = data.misGrupos.where((g) => g.entregado).length;
          final totalEvidencias = data.misGrupos.fold(0, (acc, g) => acc + g.evidencias.length);

          return RefreshIndicator(
            onRefresh: () => ref.refresh(taqeDataProvider.future),
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
              children: [
                // Hero Banner Senior
                _HeroTaqeCard(
                  gruposCount: data.misGrupos.length,
                  evidenciasCount: totalEvidencias,
                  finalizadosCount: finalizados,
                  cursosSinGrupo: data.cursosSinGrupo,
                  onCrearGrupo: () => _mostrarDialogoCrearGrupo(data.cursosSinGrupo),
                  onVerGuia: () => _mostrarDialogoGuia(data.investigadores),
                ),
                const SizedBox(height: 18),

                // Invitaciones Pendientes
                if (data.invitacionesPendientes.isNotEmpty) ...[
                  ...data.invitacionesPendientes.map(
                    (inv) => _InvitacionCard(
                      invitacion: inv,
                      onAceptar: () => _responderInvitacion(inv.id, 'aceptado'),
                      onRechazar: () => _responderInvitacion(inv.id, 'rechazado'),
                    ),
                  ),
                  const SizedBox(height: 16),
                ],

                // Proyecto Integrador del Ciclo (Igual a la web)
                if (data.proyecto != null) ...[
                  _ProyectoIntegradorCard(
                    proyecto: data.proyecto!,
                    onVerDetalle: () => _mostrarDetalleProyecto(data.proyecto!),
                    onVerGuia: () => _mostrarDialogoGuia(data.investigadores),
                  ),
                  const SizedBox(height: 20),
                ],

                // Sección Mis Grupos de Investigación
                Row(
                  children: [
                    const Icon(Icons.groups_outlined, color: AppColors.teal, size: 22),
                    const SizedBox(width: 8),
                    const Text(
                      'Mis grupos de investigación',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.tealDark),
                    ),
                    const Spacer(),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Text(
                        '${data.misGrupos.length}',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Color(0xFF64748B)),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),

                if (data.misGrupos.isEmpty)
                  _EmptyGruposCard(
                    cursosSinGrupo: data.cursosSinGrupo,
                    onCrearGrupo: () => _mostrarDialogoCrearGrupo(data.cursosSinGrupo),
                  )
                else
                  ...data.misGrupos.map(
                    (grupo) => _GrupoCard(
                      grupo: grupo,
                      onPedirAyuda: () => _mostrarDialogoAyuda(grupo),
                      onInvitar: () => _mostrarDialogoInvitar(grupo),
                      onRetirarIntegrante: (integrante) => _confirmarRetirar(grupo, integrante),
                      onSalir: () => _confirmarSalir(grupo),
                      onSubirTrabajo: (destino) => _mostrarDialogoSubirTrabajo(grupo, destino),
                      onEntregar: () => _confirmarEntregar(grupo),
                      onReabrir: () => _confirmarReabrir(grupo),
                      onMoverTrabajo: _moverTrabajo,
                      onEliminarTrabajo: _eliminarTrabajo,
                    ),
                  ),

                const SizedBox(height: 24),

                // Investigadores de Apoyo
                if (data.investigadores.isNotEmpty) ...[
                  _InvestigadoresSection(investigadores: data.investigadores),
                  const SizedBox(height: 32),
                ],
              ],
            ),
          );
        },
      ),
    );
  }
}

class _GuiaPasoItem extends StatelessWidget {
  const _GuiaPasoItem({required this.numero, required this.titulo, required this.descripcion});
  final String numero;
  final String titulo;
  final String descripcion;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 24,
            height: 24,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: AppColors.gold.withValues(alpha: 0.2),
              shape: BoxShape.circle,
            ),
            child: Text(
              numero,
              style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12, color: AppColors.goldHover),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(titulo, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFF1E293B))),
                const SizedBox(height: 2),
                Text(descripcion, style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), height: 1.35)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// Hero Banner con resumen institucional y botones de acción
class _HeroTaqeCard extends StatelessWidget {
  const _HeroTaqeCard({
    required this.gruposCount,
    required this.evidenciasCount,
    required this.finalizadosCount,
    required this.cursosSinGrupo,
    required this.onCrearGrupo,
    required this.onVerGuia,
  });

  final int gruposCount;
  final int evidenciasCount;
  final int finalizadosCount;
  final List<TaqeCursoOpcion> cursosSinGrupo;
  final VoidCallback onCrearGrupo;
  final VoidCallback onVerGuia;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF1E293B), Color(0xFF33445A)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E293B).withValues(alpha: 0.16),
            blurRadius: 18,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.gold.withValues(alpha: 0.22),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.science_outlined, color: AppColors.gold, size: 24),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      "Espacio Taq'e",
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 17.5,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 0.2,
                      ),
                    ),
                    Text(
                      'Ferias de Investigación & Proyectos',
                      style: TextStyle(
                        color: Color(0xFF94A3B8),
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          const Text(
            'Forma tu equipo, registra las evidencias de campo y presenta tu trabajo integrador final.',
            style: TextStyle(color: Color(0xFFE2E8F0), fontSize: 13, height: 1.4),
          ),
          if (gruposCount > 0) ...[
            const SizedBox(height: 14),
            Wrap(
              spacing: 8,
              runSpacing: 6,
              children: [
                _StatChip(icon: Icons.groups, label: '$gruposCount ${gruposCount == 1 ? "grupo" : "grupos"}'),
                _StatChip(icon: Icons.imagesearch_roller_outlined, label: '$evidenciasCount evidencias'),
                _StatChip(
                  icon: Icons.flag_circle_outlined,
                  label: '$finalizadosCount finalizados',
                  isSuccess: finalizadosCount > 0,
                ),
              ],
            ),
          ],
          const SizedBox(height: 16),
          Row(
            children: [
              if (cursosSinGrupo.isNotEmpty)
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: onCrearGrupo,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.goldHover,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 11),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 0,
                    ),
                    icon: const Icon(Icons.add_rounded, size: 18),
                    label: Text(
                      gruposCount == 0 ? 'Crear mi grupo' : 'Nuevo grupo',
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                    ),
                  ),
                ),
              if (cursosSinGrupo.isNotEmpty) const SizedBox(width: 8),
              OutlinedButton.icon(
                onPressed: onVerGuia,
                style: OutlinedButton.styleFrom(
                  foregroundColor: Colors.white,
                  side: const BorderSide(color: Color(0xFF64748B)),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                icon: const Icon(Icons.route_outlined, size: 16),
                label: const Text('Guía rápida', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5)),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _StatChip extends StatelessWidget {
  const _StatChip({
    required this.icon,
    required this.label,
    this.isSuccess = false,
  });

  final IconData icon;
  final String label;
  final bool isSuccess;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: isSuccess
            ? const Color(0xFF10B981).withValues(alpha: 0.22)
            : Colors.white.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(
          color: isSuccess ? const Color(0xFF10B981).withValues(alpha: 0.4) : Colors.white.withValues(alpha: 0.18),
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: isSuccess ? const Color(0xFF34D399) : Colors.white),
          const SizedBox(width: 5),
          Text(
            label,
            style: TextStyle(
              fontSize: 11.5,
              fontWeight: FontWeight.w700,
              color: isSuccess ? const Color(0xFF34D399) : Colors.white,
            ),
          ),
        ],
      ),
    );
  }
}

/// Tarjeta de invitación pendiente
class _InvitacionCard extends StatelessWidget {
  const _InvitacionCard({
    required this.invitacion,
    required this.onAceptar,
    required this.onRechazar,
  });

  final TaqeInvitacion invitacion;
  final VoidCallback onAceptar;
  final VoidCallback onRechazar;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFF0FDF4),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFBBF7D0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF16A34A).withValues(alpha: 0.06),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: const BoxDecoration(
                  color: Color(0xFFDCFCE7),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.mail_outline, color: Color(0xFF16A34A), size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Te invitaron al grupo "${invitacion.grupoNombre}"',
                      style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5, color: Color(0xFF14532D)),
                    ),
                    Text(
                      '${invitacion.cursoNombre} · De: ${invitacion.creadorNombre}',
                      style: const TextStyle(fontSize: 12, color: Color(0xFF15803D)),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              OutlinedButton.icon(
                onPressed: onRechazar,
                style: OutlinedButton.styleFrom(
                  foregroundColor: const Color(0xFF64748B),
                  side: const BorderSide(color: Color(0xFFCBD5E1)),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                icon: const Icon(Icons.close, size: 16),
                label: const Text('Rechazar', style: TextStyle(fontSize: 12.5)),
              ),
              const SizedBox(width: 8),
              FilledButton.icon(
                onPressed: onAceptar,
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF16A34A),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                icon: const Icon(Icons.check, size: 16),
                label: const Text('Aceptar invitación', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700)),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

/// Tarjeta del Proyecto Integrador del ciclo (Espejo de la web)
class _ProyectoIntegradorCard extends StatelessWidget {
  const _ProyectoIntegradorCard({
    required this.proyecto,
    required this.onVerDetalle,
    required this.onVerGuia,
  });

  final TaqeProyecto proyecto;
  final VoidCallback onVerDetalle;
  final VoidCallback onVerGuia;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E293B).withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppColors.teal.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.account_tree_outlined, color: AppColors.teal, size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Tu Proyecto Integrador · ${proyecto.ciclo ?? "Ciclo actual"}',
                      style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B), fontWeight: FontWeight.w600),
                    ),
                    Text(
                      proyecto.nombre,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w800, color: AppColors.tealDark),
                    ),
                  ],
                ),
              ),
            ],
          ),
          if (proyecto.producto != null && proyecto.producto!.isNotEmpty) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: const Color(0xFFFFFBEB),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFFDE68A)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.inventory_2_outlined, size: 15, color: Color(0xFFD97706)),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'Producto: ${proyecto.producto}',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Color(0xFF92400E)),
                    ),
                  ),
                ],
              ),
            ),
          ],
          const SizedBox(height: 12),
          Row(
            children: [
              TextButton.icon(
                onPressed: onVerDetalle,
                icon: const Icon(Icons.menu_book_outlined, size: 16),
                label: const Text('Ver descripción', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700)),
                style: TextButton.styleFrom(
                  foregroundColor: AppColors.teal,
                  visualDensity: VisualDensity.compact,
                ),
              ),
              const Spacer(),
              TextButton.icon(
                onPressed: onVerGuia,
                icon: const Icon(Icons.route_outlined, size: 16),
                label: const Text('¿Cómo funciona?', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700)),
                style: TextButton.styleFrom(
                  foregroundColor: AppColors.goldHover,
                  visualDensity: VisualDensity.compact,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

/// Estado vacío si no tiene grupos
class _EmptyGruposCard extends StatelessWidget {
  const _EmptyGruposCard({
    required this.cursosSinGrupo,
    required this.onCrearGrupo,
  });

  final List<TaqeCursoOpcion> cursosSinGrupo;
  final VoidCallback onCrearGrupo;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 28),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Center(
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: const BoxDecoration(
                color: Color(0xFFF1F5F9),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.biotech_outlined, color: Color(0xFF64748B), size: 36),
            ),
            const SizedBox(height: 14),
            const Text(
              "Aún no perteneces a ningún grupo Taq'e este periodo",
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800, color: Color(0xFF1E293B)),
            ),
            const SizedBox(height: 6),
            const Text(
              'Crea uno para un curso y suma a tus compañeros, o espera su invitación en tu bandeja.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 12.5, color: Color(0xFF64748B), height: 1.35),
            ),
            if (cursosSinGrupo.isNotEmpty) ...[
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: onCrearGrupo,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.goldHover,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 11),
                ),
                icon: const Icon(Icons.add_rounded, size: 18),
                label: const Text('Crear mi primer grupo', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

/// Tarjeta completa de Grupo con Miembros, Acciones, Evidencias y Trabajo Final
class _GrupoCard extends StatefulWidget {
  const _GrupoCard({
    required this.grupo,
    required this.onPedirAyuda,
    required this.onInvitar,
    required this.onRetirarIntegrante,
    required this.onSalir,
    required this.onSubirTrabajo,
    required this.onEntregar,
    required this.onReabrir,
    required this.onMoverTrabajo,
    required this.onEliminarTrabajo,
  });

  final TaqeGrupo grupo;
  final VoidCallback onPedirAyuda;
  final VoidCallback onInvitar;
  final ValueChanged<TaqeIntegrante> onRetirarIntegrante;
  final VoidCallback onSalir;
  final ValueChanged<String> onSubirTrabajo;
  final VoidCallback onEntregar;
  final VoidCallback onReabrir;
  final ValueChanged<int> onMoverTrabajo;
  final void Function(int id, String nombre) onEliminarTrabajo;

  @override
  State<_GrupoCard> createState() => _GrupoCardState();
}

class _GrupoCardState extends State<_GrupoCard> {
  int _archivosTab = 0; // 0: Evidencias, 1: Trabajo Final

  @override
  Widget build(BuildContext context) {
    final grupo = widget.grupo;
    final esFinalizado = grupo.entregado;
    final esOrganizador = grupo.esOrganizador;

    return Container(
      margin: const EdgeInsets.only(bottom: 18),
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E293B).withValues(alpha: 0.05),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Cabecera: Nombre y Estado
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.teal.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.biotech_outlined, color: AppColors.teal, size: 24),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      grupo.nombre,
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.tealDark),
                    ),
                    Text(
                      grupo.cursoNombre,
                      style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: esFinalizado ? const Color(0xFFE6F4EC) : const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(999),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      esFinalizado ? Icons.check_circle : Icons.autorenew,
                      size: 13,
                      color: esFinalizado ? const Color(0xFF0B7A43) : const Color(0xFF1D4ED8),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      esFinalizado ? 'Finalizado' : 'En proceso',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: esFinalizado ? const Color(0xFF0B7A43) : const Color(0xFF1D4ED8),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // ── PASOS DE AVANCE INSTITUCIONAL (Igual que taqe._pasos) ──
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Row(
              children: [
                _PasoAvanceItem(
                  numero: '1',
                  label: 'Equipo',
                  completado: grupo.integrantes.isNotEmpty,
                  activo: !esFinalizado && grupo.evidencias.isEmpty,
                ),
                const Expanded(child: Divider(color: Color(0xFFCBD5E1), thickness: 1)),
                _PasoAvanceItem(
                  numero: '2',
                  label: 'Evidencias',
                  completado: grupo.evidencias.isNotEmpty,
                  activo: !esFinalizado && grupo.evidencias.isNotEmpty,
                ),
                const Expanded(child: Divider(color: Color(0xFFCBD5E1), thickness: 1)),
                _PasoAvanceItem(
                  numero: '3',
                  label: 'Final',
                  completado: esFinalizado,
                  activo: esFinalizado,
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Equipo de Integrantes
          Row(
            children: [
              const Icon(Icons.people_alt_outlined, size: 16, color: Color(0xFF64748B)),
              const SizedBox(width: 6),
              Text(
                'Equipo · ${grupo.integrantes.length} ${grupo.integrantes.length == 1 ? "integrante" : "integrantes"}',
                style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: Color(0xFF475569)),
              ),
              const Spacer(),
              if (esOrganizador && !esFinalizado)
                TextButton.icon(
                  onPressed: widget.onInvitar,
                  icon: const Icon(Icons.person_add_alt_1_outlined, size: 15),
                  label: const Text('Invitar', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                  style: TextButton.styleFrom(foregroundColor: AppColors.teal, visualDensity: VisualDensity.compact),
                ),
              TextButton.icon(
                onPressed: widget.onPedirAyuda,
                icon: const Icon(Icons.support_agent_outlined, size: 15),
                label: const Text('Ayuda', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                style: TextButton.styleFrom(foregroundColor: AppColors.goldHover, visualDensity: VisualDensity.compact),
              ),
            ],
          ),
          const SizedBox(height: 8),

          // Lista de miembros con roles y opción de retirar para el organizador
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: grupo.integrantes.map((m) {
              return Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircleAvatar(
                      radius: 11,
                      backgroundColor: m.esOrganizador ? AppColors.gold : AppColors.teal,
                      child: Text(
                        m.nombre.isNotEmpty ? m.nombre[0].toUpperCase() : '?',
                        style: const TextStyle(fontSize: 10, color: Colors.white, fontWeight: FontWeight.w800),
                      ),
                    ),
                    const SizedBox(width: 6),
                    Text(
                      m.nombre,
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF1E293B)),
                    ),
                    if (m.esOrganizador) ...[
                      const SizedBox(width: 5),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                        decoration: BoxDecoration(
                          color: AppColors.gold.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Text(
                          'Org.',
                          style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w800, color: AppColors.goldHover),
                        ),
                      ),
                    ],
                    if (m.estadoInvitacion == 'pendiente') ...[
                      const SizedBox(width: 5),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEFF6FF),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Text(
                          'Invitado',
                          style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w700, color: Color(0xFF2563EB)),
                        ),
                      ),
                    ],
                    if (esOrganizador && !m.esOrganizador && !esFinalizado) ...[
                      const SizedBox(width: 4),
                      InkWell(
                        onTap: () => widget.onRetirarIntegrante(m),
                        child: const Padding(
                          padding: EdgeInsets.all(2),
                          child: Icon(Icons.close_rounded, size: 14, color: Color(0xFFEF4444)),
                        ),
                      ),
                    ],
                  ],
                ),
              );
            }).toList(),
          ),

          // Botón para salir del grupo
          Align(
            alignment: Alignment.centerRight,
            child: TextButton.icon(
              onPressed: widget.onSalir,
              icon: const Icon(Icons.exit_to_app_rounded, size: 14),
              label: const Text('Salir del grupo', style: TextStyle(fontSize: 11.5, color: Color(0xFFDC2626))),
            ),
          ),

          const Divider(height: 1, color: Color(0xFFF1F5F9)),
          const SizedBox(height: 14),

          // Pestañas de Archivos: Evidencias vs Trabajo Final
          Row(
            children: [
              Expanded(
                child: _PestanaArchivo(
                  titulo: 'Evidencias (${grupo.evidencias.length})',
                  icon: Icons.photo_library_outlined,
                  isSelected: _archivosTab == 0,
                  onTap: () => setState(() => _archivosTab = 0),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _PestanaArchivo(
                  titulo: 'Trabajo Final (${grupo.finales.length})',
                  icon: Icons.assignment_turned_in_outlined,
                  isSelected: _archivosTab == 1,
                  onTap: () => setState(() => _archivosTab = 1),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Botón para agregar trabajo (si no está entregado)
          if (!esFinalizado)
            Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () => widget.onSubirTrabajo(_archivosTab == 0 ? 'evidencia' : 'final'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: _archivosTab == 0 ? AppColors.goldHover : AppColors.teal,
                    side: BorderSide(color: _archivosTab == 0 ? AppColors.goldHover : AppColors.teal),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  icon: const Icon(Icons.add_rounded, size: 18),
                  label: Text(
                    _archivosTab == 0 ? 'Subir o enlazar evidencia' : 'Agregar al trabajo final',
                    style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 12.5),
                  ),
                ),
              ),
            ),

          // Lista de archivos según pestaña
          if (_archivosTab == 0)
            _ListaArchivos(
              archivos: grupo.evidencias,
              tipoVacio: 'evidencias',
              esFinalizado: esFinalizado,
              onMover: widget.onMoverTrabajo,
              onEliminar: widget.onEliminarTrabajo,
            )
          else ...[
            _ListaArchivos(
              archivos: grupo.finales,
              tipoVacio: 'entregables finales',
              esFinalizado: esFinalizado,
              onMover: widget.onMoverTrabajo,
              onEliminar: widget.onEliminarTrabajo,
            ),
            const SizedBox(height: 14),

            // ── CAJA DE ENTREGA INSTITUCIONAL ──
            _CajaEntrega(
              grupo: grupo,
              onEntregar: widget.onEntregar,
              onReabrir: widget.onReabrir,
            ),
          ],
        ],
      ),
    );
  }
}

class _PasoAvanceItem extends StatelessWidget {
  const _PasoAvanceItem({
    required this.numero,
    required this.label,
    required this.completado,
    required this.activo,
  });

  final String numero;
  final String label;
  final bool completado;
  final bool activo;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 20,
          height: 20,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: completado
                ? const Color(0xFF10B981)
                : activo
                    ? AppColors.goldHover
                    : const Color(0xFFCBD5E1),
            shape: BoxShape.circle,
          ),
          child: completado
              ? const Icon(Icons.check, size: 13, color: Colors.white)
              : Text(
                  numero,
                  style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: Colors.white),
                ),
        ),
        const SizedBox(width: 5),
        Text(
          label,
          style: TextStyle(
            fontSize: 11,
            fontWeight: completado || activo ? FontWeight.w800 : FontWeight.w600,
            color: completado
                ? const Color(0xFF10B981)
                : activo
                    ? const Color(0xFF0F172A)
                    : const Color(0xFF94A3B8),
          ),
        ),
      ],
    );
  }
}

class _CajaEntrega extends StatelessWidget {
  const _CajaEntrega({
    required this.grupo,
    required this.onEntregar,
    required this.onReabrir,
  });

  final TaqeGrupo grupo;
  final VoidCallback onEntregar;
  final VoidCallback onReabrir;

  @override
  Widget build(BuildContext context) {
    final entregado = grupo.entregado;
    final esOrganizador = grupo.esOrganizador;

    if (entregado) {
      return Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: const Color(0xFFF0FDF4),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFFBBF7D0)),
        ),
        child: Column(
          children: [
            Row(
              children: [
                const Icon(Icons.check_circle_rounded, color: Color(0xFF16A34A), size: 20),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Entregado ${grupo.entregadoAt != null ? "el " + grupo.entregadoAt! : ""}. Archivos bloqueados.',
                    style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: Color(0xFF166534)),
                  ),
                ),
              ],
            ),
            if (esOrganizador) ...[
              const SizedBox(height: 8),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: onReabrir,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: const Color(0xFF15803D),
                    side: const BorderSide(color: Color(0xFF86EFAC)),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                  icon: const Icon(Icons.lock_open_rounded, size: 16),
                  label: const Text('Reabrir entrega para correcciones', style: TextStyle(fontSize: 12)),
                ),
              ),
            ],
          ],
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFEFF6FF),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFBFDBFE)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: const [
              Icon(Icons.info_outline_rounded, color: Color(0xFF1D4ED8), size: 18),
              SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Cuando el trabajo final esté completo, el organizador debe entregarlo para formalizar la presentación.',
                  style: TextStyle(fontSize: 12, color: Color(0xFF1E40AF), height: 1.35),
                ),
              ),
            ],
          ),
          if (esOrganizador) ...[
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: grupo.finales.isEmpty ? null : onEntregar,
                style: FilledButton.styleFrom(
                  backgroundColor: AppColors.goldHover,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                icon: const Icon(Icons.send_rounded, size: 16),
                label: Text(
                  grupo.finales.isEmpty ? 'Agrega archivos antes de entregar' : 'Entregar trabajo final',
                  style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _PestanaArchivo extends StatelessWidget {
  const _PestanaArchivo({
    required this.titulo,
    required this.icon,
    required this.isSelected,
    required this.onTap,
  });

  final String titulo;
  final IconData icon;
  final bool isSelected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 8),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.teal.withValues(alpha: 0.1) : const Color(0xFFF8FAFC),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: isSelected ? AppColors.teal : const Color(0xFFE2E8F0),
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 15, color: isSelected ? AppColors.teal : const Color(0xFF64748B)),
            const SizedBox(width: 6),
            Flexible(
              child: Text(
                titulo,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 11.5,
                  fontWeight: isSelected ? FontWeight.w700 : FontWeight.w600,
                  color: isSelected ? AppColors.teal : const Color(0xFF64748B),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ListaArchivos extends StatelessWidget {
  const _ListaArchivos({
    required this.archivos,
    required this.tipoVacio,
    required this.esFinalizado,
    required this.onMover,
    required this.onEliminar,
  });

  final List<TaqeArchivo> archivos;
  final String tipoVacio;
  final bool esFinalizado;
  final ValueChanged<int> onMover;
  final void Function(int id, String nombre) onEliminar;

  IconData _iconForType(String tipo) {
    switch (tipo.toLowerCase()) {
      case 'imagen':
        return Icons.image_outlined;
      case 'pdf':
        return Icons.picture_as_pdf_outlined;
      case 'word':
        return Icons.description_outlined;
      case 'audio':
        return Icons.audiotrack_outlined;
      case 'video':
        return Icons.videocam_outlined;
      case 'enlace':
        return Icons.link_outlined;
      default:
        return Icons.insert_drive_file_outlined;
    }
  }

  Color _colorForType(String tipo) {
    switch (tipo.toLowerCase()) {
      case 'imagen':
        return const Color(0xFF10B981);
      case 'pdf':
        return const Color(0xFFEF4444);
      case 'word':
        return const Color(0xFF2563EB);
      case 'audio':
        return const Color(0xFF8B5CF6);
      case 'video':
        return const Color(0xFFF59E0B);
      case 'enlace':
        return const Color(0xFF0EA5E9);
      default:
        return const Color(0xFF64748B);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (archivos.isEmpty) {
      return Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        alignment: Alignment.center,
        child: Text(
          'No se han registrado $tipoVacio aún.',
          style: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8), fontStyle: FontStyle.italic),
        ),
      );
    }

    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: archivos.length,
      separatorBuilder: (_, __) => const Divider(height: 12, color: Color(0xFFF1F5F9)),
      itemBuilder: (context, index) {
        final a = archivos[index];
        final color = _colorForType(a.tipoIcono);

        return Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(_iconForType(a.tipoIcono), size: 18, color: color),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: InkWell(
                onTap: () {
                  if (a.url != null && a.url!.isNotEmpty) {
                    launchUrl(Uri.parse(a.url!), mode: LaunchMode.externalApplication);
                  }
                },
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      a.nombre,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: a.url != null ? const Color(0xFF0284C7) : const Color(0xFF1E293B),
                        decoration: a.url != null ? TextDecoration.underline : null,
                      ),
                    ),
                    Text(
                      '${a.subidoPor ?? "Autor"} · ${a.fecha ?? ""}',
                      style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                    ),
                  ],
                ),
              ),
            ),
            if (!esFinalizado && a.puedeMover)
              IconButton(
                icon: const Icon(Icons.swap_horiz_rounded, size: 18, color: AppColors.teal),
                tooltip: 'Mover entre evidencia y trabajo final',
                onPressed: () => onMover(a.id),
              ),
            if (!esFinalizado && a.puedeEliminar)
              IconButton(
                icon: const Icon(Icons.delete_outline_rounded, size: 18, color: Color(0xFFEF4444)),
                tooltip: 'Eliminar',
                onPressed: () => onEliminar(a.id, a.nombre),
              ),
          ],
        );
      },
    );
  }
}

/// Sección de Investigadores de Apoyo
class _InvestigadoresSection extends StatelessWidget {
  const _InvestigadoresSection({required this.investigadores});

  final List<TaqeInvestigador> investigadores;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: const [
            Icon(Icons.contact_support_outlined, color: AppColors.teal, size: 18),
            SizedBox(width: 8),
            Text(
              'Docentes Investigadores de Apoyo',
              style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w800, color: AppColors.tealDark),
            ),
          ],
        ),
        const SizedBox(height: 10),
        ...investigadores.map(
          (inv) => Card(
            margin: const EdgeInsets.only(bottom: 8),
            elevation: 0,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
              side: const BorderSide(color: Color(0xFFE2E8F0)),
            ),
            child: ListTile(
              dense: true,
              leading: const CircleAvatar(
                radius: 16,
                backgroundColor: Color(0xFFEFF6FF),
                child: Icon(Icons.school, size: 18, color: Color(0xFF1D4ED8)),
              ),
              title: Text(inv.nombre, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13)),
              subtitle: inv.email != null ? Text(inv.email!, style: const TextStyle(fontSize: 11.5)) : null,
              trailing: inv.email != null
                  ? IconButton(
                      icon: const Icon(Icons.mail_outline, size: 18, color: AppColors.teal),
                      onPressed: () => launchUrl(Uri.parse('mailto:${inv.email}')),
                    )
                  : null,
            ),
          ),
        ),
      ],
    );
  }
}
