import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/theme/puklla_dashboard_styles.dart';
import '../../../core/widgets/error_state.dart';
import '../../../core/widgets/puklla_app_bar_actions.dart';
import '../../../core/widgets/puklla_bottom_nav_bar.dart';
import '../../../core/widgets/puklla_preloader.dart';
import '../../../routing/app_drawer.dart';
import '../application/titulacion_providers.dart';
import '../data/titulacion_data.dart';
import 'titulacion_widgets.dart';

/// "Mi proceso de titulación": lo mismo que la web muestra al egresado o al
/// alumno de los últimos ciclos (titulacion/_avance_alumno y _guia_inicio).
/// Solo lectura: las solicitudes (PTI, carta, originalidad) se envían en la web.
class TitulacionScreen extends ConsumerWidget {
  const TitulacionScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final async = ref.watch(titulacionProvider);

    return Scaffold(
      backgroundColor: PukllaDashboardStyles.backgroundColor(context),
      appBar: AppBar(
        title: const Text('Mi Titulación', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 19)),
        actions: const [PukllaAppBarActions()],
        elevation: 0,
      ),
      endDrawer: const AppDrawer(),
      bottomNavigationBar: const PukllaBottomNavBar(),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(titulacionProvider.future),
        child: async.when(
          loading: () => const PukllaPreloader.inline(mensaje: 'Cargando tu proceso de titulación...'),
          error: (e, _) => ListView(children: [
            const SizedBox(height: 80),
            ErrorState(
              message: e is ApiException ? e.message : 'No se pudo cargar tu proceso de titulación.',
              onRetry: () => ref.invalidate(titulacionProvider),
            ),
          ]),
          data: (data) => data.disponible ? _Contenido(data: data) : const _NoDisponible(),
        ),
      ),
    );
  }
}

class _NoDisponible extends StatelessWidget {
  const _NoDisponible();

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(24),
      children: [
        const SizedBox(height: 40),
        Icon(Icons.workspace_premium_outlined, size: 56, color: PukllaDashboardStyles.textSecondary(context)),
        const SizedBox(height: 14),
        Text(
          'Tu titulación aún no está disponible',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: PukllaDashboardStyles.textPrimary(context)),
        ),
        const SizedBox(height: 8),
        Text(
          'En Formación Inicial Docente podrás iniciar tu proceso de titulación desde el Ciclo IX. '
          'Mientras tanto, revisa las guías de TI y Tesis en "Guías & Biblioteca".',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 13, height: 1.4, color: PukllaDashboardStyles.textSecondary(context)),
        ),
      ],
    );
  }
}

class _Contenido extends StatelessWidget {
  const _Contenido({required this.data});

  final TitulacionData data;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 28),
      children: [
        _Cabecera(data: data),
        if (data.vigenciaPti != null) ...[
          const SizedBox(height: 12),
          VigenciaPtiAviso(vigencia: data.vigenciaPti!),
        ],
        if (data.acciones.isNotEmpty) ...[
          const SizedBox(height: 20),
          _Titulo(data.tieneProceso ? 'Mis solicitudes' : 'Tu primer paso'),
          ...data.acciones.map((a) => _AccionCard(accion: a)),
        ],
        if (data.tramites != null && data.tramites!.items.isNotEmpty) ...[
          const SizedBox(height: 20),
          _Titulo('Qué hacer ahora · ${data.tramites!.titulo}'),
          ...data.tramites!.items.map((t) => _TramiteCard(tramite: t)),
          if (data.tramites!.contactos.isNotEmpty) _Contactos(contactos: data.tramites!.contactos),
        ],
        const SizedBox(height: 20),
        _Titulo(data.tieneProceso ? 'Mi línea de tiempo' : 'Los pasos de tu titulación'),
        ...data.etapas.asMap().entries.map((e) => _EtapaCard(etapa: e.value, numero: e.key + 1)),
        const SizedBox(height: 16),
        OutlinedButton.icon(
          onPressed: () => abrirEnlaceTitulacion(context, data.enlaceWeb),
          icon: const Icon(Icons.open_in_new_rounded, size: 18),
          label: const Text('Ver mi proceso completo en la web'),
          style: OutlinedButton.styleFrom(
            padding: const EdgeInsets.symmetric(vertical: 13),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
        ),
        const SizedBox(height: 6),
        Text(
          'En la web también ves tu grupo, tus resoluciones y puedes avisar si algún dato no coincide.',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 11.5, color: PukllaDashboardStyles.textSecondary(context)),
        ),
      ],
    );
  }
}

