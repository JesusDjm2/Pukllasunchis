import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_colors.dart';
import '../../auth/application/auth_controller.dart';
import '../../calificaciones/application/calificaciones_providers.dart';
import '../../calificaciones/data/matricula.dart';
import '../../home/application/home_providers.dart';
import '../../home/data/periodo_actual.dart';
import '../application/perfil_providers.dart';
import '../data/alumno_perfil.dart';
import '../data/perfil_repository.dart';

/// Formulario grande de matrícula/actualización de datos — espejo móvil de
/// `alumnos/vistasAlumnos/actualizar-datos.blade.php`. Carga el perfil, el
/// periodo activo y la matrícula de ese periodo (si existe) antes de mostrar
/// el formulario, para poder precargar todos los campos y decidir si pide
/// comprobante.
class MatriculaFormScreen extends ConsumerWidget {
  const MatriculaFormScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final perfilAsync = ref.watch(perfilProvider);
    final periodoAsync = ref.watch(periodoActualProvider);
    final matriculasAsync = ref.watch(matriculasProvider);
    final beca = ref.watch(authControllerProvider).user?.beca ?? false;

    final loading = perfilAsync.isLoading || periodoAsync.isLoading || matriculasAsync.isLoading;
    final error = perfilAsync.hasError
        ? perfilAsync.error
        : periodoAsync.hasError
            ? periodoAsync.error
            : matriculasAsync.hasError
                ? matriculasAsync.error
                : null;

    return Scaffold(
      appBar: AppBar(title: const Text('Completar ficha de matrícula')),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : error != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Text(
                      error is ApiException ? error.message : 'No se pudo cargar el formulario.',
                      textAlign: TextAlign.center,
                    ),
                  ),
                )
              : _buildForm(perfilAsync.value!, periodoAsync.value, matriculasAsync.value!, beca),
    );
  }

  Widget _buildForm(AlumnoPerfil perfil, PeriodoActual? periodo, List<Matricula> matriculas, bool becado) {
    Matricula? matriculaActual;
    if (periodo != null) {
      for (final m in matriculas) {
        if (m.periodoActualId == periodo.id) {
          matriculaActual = m;
          break;
        }
      }
    }

    return _MatriculaForm(
      perfil: perfil,
      periodo: periodo,
      matriculaActual: matriculaActual,
      becado: becado,
    );
  }
}

class _MatriculaForm extends ConsumerStatefulWidget {
  const _MatriculaForm({
    required this.perfil,
    required this.periodo,
    required this.matriculaActual,
    required this.becado,
  });

  final AlumnoPerfil perfil;
  final PeriodoActual? periodo;
  final Matricula? matriculaActual;
  final bool becado;

  @override
  ConsumerState<_MatriculaForm> createState() => _MatriculaFormState();
}

class _MatriculaFormState extends ConsumerState<_MatriculaForm> {
  final _formKey = GlobalKey<FormState>();
  bool _enviando = false;

  late final TextEditingController _numero;
  late final TextEditingController _numeroReferencia;
  late final TextEditingController _departamento;
  late final TextEditingController _provincia;
  late final TextEditingController _distrito;
  late final TextEditingController _direccion;
  late final TextEditingController _numHijos;
  late final TextEditingController _convivientes;
  late final TextEditingController _quienMantiene;
  late final TextEditingController _dependientesChild;
  late final TextEditingController _dependientesOld;
  late final TextEditingController _dependientesOtros;
  late final TextEditingController _dondeTrabajas;
  late final TextEditingController _sectorLaboral;
  late final TextEditingController _egreso;
  late final TextEditingController _hrsLaboradasSem;
  late final TextEditingController _dormitorios;
  late final TextEditingController _banos;
  late final TextEditingController _hrsAgua;
  late final TextEditingController _hrsDesague;
  late final TextEditingController _hrsLuz;
  late final TextEditingController _comprobante;

