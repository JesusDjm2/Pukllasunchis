import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_preloader.dart';
import '../application/titulacion_providers.dart';
import '../data/solicitud_formulario.dart';
import '../data/titulacion_repository.dart';
import 'titulacion_widgets.dart';

const _declaracion = 'Declaración de veracidad. Declaro bajo juramento que la información registrada expresa la verdad.';
const _ayudaComprobante = 'El comprobante lo emite la responsable de pagos de la EESPP (llega a tu correo dentro de dos '
    'días hábiles del pago); no vale el voucher de Caja Cusco.';

/// Formulario nativo de una solicitud de titulación (PTI, carta u originalidad):
/// mismos campos, reglas y textos que `/mi-titulacion/*` en la web.
class SolicitudTitulacionScreen extends ConsumerWidget {
  const SolicitudTitulacionScreen({super.key, required this.tipo});

  final TipoSolicitud tipo;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(solicitudFormularioProvider(tipo));

    return Scaffold(
      backgroundColor: PukllaDashboardStyles.backgroundColor(context),
      appBar: AppBar(
        title: Text(tipo.titulo, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 18)),
        elevation: 0,
      ),
      body: async.when(
        loading: () => const PukllaPreloader.inline(mensaje: 'Cargando el formulario...'),
        error: (e, _) => ErrorState(
          message: e is ApiException ? e.message : 'No se pudo cargar el formulario.',
          onRetry: () => ref.invalidate(solicitudFormularioProvider(tipo)),
        ),
        data: (form) => _Formulario(tipo: tipo, form: form),
      ),
    );
  }
}

class _Formulario extends ConsumerStatefulWidget {
  const _Formulario({required this.tipo, required this.form});

  final TipoSolicitud tipo;
  final SolicitudFormulario form;

  @override
  ConsumerState<_Formulario> createState() => _FormularioState();
}

class _FormularioState extends ConsumerState<_Formulario> {
  final _formKey = GlobalKey<FormState>();
  late final Map<String, TextEditingController> _texto;
  late final Map<String, String?> _eleccion;
  final Map<String, List<ArchivoElegido>> _archivos = {};
  bool _declaracion = false;
  bool _enviando = false;
  Map<String, String> _errores = {};

  SolicitudFormulario get f => widget.form;
  TipoSolicitud get tipo => widget.tipo;

  /// Campos de texto y de opción de cada solicitud (los nombres son los de la API).
  static const _camposTexto = {
    TipoSolicitud.pti: ['integrante2', 'integrante3', 'celulares', 'titulo'],
    TipoSolicitud.carta: ['grupo', 'celular', 'institucion', 'director', 'titulo'],
    TipoSolicitud.originalidad: ['titulo', 'celulares', 'domicilio', 'fecha_pago'],
  };
  static const _camposOpcion = {
    TipoSolicitud.pti: ['programa_estudios', 'ciclo', 'area', 'linea'],
    TipoSolicitud.carta: ['programa_estudios', 'programa_formacion'],
    TipoSolicitud.originalidad: ['etapa'],
  };

  /// Archivos: campo → si admite varios.
  static const _camposArchivo = {
    TipoSolicitud.pti: {'pti': false, 'comprobantes': true},
    TipoSolicitud.carta: {'comprobante': false},
    TipoSolicitud.originalidad: {'documento': false, 'comprobantes': true},
  };

  @override
  void initState() {
    super.initState();
    _texto = {for (final c in _camposTexto[tipo]!) c: TextEditingController(text: f.valores[c] ?? '')};
    _eleccion = {
      for (final c in _camposOpcion[tipo]!) c: (f.valores[c]?.isNotEmpty ?? false) ? f.valores[c] : null,
    };
  }

  @override
  void dispose() {
    for (final c in _texto.values) {
      c.dispose();
    }
    super.dispose();
  }

  bool get _bloqueado => !f.puedeEnviar || (tipo == TipoSolicitud.originalidad && !f.habilitada);

  // ── Envío ──