class _Titulo extends StatelessWidget {
  const _Titulo(this.texto);

  final String texto;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Text(
        texto,
        style: TextStyle(fontSize: 15.5, fontWeight: FontWeight.w800, color: PukllaDashboardStyles.textPrimary(context)),
      ),
    );
  }
}

class _Cabecera extends StatelessWidget {
  const _Cabecera({required this.data});

  final TitulacionData data;

  @override
  Widget build(BuildContext context) {
    final siguiente = data.siguientePaso;
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: PukllaDashboardStyles.heroHeaderDecoration(context).copyWith(borderRadius: BorderRadius.circular(20)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.workspace_premium_rounded, color: AppColors.gold, size: 22),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  data.esEgresado
                      ? 'Egresado(a)${data.promocion != null ? ' · Promoción ${data.promocion}' : ''}'
                      : 'Estudiante de los últimos ciclos',
                  style: TextStyle(color: Colors.white.withValues(alpha: 0.85), fontSize: 12.5, fontWeight: FontWeight.w600),
                ),
              ),
              if (data.estado != null) EstadoTitulacionChip(estado: data.estado!),
            ],
          ),
          const SizedBox(height: 14),
          Text(
            data.tieneProceso
                ? (siguiente != null ? siguiente.label : '¡Proceso completo!')
                : 'Ya puedes iniciar tu proceso',
            style: const TextStyle(color: Colors.white, fontSize: 19, fontWeight: FontWeight.w800, height: 1.2),
          ),
          const SizedBox(height: 4),
          Text(
            data.tieneProceso
                ? (siguiente != null ? '${siguiente.falta}${siguiente.etapaTitulo != null ? '\nEtapa: ${siguiente.etapaTitulo}' : ''}' : 'Ya tienes tu Título de Licenciado(a).')
                : 'Cada paso se habilita cuando se registra el anterior.',
            style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 12.5, height: 1.4),
          ),
          if (data.tieneProceso) ...[
            const SizedBox(height: 14),
            ClipRRect(
              borderRadius: BorderRadius.circular(6),
              child: LinearProgressIndicator(
                value: data.progreso,
                minHeight: 8,
                backgroundColor: Colors.white.withValues(alpha: 0.18),
                valueColor: const AlwaysStoppedAnimation(AppColors.gold),
              ),
            ),
            const SizedBox(height: 6),
            Text(
              '${(data.progreso * 100).round()} % del camino PTI → Bachiller → Licenciatura',
              style: TextStyle(color: Colors.white.withValues(alpha: 0.75), fontSize: 11.5),
            ),
          ],
        ],
      ),
    );
  }
}

class _AccionCard extends StatelessWidget {
  const _AccionCard({required this.accion});

  final TitulacionAccion accion;