  DateTime? _fechaNacimiento;
  String? _genero;
  String? _permanenciaVivienda;
  String? _estadoCivil;
  bool? _pMSoltero;
  String? _sectorSocioeconomico;
  String? _trabajas;
  String? _ingresoMensual;
  bool? _ayudaEconomica;
  String? _tiempoAyuda;
  String? _tipoApoyoFormacion;
  bool? _estudioBeca;
  String? _tipoVivienda;
  String? _situacionVivienda;
  String? _materialVivienda;
  String? _tipoSeguro;
  late List<String> _bienesVivienda;
  late List<String> _otrosServicios;

  bool get _requiereComprobante =>
      widget.periodo != null && widget.periodo!.formularioHabilitado && !widget.becado;

  @override
  void initState() {
    super.initState();
    final p = widget.perfil;
    _numero = TextEditingController(text: p.numero);
    _numeroReferencia = TextEditingController(text: p.numeroReferencia);
    _departamento = TextEditingController(text: p.departamento ?? '');
    _provincia = TextEditingController(text: p.provincia ?? '');
    _distrito = TextEditingController(text: p.distrito ?? '');
    _direccion = TextEditingController(text: p.direccion);
    _numHijos = TextEditingController(text: p.numHijos?.toString() ?? '');
    _convivientes = TextEditingController(text: p.convivientes ?? '');
    _quienMantiene = TextEditingController(text: p.quienMantiene ?? '');
    _dependientesChild = TextEditingController(text: p.cantDependientesChild ?? '');
    _dependientesOld = TextEditingController(text: p.cantDependientesOld ?? '');
    _dependientesOtros = TextEditingController(text: p.cantDependientesOtros ?? '');
    _dondeTrabajas = TextEditingController(text: p.dondeTrabajas ?? '');
    _sectorLaboral = TextEditingController(text: p.sectorLaboral ?? '');
    _egreso = TextEditingController(text: p.egreso ?? '');
    _hrsLaboradasSem = TextEditingController(text: p.hrsLaboradasSem?.toString() ?? '');
    _dormitorios = TextEditingController(text: p.dormitoriosVivienda?.toString() ?? '');
    _banos = TextEditingController(text: p.banosVivienda?.toString() ?? '');
    _hrsAgua = TextEditingController(text: p.hrsDisponiblesAgua?.toString() ?? '');
    _hrsDesague = TextEditingController(text: p.hrsDisponiblesDesague?.toString() ?? '');
    _hrsLuz = TextEditingController(text: p.hrsDisponiblesLuz?.toString() ?? '');
    _comprobante = TextEditingController(text: widget.matriculaActual?.comprobante ?? '');

    _genero = p.genero;
    _permanenciaVivienda = p.permanenciaVivienda;
    _estadoCivil = p.estadoCivil;
    _pMSoltero = p.pMSoltero;
    _sectorSocioeconomico = p.sectorSocioeconomico;
    _trabajas = p.trabajas;
    _ingresoMensual = p.ingresoMensual;
    _ayudaEconomica = p.ayudaEconomica;
    _tiempoAyuda = p.tiempoAyuda;
    _tipoApoyoFormacion = p.tipoApoyoFormacion;
    _estudioBeca = p.estudioBeca;
    _tipoVivienda = p.tipoVivienda;
    _situacionVivienda = p.situacionVivienda;
    _materialVivienda = p.materialVivienda;
    _tipoSeguro = p.tipoSeguro;
    _bienesVivienda = List.of(p.bienesVivienda);
    _otrosServicios = List.of(p.otrosServicios);
  }