  Future<void> _enviar() async {
    setState(() => _errores = {});
    final validoLocal = _formKey.currentState!.validate();
    final faltan = <String, String>{
      for (final e in _camposArchivo[tipo]!.entries)
        if ((_archivos[e.key] ?? const []).isEmpty) e.key: 'Adjunta este archivo.',
      if (!_declaracion) 'declaracion': 'Debes aceptar la declaración de veracidad.',
    };
    if (!validoLocal || faltan.isNotEmpty) {
      setState(() => _errores = faltan);
      _aviso('Revisa los campos marcados.', error: true);
      return;
    }

    setState(() => _enviando = true);
    try {
      final r = await ref.read(titulacionRepositoryProvider).enviar(
        tipo,
        campos: {
          for (final e in _texto.entries)
            if (e.value.text.trim().isNotEmpty) e.key: e.value.text.trim(),
          for (final e in _eleccion.entries)
            if (e.value != null) e.key: e.value!,
          'declaracion': '1',
        },
        archivos: _archivos,
        multiples: {
          for (final e in _camposArchivo[tipo]!.entries)
            if (e.value) e.key,
        },
      );
      ref.invalidate(titulacionProvider);
      ref.invalidate(solicitudFormularioProvider(tipo));
      if (!mounted) return;
      await showDialog<void>(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          icon: const Icon(Icons.check_circle_rounded, color: Color(0xFF16A34A), size: 40),
          title: const Text('Solicitud enviada', style: TextStyle(fontWeight: FontWeight.w800)),
          content: Text(r.mensaje, style: const TextStyle(height: 1.4)),
          actions: [FilledButton(onPressed: () => Navigator.pop(ctx), child: const Text('Entendido'))],
        ),
      );
      if (mounted) context.pop();
    } on ApiException catch (e) {
      // 422: errores por campo ("comprobantes.0" → "comprobantes").
      setState(() => _errores = {
            for (final entry in (e.errors ?? const <String, List<String>>{}).entries)
              entry.key.split('.').first: entry.value.first,
          });
      _aviso(e.message, error: true);
    } catch (_) {
      _aviso('No se pudo enviar la solicitud. Revisa tu conexión e inténtalo de nuevo.', error: true);
    } finally {
      if (mounted) setState(() => _enviando = false);
    }
  }

  void _aviso(String texto, {bool error = false}) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(texto),
      backgroundColor: error ? AppColors.danger : null,
      behavior: SnackBarBehavior.floating,
    ));
  }

  Future<void> _elegirArchivo(String campo, bool multiple) async {
    final regla = f.archivos[campo];
    final r = await FilePicker.pickFiles(
      type: FileType.custom,
      allowedExtensions: regla?.extensiones ?? const ['pdf'],
      allowMultiple: multiple,
      withData: true,
    );
    if (r == null) return;

    final actuales = [...?_archivos[campo]];
    for (final file in r.files) {
      if (file.bytes == null) continue;
      final a = ArchivoElegido(nombre: file.name, bytes: file.bytes!);
      if (regla != null && a.mb > regla.maxMb) {
        _aviso('«${a.nombre}» pesa ${a.mb.toStringAsFixed(1)} MB: el máximo es ${regla.maxMb} MB.', error: true);
        continue;
      }
      if (multiple) {
        actuales.add(a);
      } else {
        actuales
          ..clear()
          ..add(a);
      }
    }
    final max = regla?.maxArchivos ?? 1;
    if (actuales.length > max) {
      _aviso('Puedes adjuntar hasta $max archivos.', error: true);
      actuales.removeRange(max, actuales.length);
    }
    setState(() {
      _archivos[campo] = actuales;
      _errores.remove(campo);
    });
  }

  // ── Construcción ──

  @override
  Widget build(BuildContext context) {
    return Form(
      key: _formKey,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
        children: [
          if (f.ultima != null) _EstadoUltima(ultima: f.ultima!, tipo: tipo),
          if (tipo == TipoSolicitud.originalidad && !f.habilitada)
            _Bloqueo(
              texto: f.motivoNoPuede ?? 'Por ahora se solicita con el formulario de Google.',
              boton: f.formularioExterno != null ? 'Abrir formulario de Google' : null,
              onBoton: f.formularioExterno != null ? () => abrirEnlaceTitulacion(context, f.formularioExterno!) : null,
            )
          else if (!f.puedeEnviar)
            _Bloqueo(texto: f.motivoNoPuede!),
          if (!_bloqueado) ...[
            _pago(),
            ..._campos(),
            const SizedBox(height: 8),
            _CheckDeclaracion(
              valor: _declaracion,
              error: _errores['declaracion'],
              onChanged: (v) => setState(() {
                _declaracion = v;
                _errores.remove('declaracion');
              }),
            ),
            const SizedBox(height: 16),
            FilledButton.icon(
              onPressed: _enviando ? null : _enviar,
              icon: _enviando
                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Icon(Icons.send_rounded, size: 18),
              label: Text(_enviando ? 'Enviando...' : (f.ultima?.observada == true ? 'Corregir y enviar' : 'Enviar solicitud')),
              style: FilledButton.styleFrom(
                backgroundColor: AppColors.goldHover,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                textStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _pago() {
    final pago = tipo == TipoSolicitud.originalidad ? f.pagos[_eleccion['etapa'] ?? 'ti'] : f.pago;
    if (pago == null) return const SizedBox.shrink();
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFB91C1C).withValues(alpha: 0.07),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFB91C1C).withValues(alpha: 0.25)),
      ),
      child: Row(
        children: [
          const Icon(Icons.payments_outlined, color: Color(0xFFB91C1C)),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              'Antes de enviar, paga en Caja Cusco (pago ordinario): ${pago.concepto} · código ${pago.codigo} · S/ ${pago.monto}',
              style: TextStyle(fontSize: 12.5, height: 1.35, color: PukllaDashboardStyles.textPrimary(context)),
            ),
          ),
        ],
      ),
    );
  }

  List<Widget> _campos() {
    switch (tipo) {
      case TipoSolicitud.pti:
        final area = _eleccion['area'];
        final lineas = (f.opciones['lineas'] ?? const <Opcion>[]).where((l) => area != null && l.valor.startsWith('$area-')).toList();
        return [
          _Seccion('Integrantes'),
          _Fijo(etiqueta: 'Integrante 1 (tú)', valor: f.solicitante ?? ''),
          _campoTexto('integrante2', 'Integrante 2 (opcional)', ayuda: 'Apellidos y nombres completos', opcional: true, min: 5),
          _campoTexto('integrante3', 'Integrante 3 (opcional)', ayuda: 'Apellidos y nombres completos', opcional: true, min: 5),
          _campoTexto('celulares', 'Números de contacto', ayuda: 'De todos los integrantes, separados por coma', teclado: TextInputType.phone, min: 9),
          _Seccion('Programa'),
          _opcion('programa_estudios', 'Programa de estudios', f.opciones['programas_estudios']),
          _opcion('ciclo', 'Ciclo o programa', f.opciones['ciclos']),
          _Seccion('Tu investigación'),
          _opcion('area', 'Área de investigación', f.opciones['areas'], alCambiar: (_) => _eleccion['linea'] = null),
          _opcion('linea', 'Línea de investigación', lineas, ayudaVacio: 'Elige primero el área'),
          _campoTexto('titulo', 'Título del PTI', lineas: 3, min: 10),
          _Seccion('Archivos'),
          _archivo('pti', 'Plan de Trabajo de Investigación (PTI)', false),
          _archivo('comprobantes', 'Comprobante de pago', true, ayuda: _ayudaComprobante),
        ];
      case TipoSolicitud.carta:
        return [
          _Seccion('Tu grupo'),
          _campoTexto('grupo', 'Grupo de investigación', ayuda: 'Nombres de los integrantes', lineas: 2, min: 5),
          _campoTexto('celular', 'Celular', teclado: TextInputType.phone, regex: RegExp(r'^\+?[\d\s]{9,15}$'), mensajeRegex: 'Escribe un número de celular válido (9 dígitos).'),
          _opcion('programa_estudios', 'Programa de estudios', f.opciones['programas_estudios']),
          _opcion('programa_formacion', 'Programa de formación', f.opciones['programas_formacion']),
          _Seccion('Institución Educativa'),
          _campoTexto('institucion', 'Denominación de la institución educativa', min: 5),
          _campoTexto('director', 'Nombre del director(a)', min: 5),
          _campoTexto('titulo', 'Título del trabajo de investigación', lineas: 3, min: 10),
          _Seccion('Pago'),
          _archivo('comprobante', 'Comprobante de pago', false, ayuda: 'Comprobante, no voucher.'),
        ];
      case TipoSolicitud.originalidad:
        return [
          if (f.tesistas.isNotEmpty) ...[
            _Seccion('Tesistas'),
            _Fijo(etiqueta: 'Según tu grupo en el sistema', valor: f.tesistas.join('\n')),
          ],
          _Seccion('Documento'),
          _opcion('etapa', 'Documento a revisar', f.opciones['etapas'], alCambiar: (v) {
            final sugerido = f.titulos[v];
            if (sugerido != null && sugerido.isNotEmpty) _texto['titulo']!.text = sugerido;
          }),
          _campoTexto('titulo', 'Título del documento', lineas: 3, min: 10),
          _campoTexto('celulares', 'Números de celular', teclado: TextInputType.phone, min: 9),
          _campoTexto('domicilio', 'Domicilio (opcional)', opcional: true),
          _fecha('fecha_pago', 'Fecha de pago'),
          _Seccion('Archivos'),
          _archivo('documento', 'Versión final en Word', false, ayuda: 'Turnitin lo revisa en formato Word (.doc o .docx).'),
          _archivo('comprobantes', 'Comprobante de pago', true, ayuda: _ayudaComprobante),
          if (f.similitudMaxima != null)
            Padding(
              padding: const EdgeInsets.only(top: 4, bottom: 8),
              child: Text(
                'La similitud máxima permitida es ${f.similitudMaxima} %. Cada pago incluye una revisión.',
                style: TextStyle(fontSize: 12, color: PukllaDashboardStyles.textSecondary(context)),
              ),
            ),
        ];
    }
  }

  // ── Campos ──

  Widget _campoTexto(
    String campo,
    String etiqueta, {
    String? ayuda,
    bool opcional = false,
    int lineas = 1,
    int? min,
    TextInputType? teclado,
    RegExp? regex,
    String? mensajeRegex,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextFormField(
        controller: _texto[campo],
        maxLines: lineas,
        keyboardType: teclado ?? (lineas > 1 ? TextInputType.multiline : TextInputType.text),
        textCapitalization: teclado == null ? TextCapitalization.sentences : TextCapitalization.none,
        decoration: _decoracion(etiqueta, ayuda: ayuda, error: _errores[campo]),
        onChanged: (_) {
          if (_errores.containsKey(campo)) setState(() => _errores.remove(campo));
        },
        validator: (v) {
          final t = (v ?? '').trim();
          if (t.isEmpty) return opcional ? null : 'Este campo es obligatorio.';
          if (min != null && t.length < min) return 'Escribe al menos $min caracteres.';
          if (regex != null && !regex.hasMatch(t)) return mensajeRegex;
          return null;
        },
      ),
    );
  }

  Widget _opcion(String campo, String etiqueta, List<Opcion>? opciones, {String? ayudaVacio, void Function(String?)? alCambiar}) {
    final lista = opciones ?? const <Opcion>[];
    final fijo = f.fijos.contains(campo);
    final valor = lista.any((o) => o.valor == _eleccion[campo]) ? _eleccion[campo] : null;
    if (fijo && valor != null) {
      return _Fijo(etiqueta: etiqueta, valor: lista.firstWhere((o) => o.valor == valor).texto, nota: 'Según tu ficha');
    }

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: DropdownButtonFormField<String>(
        // Se reconstruye si cambian sus opciones o su valor (la línea depende del área).
        key: ValueKey('$campo|$valor|${lista.map((o) => o.valor).join(',')}'),
        initialValue: valor,
        isExpanded: true,
        decoration: _decoracion(etiqueta, ayuda: lista.isEmpty ? ayudaVacio : null, error: _errores[campo]),
        items: lista
            .map((o) => DropdownMenuItem(value: o.valor, child: Text(o.texto, maxLines: 2, overflow: TextOverflow.ellipsis)))
            .toList(),
        onChanged: lista.isEmpty
            ? null
            : (v) => setState(() {
                  _eleccion[campo] = v;
                  _errores.remove(campo);
                  alCambiar?.call(v);
                }),
        validator: (v) => v == null ? 'Elige una opción.' : null,
      ),
    );
  }

  Widget _fecha(String campo, String etiqueta) {
    final ctrl = _texto[campo]!;
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextFormField(
        controller: ctrl,
        readOnly: true,
        decoration: _decoracion(etiqueta, error: _errores[campo]).copyWith(suffixIcon: const Icon(Icons.calendar_today_outlined, size: 18)),
        onTap: () async {
          final hoy = DateTime.now();
          final elegido = await showDatePicker(
            context: context,
            initialDate: DateTime.tryParse(ctrl.text) ?? hoy,
            firstDate: DateTime(hoy.year - 2),
            lastDate: hoy, // no puede ser posterior a hoy (regla de la web)
          );
          if (elegido != null) {
            setState(() {
              ctrl.text = '${elegido.year}-${elegido.month.toString().padLeft(2, '0')}-${elegido.day.toString().padLeft(2, '0')}';
              _errores.remove(campo);
            });
          }
        },
        validator: (v) => (v ?? '').isEmpty ? 'Elige la fecha de pago.' : null,
      ),
    );
  }

  Widget _archivo(String campo, String etiqueta, bool multiple, {String? ayuda}) {
    final regla = f.archivos[campo];
    final elegidos = _archivos[campo] ?? const <ArchivoElegido>[];
    final error = _errores[campo];
    final secundario = PukllaDashboardStyles.textSecondary(context);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      decoration: PukllaDashboardStyles.cardDecoration(
        context,
        radius: 12,
        customBorder: error != null ? AppColors.danger.withValues(alpha: 0.6) : null,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(etiqueta, style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700, color: PukllaDashboardStyles.textPrimary(context))),
          if (regla != null) Text(regla.descripcion, style: TextStyle(fontSize: 11.5, color: secundario)),
          if (ayuda != null) ...[
            const SizedBox(height: 4),
            Text(ayuda, style: TextStyle(fontSize: 11.5, height: 1.3, color: secundario)),
          ],
          const SizedBox(height: 8),
          ...elegidos.map((a) => Row(
                children: [
                  const Icon(Icons.insert_drive_file_outlined, size: 18, color: AppColors.goldHover),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text('${a.nombre} · ${a.mb.toStringAsFixed(1)} MB',
                        maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 12.5)),
                  ),
                  IconButton(
                    visualDensity: VisualDensity.compact,
                    tooltip: 'Quitar',
                    icon: const Icon(Icons.close_rounded, size: 18),
                    onPressed: () => setState(() => _archivos[campo] = [...elegidos]..remove(a)),
                  ),
                ],
              )),
          OutlinedButton.icon(
            onPressed: () => _elegirArchivo(campo, multiple),
            icon: const Icon(Icons.attach_file_rounded, size: 18),
            label: Text(elegidos.isEmpty ? 'Elegir archivo' : (multiple ? 'Agregar otro' : 'Cambiar archivo')),
          ),
          if (error != null) ...[
            const SizedBox(height: 4),
            Text(error, style: const TextStyle(fontSize: 12, color: AppColors.danger)),
          ],
        ],
      ),
    );
  }

  InputDecoration _decoracion(String etiqueta, {String? ayuda, String? error}) {
    return InputDecoration(
      labelText: etiqueta,
      helperText: ayuda,
      helperMaxLines: 2,
      errorText: error,
      errorMaxLines: 3,
      filled: true,
      fillColor: PukllaDashboardStyles.surfaceColor(context),
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
    );
  }
}