  @override
  Widget build(BuildContext context) {
    final (Color color, IconData icono) = accion.observada
        ? (const Color(0xFFD97706), Icons.error_outline_rounded)
        : accion.enCurso
            ? (const Color(0xFF0891B2), Icons.inbox_rounded)
            : accion.lista
                ? (const Color(0xFF16A34A), Icons.check_circle_rounded)
                : (AppColors.goldHover, Icons.flag_rounded);

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 14, customBorder: color.withValues(alpha: 0.35)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(icono, color: color, size: 22),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  accion.mensaje,
                  style: TextStyle(fontSize: 13, height: 1.35, color: PukllaDashboardStyles.textPrimary(context)),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Align(
            alignment: Alignment.centerRight,
            child: FilledButton.icon(
              onPressed: () => abrirSolicitudTitulacion(context, formularioApp: accion.formularioApp, url: accion.url),
              icon: Icon(accion.formularioApp != null ? Icons.edit_document : Icons.open_in_new_rounded, size: 16),
              label: Text(accion.boton, style: const TextStyle(fontWeight: FontWeight.w700)),
              style: FilledButton.styleFrom(
                backgroundColor: color,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _TramiteCard extends StatelessWidget {
  const _TramiteCard({required this.tramite});

  final TitulacionTramite tramite;

  @override
  Widget build(BuildContext context) {
    final secundario = PukllaDashboardStyles.textSecondary(context);
    final primario = PukllaDashboardStyles.textPrimary(context);
    final pago = tramite.pago;

    Widget seccion(String titulo, List<String> lineas, {bool numerada = false}) => Padding(
          padding: const EdgeInsets.only(top: 10),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(titulo, style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: secundario, letterSpacing: .3)),
              const SizedBox(height: 4),
              ...lineas.asMap().entries.map((e) => Padding(
                    padding: const EdgeInsets.only(bottom: 3),
                    child: Text(
                      '${numerada ? '${e.key + 1}.' : '•'} ${e.value}',
                      style: TextStyle(fontSize: 12.5, height: 1.35, color: primario),
                    ),
                  )),
            ],
          ),
        );

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 14),
      child: Theme(
        data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
        child: ExpansionTile(
          tilePadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 2),
          childrenPadding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
          expandedCrossAxisAlignment: CrossAxisAlignment.start,
          leading: const Icon(Icons.assignment_turned_in_outlined, color: AppColors.goldHover),
          title: Text(tramite.titulo, style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: primario)),
          subtitle: pago != null
              ? Text('S/ ${pago.monto} · código ${pago.codigo}', style: TextStyle(fontSize: 12, color: secundario))
              : (tramite.opcional != null ? Text(tramite.opcional!, style: TextStyle(fontSize: 12, color: secundario)) : null),
          children: [
            if (pago != null)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFB91C1C).withValues(alpha: 0.07),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFB91C1C).withValues(alpha: 0.25)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Pago en Caja Cusco (pago ordinario)',
                        style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800, color: Color(0xFFB91C1C))),
                    const SizedBox(height: 6),
                    _DatoPago(etiqueta: 'Concepto', valor: pago.concepto),
                    _DatoPago(etiqueta: 'Código', valor: pago.codigo),
                    _DatoPago(etiqueta: 'Monto', valor: 'S/ ${pago.monto}'),
                  ],
                ),
              ),
            if (tramite.pasos.isNotEmpty) seccion('PASOS', tramite.pasos, numerada: true),
            if (tramite.adjuntar.isNotEmpty) seccion('ADJUNTA', tramite.adjuntar),
            if (tramite.resultado != null) seccion('RESULTADO', [tramite.resultado!]),
            if (tramite.plazo != null) seccion('PLAZO', [tramite.plazo!]),
            if (tramite.nota != null) seccion('IMPORTANTE', [tramite.nota!]),
            if (tramite.descargas.isNotEmpty || tramite.formulario != null) ...[
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  ...tramite.descargas.map((d) => OutlinedButton.icon(
                        onPressed: () => abrirEnlaceTitulacion(context, d.url),
                        icon: const Icon(Icons.download_rounded, size: 16),
                        label: Text(d.texto),
                      )),
                  if (tramite.formulario != null)
                    FilledButton.icon(
                      onPressed: () => abrirSolicitudTitulacion(
                        context,
                        formularioApp: tramite.formulario!.formularioApp,
                        url: tramite.formulario!.url,
                      ),
                      icon: Icon(tramite.formulario!.formularioApp != null ? Icons.edit_document : Icons.open_in_new_rounded, size: 16),
                      label: Text(tramite.formulario!.texto),
                      style: FilledButton.styleFrom(backgroundColor: AppColors.goldHover),
                    ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _DatoPago extends StatelessWidget {
  const _DatoPago({required this.etiqueta, required this.valor});

  final String etiqueta;
  final String valor;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 2),
      child: Row(
        children: [
          SizedBox(
            width: 70,
            child: Text(etiqueta, style: TextStyle(fontSize: 12, color: PukllaDashboardStyles.textSecondary(context))),
          ),
          Expanded(
            child: Text(valor, style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: PukllaDashboardStyles.textPrimary(context))),
          ),
          InkWell(
            onTap: () {
              Clipboard.setData(ClipboardData(text: valor));
              ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                content: Text('$etiqueta copiado'),
                behavior: SnackBarBehavior.floating,
                duration: const Duration(seconds: 1),
              ));
            },
            child: Padding(
              padding: const EdgeInsets.all(4),
              child: Icon(Icons.copy_rounded, size: 14, color: PukllaDashboardStyles.textSecondary(context)),
            ),
          ),
        ],
      ),
    );
  }
}