  @override
  void dispose() {
    _numero.dispose();
    _numeroReferencia.dispose();
    _departamento.dispose();
    _provincia.dispose();
    _distrito.dispose();
    _direccion.dispose();
    _numHijos.dispose();
    _convivientes.dispose();
    _quienMantiene.dispose();
    _dependientesChild.dispose();
    _dependientesOld.dispose();
    _dependientesOtros.dispose();
    _dondeTrabajas.dispose();
    _sectorLaboral.dispose();
    _egreso.dispose();
    _hrsLaboradasSem.dispose();
    _dormitorios.dispose();
    _banos.dispose();
    _hrsAgua.dispose();
    _hrsDesague.dispose();
    _hrsLuz.dispose();
    _comprobante.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Form(
      key: _formKey,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(
            'Actualiza tus datos para el nuevo período. Los campos marcados con * son obligatorios.',
            style: Theme.of(context).textTheme.bodySmall,
          ),
          const SizedBox(height: 16),
          if (widget.periodo != null && widget.periodo!.formularioHabilitado) _VoucherSection(this),
          _Seccion(
            icon: Icons.contact_phone_outlined,
            title: 'Datos de contacto',
            children: [
              _text(_numero, 'Celular', required: true),
              _text(_numeroReferencia, 'Celular de emergencia', required: true),
              if (!widget.perfil.tieneFechaNacimiento) _fechaNacimientoField(),
              if (widget.perfil.genero == null) _generoField(),
            ],
          ),
          _Seccion(
            icon: Icons.map_outlined,
            title: 'Domicilio actual',
            children: [
              _text(_departamento, 'Departamento'),
              _text(_provincia, 'Provincia'),
              _text(_distrito, 'Distrito'),
              _text(_direccion, 'Dirección detallada', required: true),
              _safeDropdown(
                label: 'Permanencia en la vivienda',
                value: _permanenciaVivienda,
                options: _Opciones.permanenciaVivienda,
                onChanged: (v) => setState(() => _permanenciaVivienda = v),
                required: true,
              ),
            ],
          ),
          _Seccion(
            icon: Icons.favorite_outline,
            title: 'Estado personal y familiar',
            children: [
              _safeDropdown(
                label: 'Estado civil',
                value: _estadoCivil,
                options: _Opciones.estadoCivil,
                onChanged: (v) => setState(() => _estadoCivil = v),
                required: true,
              ),
              _numero_(_numHijos, 'Número de hijos'),
              _radioSiNo('¿Eres padre/madre soltero/a?', _pMSoltero, (v) => setState(() => _pMSoltero = v)),
              _text(_convivientes, 'Convivientes', required: true),
              _text(_quienMantiene, '¿Quién mantiene el hogar?', required: true),
              _text(_dependientesChild, 'Dependientes menores', required: true),
              _text(_dependientesOld, 'Dependientes adultos mayores', required: true),
              _text(_dependientesOtros, 'Otros dependientes', required: true),
            ],
          ),
          _Seccion(
            icon: Icons.work_outline,
            title: 'Situación económica',
            children: [
              _safeDropdown(
                label: 'Sector socioeconómico',
                value: _sectorSocioeconomico,
                options: _Opciones.sectorSocioeconomico,
                onChanged: (v) => setState(() => _sectorSocioeconomico = v),
                required: true,
                display: _Opciones.sectorDisplay,
              ),
              _safeDropdown(
                label: '¿Trabajas actualmente?',
                value: _trabajas,
                options: _Opciones.trabajas,
                onChanged: (v) => setState(() => _trabajas = v),
                required: true,
              ),
              _text(_sectorLaboral, 'Sector laboral'),
              _text(_dondeTrabajas, 'Dónde trabajas'),
              _safeDropdown(
                label: 'Ingreso mensual promedio',
                value: _ingresoMensual,
                options: _Opciones.ingresoMensual,
                onChanged: (v) => setState(() => _ingresoMensual = v),
              ),
              _text(_egreso, 'Egreso mensual promedio', required: true),
              _numero_(_hrsLaboradasSem, 'Horas laboradas por semana'),
              _radioSiNo('¿Recibe ayuda económica familiar?', _ayudaEconomica, (v) => setState(() => _ayudaEconomica = v)),
              _safeDropdown(
                label: 'Frecuencia de ayuda económica',
                value: _tiempoAyuda,
                options: _Opciones.tiempoAyuda,
                onChanged: (v) => setState(() => _tiempoAyuda = v),
                required: true,
              ),
              _safeDropdown(
                label: 'Tipo de apoyo en formación',
                value: _tipoApoyoFormacion,
                options: _Opciones.tipoApoyoFormacion,
                onChanged: (v) => setState(() => _tipoApoyoFormacion = v),
                required: true,
              ),
              _radioSiNo('¿Estudia con beca?', _estudioBeca, (v) => setState(() => _estudioBeca = v)),
            ],
          ),
          _Seccion(
            icon: Icons.home_outlined,
            title: 'Vivienda',
            children: [
              _safeDropdown(
                label: 'Tipo de vivienda',
                value: _tipoVivienda,
                options: _Opciones.tipoVivienda,
                onChanged: (v) => setState(() => _tipoVivienda = v),
                required: true,
              ),
              _safeDropdown(
                label: 'Situación de vivienda',
                value: _situacionVivienda,
                options: _Opciones.situacionVivienda,
                onChanged: (v) => setState(() => _situacionVivienda = v),
                required: true,
              ),
              _safeDropdown(
                label: 'Material de la vivienda',
                value: _materialVivienda,
                options: _Opciones.materialVivienda,
                onChanged: (v) => setState(() => _materialVivienda = v),
                required: true,
              ),
              _numero_(_dormitorios, 'Dormitorios'),
              _numero_(_banos, 'Baños'),
              _numero_(_hrsAgua, 'Horas de agua al día'),
              _numero_(_hrsDesague, 'Horas de desagüe al día'),
              _numero_(_hrsLuz, 'Horas de luz al día'),
              _checkboxGroup(
                label: 'Bienes en la vivienda',
                options: _Opciones.bienesVivienda,
                selected: _bienesVivienda,
                required: true,
              ),
              _checkboxGroup(
                label: 'Otros servicios en la vivienda',
                options: _Opciones.otrosServicios,
                selected: _otrosServicios,
              ),
            ],
          ),
          _Seccion(
            icon: Icons.local_hospital_outlined,
            title: 'Salud',
            children: [
              _safeDropdown(
                label: 'Tipo de seguro de salud',
                value: _tipoSeguro,
                options: _Opciones.tipoSeguro,
                onChanged: (v) => setState(() => _tipoSeguro = v),
                required: true,
              ),
            ],
          ),
          const SizedBox(height: 8),
          FilledButton.icon(
            onPressed: _enviando ? null : _guardar,
            icon: _enviando
                ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
                : const Icon(Icons.save_outlined),
            label: Text(_enviando ? 'Guardando...' : 'Guardar cambios'),
          ),
          const SizedBox(height: 24),
        ],
      ),
    );
  }

  Widget _text(TextEditingController controller, String label, {bool required = false}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextFormField(
        controller: controller,
        decoration: InputDecoration(labelText: required ? '$label *' : label, border: const OutlineInputBorder()),
        validator: required ? (v) => (v == null || v.trim().isEmpty) ? 'Campo obligatorio' : null : null,
      ),
    );
  }

  Widget _numero_(TextEditingController controller, String label) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextFormField(
        controller: controller,
        keyboardType: TextInputType.number,
        decoration: InputDecoration(labelText: '$label *', border: const OutlineInputBorder()),
        validator: (v) {
          if (v == null || v.trim().isEmpty) return 'Campo obligatorio';
          if (int.tryParse(v.trim()) == null) return 'Debe ser un número';
          return null;
        },
      ),
    );
  }

  Widget _fechaNacimientoField() {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: () async {
          final fecha = await showDatePicker(
            context: context,
            initialDate: DateTime(2000, 1, 1),
            firstDate: DateTime(1950),
            lastDate: DateTime.now(),
          );
          if (fecha != null) setState(() => _fechaNacimiento = fecha);
        },
        child: InputDecorator(
          decoration: const InputDecoration(labelText: 'Fecha de nacimiento *', border: OutlineInputBorder()),
          child: Text(
            _fechaNacimiento == null
                ? 'Seleccionar fecha'
                : '${_fechaNacimiento!.day.toString().padLeft(2, '0')}/${_fechaNacimiento!.month.toString().padLeft(2, '0')}/${_fechaNacimiento!.year}',
          ),
        ),
      ),
    );
  }

  Widget _generoField() {
    return _safeDropdown(
      label: 'Género',
      value: _genero,
      options: _Opciones.genero,
      onChanged: (v) => setState(() => _genero = v),
      required: true,
    );
  }

  Widget _safeDropdown({
    required String label,
    required String? value,
    required List<String> options,
    required ValueChanged<String?> onChanged,
    bool required = false,
    Map<String, String>? display,
  }) {
    final items = List.of(options);
    if (value != null && value.isNotEmpty && !items.contains(value)) {
      items.insert(0, value);
    }

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: DropdownButtonFormField<String>(
        initialValue: value,
        decoration: InputDecoration(labelText: required ? '$label *' : label, border: const OutlineInputBorder()),
        items: [
          for (final op in items) DropdownMenuItem(value: op, child: Text(display?[op] ?? op)),
        ],
        onChanged: onChanged,
        validator: required ? (v) => v == null ? 'Selecciona una opción' : null : null,
      ),
    );
  }

  Widget _radioSiNo(String label, bool? value, ValueChanged<bool?> onChanged) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: FormField<bool>(
        initialValue: value,
        validator: (v) => v == null ? 'Selecciona una opción' : null,
        builder: (state) {
          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('$label *', style: const TextStyle(fontWeight: FontWeight.w600)),
              RadioGroup<bool>(
                groupValue: value,
                onChanged: (v) {
                  onChanged(v);
                  state.didChange(v);
                },
                child: Row(
                  children: const [
                    Expanded(
                      child: RadioListTile<bool>(
                        value: true,
                        title: Text('Sí'),
                        contentPadding: EdgeInsets.zero,
                      ),
                    ),
                    Expanded(
                      child: RadioListTile<bool>(
                        value: false,
                        title: Text('No'),
                        contentPadding: EdgeInsets.zero,
                      ),
                    ),
                  ],
                ),
              ),
              if (state.hasError)
                Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Text(state.errorText!, style: TextStyle(color: Theme.of(context).colorScheme.error, fontSize: 12)),
                ),
            ],
          );
        },
      ),
    );
  }

  Widget _checkboxGroup({
    required String label,
    required List<String> options,
    required List<String> selected,
    bool required = false,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: FormField<List<String>>(
        initialValue: selected,
        validator: required ? (v) => (v == null || v.isEmpty) ? 'Selecciona al menos una opción' : null : null,
        builder: (state) {
          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(required ? '$label *' : label, style: const TextStyle(fontWeight: FontWeight.w600)),
              for (final op in options)
                CheckboxListTile(
                  value: selected.contains(op),
                  title: Text(op, style: const TextStyle(fontSize: 14)),
                  contentPadding: EdgeInsets.zero,
                  controlAffinity: ListTileControlAffinity.leading,
                  onChanged: (checked) {
                    setState(() {
                      if (checked == true) {
                        selected.add(op);
                      } else {
                        selected.remove(op);
                      }
                    });
                    state.didChange(selected);
                  },
                ),
              if (state.hasError)
                Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Text(state.errorText!, style: TextStyle(color: Theme.of(context).colorScheme.error, fontSize: 12)),
                ),
            ],
          );
        },
      ),
    );
  }

  Future<void> _guardar() async {
    final formValido = _formKey.currentState!.validate();
    if (!formValido) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Revisa los campos marcados en rojo.')),
      );
      return;
    }
    if (_requiereComprobante && _comprobante.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Ingresa el número de boleta de pago (voucher).')),
      );
      return;
    }

    setState(() => _enviando = true);

    final payload = <String, dynamic>{
      'numero': _numero.text.trim(),
      'numero_referencia': _numeroReferencia.text.trim(),
      'direccion': _direccion.text.trim(),
      'departamento': _emptyToNull(_departamento.text),
      'provincia': _emptyToNull(_provincia.text),
      'distrito': _emptyToNull(_distrito.text),
      'permanencia_vivienda': _permanenciaVivienda,
      'estado_civil': _estadoCivil,
      'p_m_soltero': _boolToFlag(_pMSoltero),
      'num_hijos': int.parse(_numHijos.text.trim()),
      'sector_socioeconomico': _sectorSocioeconomico,
      'trabajas': _trabajas,
      'donde_trabajas': _emptyToNull(_dondeTrabajas.text),
      'ingreso_mensual': _emptyToNull(_ingresoMensual ?? ''),
      'egreso': _egreso.text.trim(),
      'hrs_laboradas_sem': int.parse(_hrsLaboradasSem.text.trim()),
      'sector_laboral': _emptyToNull(_sectorLaboral.text),
      'ayuda_economica': _boolToFlag(_ayudaEconomica),
      'tiempo_ayuda': _tiempoAyuda,
      'tipo_apoyo_formacion': _tipoApoyoFormacion,
      'convivientes': _convivientes.text.trim(),
      'quien_mantiene': _quienMantiene.text.trim(),
      'cant_dependientes_child': _dependientesChild.text.trim(),
      'cant_dependientes_old': _dependientesOld.text.trim(),
      'cant_dependientes_otros': _dependientesOtros.text.trim(),
      'tipo_vivienda': _tipoVivienda,
      'situacion_vivienda': _situacionVivienda,
      'dormitorios_vivienda': int.parse(_dormitorios.text.trim()),
      'banos_vivienda': int.parse(_banos.text.trim()),
      'material_vivienda': _materialVivienda,
      'bienes_vivienda': _bienesVivienda,
      'otros_servicios': _otrosServicios,
      'hrs_disponibles_agua': int.parse(_hrsAgua.text.trim()),
      'hrs_disponibles_desague': int.parse(_hrsDesague.text.trim()),
      'hrs_disponibles_luz': int.parse(_hrsLuz.text.trim()),
      'tipo_seguro': _tipoSeguro,
      'estudio_beca': _boolToFlag(_estudioBeca),
    };

    if (!widget.perfil.tieneFechaNacimiento && _fechaNacimiento != null) {
      payload['fecha_nacimiento'] =
          '${_fechaNacimiento!.year.toString().padLeft(4, '0')}-${_fechaNacimiento!.month.toString().padLeft(2, '0')}-${_fechaNacimiento!.day.toString().padLeft(2, '0')}';
    }
    if (widget.perfil.genero == null) {
      payload['genero'] = _genero;
    }
    if (_requiereComprobante) {
      payload['comprobante'] = _comprobante.text.trim();
    }

    try {
      final resultado = await ref.read(perfilRepositoryProvider).actualizarPerfil(payload);

      ref.invalidate(perfilProvider);
      ref.invalidate(periodoActualProvider);
      ref.invalidate(matriculasProvider);
      ref.invalidate(yaMatriculadoProvider);

      if (!mounted) return;

      final yaEstabaMatriculado = widget.matriculaActual != null;
      final mensaje = !resultado.matriculaCompletada
          ? 'Tus datos se actualizaron correctamente.'
          : yaEstabaMatriculado
              ? 'Tus datos se actualizaron correctamente. Sigues matriculado/a para ${widget.periodo?.nombre ?? "el período actual"}.'
              : '¡Matrícula completada! Quedaste registrado/a para ${widget.periodo?.nombre ?? "el período actual"}. Se notificó a la institución.';

      await showDialog<void>(
        context: context,
        builder: (context) => AlertDialog(
          icon: const Icon(Icons.check_circle, color: AppColors.success, size: 40),
          title: const Text('¡Formulario enviado!'),
          content: Text(mensaje),
          actions: [
            FilledButton(onPressed: () => Navigator.of(context).pop(), child: const Text('Entendido')),
          ],
        ),
      );

      if (mounted) Navigator.of(context).pop();
    } on ApiException catch (e) {
      if (!mounted) return;
      final detalle = e.errors?.values.expand((v) => v).join('\n');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(detalle?.isNotEmpty == true ? detalle! : e.message)),
      );
    } finally {
      if (mounted) setState(() => _enviando = false);
    }
  }

  String? _emptyToNull(String value) => value.trim().isEmpty ? null : value.trim();

  String _boolToFlag(bool? value) => value == true ? '1' : '0';

  TextEditingController get comprobanteController => _comprobante;
  bool get requiereComprobante => _requiereComprobante;
  bool get becado => widget.becado;
  Matricula? get matriculaActual => widget.matriculaActual;
  String? get periodoNombre => widget.periodo?.nombre;
}