// ── Piezas de la pantalla ──

class _Seccion extends StatelessWidget {
  const _Seccion(this.titulo);

  final String titulo;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: 6, bottom: 10),
      child: Text(
        titulo.toUpperCase(),
        style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, letterSpacing: .6, color: PukllaDashboardStyles.textSecondary(context)),
      ),
    );
  }
}

class _Fijo extends StatelessWidget {
  const _Fijo({required this.etiqueta, required this.valor, this.nota});

  final String etiqueta;
  final String valor;
  final String? nota;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: PukllaDashboardStyles.isDark(context) ? AppColors.darkSurfaceElevated : const Color(0xFFF1F5F9),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('$etiqueta${nota != null ? ' · $nota' : ''}',
              style: TextStyle(fontSize: 11.5, color: PukllaDashboardStyles.textSecondary(context))),
          const SizedBox(height: 2),
          Text(valor, style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600, color: PukllaDashboardStyles.textPrimary(context))),
        ],
      ),
    );
  }
}

class _CheckDeclaracion extends StatelessWidget {
  const _CheckDeclaracion({required this.valor, required this.onChanged, this.error});

  final bool valor;
  final ValueChanged<bool> onChanged;
  final String? error;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        CheckboxListTile(
          value: valor,
          onChanged: (v) => onChanged(v ?? false),
          controlAffinity: ListTileControlAffinity.leading,
          contentPadding: EdgeInsets.zero,
          title: const Text(_declaracion, style: TextStyle(fontSize: 12.5, height: 1.35)),
        ),
        if (error != null) Text(error!, style: const TextStyle(fontSize: 12, color: AppColors.danger)),
      ],
    );
  }
}