class _Contactos extends StatelessWidget {
  const _Contactos({required this.contactos});

  final List<TitulacionContacto> contactos;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: Wrap(
        spacing: 8,
        runSpacing: 8,
        crossAxisAlignment: WrapCrossAlignment.center,
        children: [
          Text('¿Dudas? Escribe a:', style: TextStyle(fontSize: 12, color: PukllaDashboardStyles.textSecondary(context))),
          ...contactos.map((c) => ActionChip(
                avatar: const Icon(Icons.chat_rounded, size: 16, color: Color(0xFF16A34A)),
                label: Text(c.nombre, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700)),
                onPressed: () => abrirEnlaceTitulacion(context, c.whatsapp),
              )),
        ],
      ),
    );
  }
}

class _EtapaCard extends StatelessWidget {
  const _EtapaCard({required this.etapa, required this.numero});

  final TitulacionEtapa etapa;
  final int numero;

  @override
  Widget build(BuildContext context) {
    final color = switch (etapa.estado) {
      'completo' => const Color(0xFF16A34A),
      'en_proceso' => AppColors.goldHover,
      _ => PukllaDashboardStyles.textSecondary(context),
    };

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: PukllaDashboardStyles.cardDecoration(context, radius: 14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              CircleAvatar(
                radius: 13,
                backgroundColor: color.withValues(alpha: 0.15),
                child: etapa.estado == 'completo'
                    ? Icon(Icons.check_rounded, size: 16, color: color)
                    : Text('$numero', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: color)),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  etapa.titulo,
                  style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: PukllaDashboardStyles.textPrimary(context)),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          ...etapa.hitos.map((h) => _HitoFila(hito: h)),
        ],
      ),
    );
  }
}

class _HitoFila extends StatelessWidget {
  const _HitoFila({required this.hito});

  final TitulacionHito hito;

  @override
  Widget build(BuildContext context) {
    final secundario = PukllaDashboardStyles.textSecondary(context);
    final (IconData icono, Color color) = switch (hito.situacion) {
      'logrado' => (Icons.check_circle_rounded, const Color(0xFF16A34A)),
      'actual' => (Icons.radio_button_checked_rounded, AppColors.goldHover),
      'sin_registro' => (Icons.remove_circle_outline_rounded, secundario),
      _ => (Icons.radio_button_unchecked_rounded, secundario),
    };
    final fecha = hito.fecha;
    final sub = [
      if (fecha != null) '${fecha.day.toString().padLeft(2, '0')}/${fecha.month.toString().padLeft(2, '0')}/${fecha.year}',
      if (hito.detalle != null) hito.detalle!,
      if (hito.situacion == 'actual') hito.falta,
      if (hito.situacion == 'sin_registro') 'Sin fecha registrada',
    ].join(' · ');

    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icono, size: 18, color: color),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  hito.label,
                  style: TextStyle(
                    fontSize: 12.8,
                    fontWeight: hito.situacion == 'actual' ? FontWeight.w800 : FontWeight.w600,
                    color: hito.situacion == 'pendiente' ? secundario : PukllaDashboardStyles.textPrimary(context),
                  ),
                ),
                if (sub.isNotEmpty)
                  Text(sub, style: TextStyle(fontSize: 11.5, height: 1.3, color: secundario)),
              ],
            ),
          ),
          if (hito.enlace != null)
            IconButton(
              tooltip: 'Ver documento',
              visualDensity: VisualDensity.compact,
              icon: const Icon(Icons.picture_as_pdf_outlined, size: 18, color: Color(0xFFB91C1C)),
              onPressed: () => abrirEnlaceTitulacion(context, hito.enlace!),
            ),
        ],
      ),
    );
  }
}