class _VoucherSection extends StatelessWidget {
  const _VoucherSection(this.formState);

  final _MatriculaFormState formState;

  @override
  Widget build(BuildContext context) {
    final yaRegistrado = formState.matriculaActual?.comprobante != null;
    final borderColor = yaRegistrado ? AppColors.success : AppColors.warning;

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        border: Border(left: BorderSide(color: borderColor, width: 4)),
        color: AppColors.background,
        borderRadius: BorderRadius.circular(4),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.receipt_long_outlined),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Voucher de matrícula — ${formState.periodoNombre ?? ""}',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          if (formState.becado)
            Text(
              'Eres becado/a — no se requiere voucher. Al guardar quedarás registrado/a en el período actual.',
              style: TextStyle(color: AppColors.success),
            )
          else ...[
            TextFormField(
              controller: formState.comprobanteController,
              decoration: InputDecoration(
                labelText: 'N° Boleta de pago *',
                helperText: 'Al guardar quedarás registrado/a en el período actual.',
                border: const OutlineInputBorder(),
                suffixIcon: yaRegistrado ? const Icon(Icons.check_circle, color: AppColors.success) : null,
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _Seccion extends StatelessWidget {
  const _Seccion({required this.icon, required this.title, required this.children});

  final IconData icon;
  final String title;
  final List<Widget> children;

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(icon, color: AppColors.teal),
                const SizedBox(width: 8),
                Text(title, style: Theme.of(context).textTheme.titleMedium),
              ],
            ),
            const Divider(height: 24),
            ...children,
          ],
        ),
      ),
    );
  }
}