class _Bloqueo extends StatelessWidget {
  const _Bloqueo({required this.texto, this.boton, this.onBoton});

  final String texto;
  final String? boton;
  final VoidCallback? onBoton;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(14),
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 14, customBorder: const Color(0xFF0891B2).withValues(alpha: 0.35)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(Icons.info_outline_rounded, color: Color(0xFF0891B2)),
              const SizedBox(width: 10),
              Expanded(child: Text(texto, style: TextStyle(fontSize: 13, height: 1.4, color: PukllaDashboardStyles.textPrimary(context)))),
            ],
          ),
          if (boton != null) ...[
            const SizedBox(height: 10),
            Align(
              alignment: Alignment.centerRight,
              child: FilledButton.icon(onPressed: onBoton, icon: const Icon(Icons.open_in_new_rounded, size: 16), label: Text(boton!)),
            ),
          ],
        ],
      ),
    );
  }
}

class _EstadoUltima extends StatelessWidget {
  const _EstadoUltima({required this.ultima, required this.tipo});

  final SolicitudResumen ultima;
  final TipoSolicitud tipo;

  @override
  Widget build(BuildContext context) {
    String fmt(DateTime? d) => d == null ? '' : '${d.day.toString().padLeft(2, '0')}/${d.month.toString().padLeft(2, '0')}/${d.year}';
    final (Color color, IconData icono, String texto) = switch (ultima.estado) {
      'observada' => (const Color(0xFFD97706), Icons.error_outline_rounded, 'Tu solicitud fue observada: corrígela y envíala de nuevo.'),
      'recibida' || 'enviada' => (
          const Color(0xFF0891B2),
          Icons.inbox_rounded,
          'Tu solicitud del ${fmt(ultima.fecha)} está en revisión.${ultima.fechaLimite != null ? ' Respuesta a más tardar el ${fmt(ultima.fechaLimite)}.' : ''}',
        ),
      _ => (
          const Color(0xFF16A34A),
          Icons.check_circle_rounded,
          ultima.similitud != null
              ? 'Tu última revisión tiene ${ultima.similitud!.toStringAsFixed(ultima.similitud! % 1 == 0 ? 0 : 2)} % de similitud${ultima.apto == true ? ': está apta.' : ': corrígela y solicita otra revisión.'}'
              : (tipo == TipoSolicitud.carta ? 'Tu carta fue aprobada.' : 'Tu solicitud fue atendida.'),
        ),
    };

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: color.withValues(alpha: 0.35)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(icono, color: color),
              const SizedBox(width: 10),
              Expanded(child: Text(texto, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, height: 1.35, color: color))),
            ],
          ),
          if (ultima.observacion != null && ultima.observacion!.trim().isNotEmpty) ...[
            const SizedBox(height: 8),
            Text('Observación: ${ultima.observacion}',
                style: TextStyle(fontSize: 12.5, height: 1.35, color: PukllaDashboardStyles.textPrimary(context))),
          ],
        ],
      ),
    );
  }
}