/// Listas calcadas de `alumnos/vistasAlumnos/actualizar-datos.blade.php`.
/// `_safeDropdown` inserta el valor actual si no está en la lista (datos
/// heredados fuera de catálogo, ver [[pukllasunchis_schema_drift_pattern]]).
class _Opciones {
  static const estadoCivil = ['Soltero', 'Casado', 'Conviviente', 'Viudo', 'Divorciado'];
  static const sectorSocioeconomico = ['popular', 'medio', 'medio_alto', 'alto'];
  static const sectorDisplay = {
    'popular': 'Popular',
    'medio': 'Medio',
    'medio_alto': 'Medio alto',
    'alto': 'Alto',
  };
  static const trabajas = ['Sí', 'No', 'Todavía no'];
  static const ingresoMensual = [
    'Menos de 500',
    'De s/501 a s/930',
    'De s/931 a s/1200',
    'De s/1201 a s/2000',
    'Más de s/2000',
  ];
  static const tiempoAyuda = ['Quincenal', 'Mensual', '1 vez al año', 'Otro'];
  static const tipoApoyoFormacion = ['Beca parcial', 'Beca integral', 'No recibo ningún beneficio', 'Otro'];
  static const tipoVivienda = [
    'Casa independiente',
    'Departamento o edificio',
    'Condominio',
    'Casa de vecindad',
    'Casa de pensión',
    'Cuarto alquilado',
    'Otro',
  ];
  static const situacionVivienda = ['Propia', 'Alquilada', 'Alquiler venta', 'Prestada', 'Otra'];
  static const materialVivienda = ['Cemento/Ladrillo', 'Adobe', 'Quincha', 'Otro'];
  static const tipoSeguro = [
    'EsSalud',
    'Seguro Ejército',
    'Seguro Integral de Salud (SIS)',
    'Seguro privado',
    'Ninguno',
  ];
  static const permanenciaVivienda = [
    'Vivo permanentemente en Cusco ciudad',
    'Vivo en comunidad y me traslado al Cusco todos los días para estudiar',
    'Estoy en Cusco de lunes a viernes y los fines de semana en mi comunidad',
    'Vivo en comunidad y me traslado al Cusco fines de semana',
  ];
  static const bienesVivienda = [
    'Cocina a gas',
    'Cocina electrica',
    'Aspiradora',
    'Televisor',
    'DVD',
    'Mini componente',
    'Cámara de video',
    'Computadora',
    'Horno microondas',
    'Lavadora',
    'Secadora de ropa',
    'Automóvil',
    'Bicicleta',
    'Motocicleta',
    'Juego de video',
    'Refrigeradora',
    'Ninguna de las anteriores',
  ];
  static const otrosServicios = [
    'Empleado(a) doméstico',
    'Servicio de teléfono',
    'Servicio de cable',
    'Servicio de Internet',
    'Ninguna de las anteriores',
  ];
  static const genero = ['Masculino', 'Femenino', 'Otro'];
}
