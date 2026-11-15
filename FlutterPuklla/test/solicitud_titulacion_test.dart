import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:flutter_puklla/features/titulacion/application/titulacion_providers.dart';
import 'package:flutter_puklla/features/titulacion/data/solicitud_formulario.dart';
import 'package:flutter_puklla/features/titulacion/data/titulacion_data.dart';
import 'package:flutter_puklla/features/titulacion/presentation/solicitud_titulacion_screen.dart';

/// Respuesta de `GET /alumno/titulacion/solicitud-pti` para un egresado que puede enviar.
Map<String, dynamic> _ptiJson({String? motivo, Map<String, dynamic>? ultima}) => {
      'motivo_no_puede': motivo,
      'ultima': ultima,
      'solicitante': 'Quispe, Ana',
      'valores': {'celulares': '987654321', 'ciclo': 'Egresado', 'titulo': '', 'area': '', 'linea': ''},
      'fijos': ['ciclo'],
      'opciones': {
        'programas_estudios': ['Educación Inicial', 'Educación Primaria'],
        'ciclos': ['IX Ciclo', 'X Ciclo', 'Egresado', 'PPD'],
        'areas': [
          {'valor': 'D1', 'texto': 'Área D1'},
          {'valor': 'D2', 'texto': 'Área D2'},
        ],
        'lineas': [
          {'valor': 'D1-1', 'texto': 'D1-Línea 1'},
          {'valor': 'D2-1', 'texto': 'D2-Línea 1'},
        ],
      },
      'archivos': {
        'pti': {'extensiones': ['pdf', 'doc', 'docx'], 'max_mb': 10},
        'comprobantes': {'extensiones': ['pdf', 'jpg', 'png'], 'max_mb': 10, 'max_archivos': 5},
      },
      'pago': ['50.00', '10', 'Aprobación de PTI y Asesor'],
    };

Future<void> _abrir(WidgetTester tester, SolicitudFormulario form) async {
  tester.view.physicalSize = const Size(800, 2400);
  tester.view.devicePixelRatio = 1.0;
  addTearDown(() => tester.view.resetPhysicalSize());

  await tester.pumpWidget(ProviderScope(
    overrides: [solicitudFormularioProvider(TipoSolicitud.pti).overrideWith((ref) async => form)],
    child: const MaterialApp(home: SolicitudTitulacionScreen(tipo: TipoSolicitud.pti)),
  ));
  await tester.pumpAndSettle();
}

void main() {
  test('el formulario lee opciones, archivos, campos fijos y el pago', () {
    final f = SolicitudFormulario.fromJson(_ptiJson());

    expect(f.puedeEnviar, isTrue);
    expect(f.fijos, contains('ciclo'));
    expect(f.opciones['areas']!.first.valor, 'D1');
    expect(f.opciones['programas_estudios']!.first.texto, 'Educación Inicial');
    expect(f.archivos['comprobantes']!.maxArchivos, 5);
    expect(f.pago!.codigo, '10');
  });

  test('la acción y el trámite indican su formulario nativo', () {
    final t = TitulacionData.fromJson({
      'disponible': true,
      'solicitud_pti': {'mensaje': 'm', 'boton': 'b', 'url': 'https://x', 'formulario_app': 'solicitud-pti'},
      'tramites': {
        'titulo': 'Aprobación de tu PTI',
        'items': [
          {'titulo': 'PTI', 'formulario': {'texto': 'F', 'url': 'https://x', 'formulario_app': 'solicitud-pti'}},
        ],
      },
    });

    expect(t.solicitudPti!.formularioApp, 'solicitud-pti');
    expect(TipoSolicitud.deRuta(t.tramites!.items.first.formulario!.formularioApp), TipoSolicitud.pti);
  });

  testWidgets('muestra el formulario con el pago, el ciclo fijo y los archivos', (tester) async {
    await _abrir(tester, SolicitudFormulario.fromJson(_ptiJson()));

    expect(find.textContaining('código 10'), findsOneWidget);
    expect(find.text('Egresado'), findsOneWidget); // ciclo fijo (según la ficha), no se elige
    expect(find.text('Plan de Trabajo de Investigación (PTI)'), findsOneWidget);
    expect(find.text('Enviar solicitud'), findsOneWidget);
  });

  testWidgets('no deja enviar sin archivos ni declaración', (tester) async {
    await _abrir(tester, SolicitudFormulario.fromJson(_ptiJson()));

    await tester.ensureVisible(find.text('Enviar solicitud'));
    await tester.tap(find.text('Enviar solicitud'));
    await tester.pumpAndSettle();

    expect(find.text('Adjunta este archivo.'), findsNWidgets(2));
    expect(find.text('Debes aceptar la declaración de veracidad.'), findsOneWidget);
  });

  testWidgets('con una solicitud en revisión muestra el motivo y oculta el envío', (tester) async {
    await _abrir(
      tester,
      SolicitudFormulario.fromJson(_ptiJson(
        motivo: 'Ya tienes una solicitud en revisión: espera la respuesta del Área de Investigación.',
        ultima: {'estado': 'recibida', 'estado_nombre': 'Por atender', 'fecha': '2026-11-10'},
      )),
    );

    expect(find.textContaining('Ya tienes una solicitud en revisión'), findsOneWidget);
    expect(find.textContaining('está en revisión'), findsOneWidget);
    expect(find.text('Enviar solicitud'), findsNothing);
  });

  testWidgets('una solicitud observada muestra la observación y permite corregir', (tester) async {
    await _abrir(
      tester,
      SolicitudFormulario.fromJson(_ptiJson(
        ultima: {'estado': 'observada', 'estado_nombre': 'Observada', 'observacion': 'Falta el comprobante legible.'},
      )),
    );

    expect(find.textContaining('Falta el comprobante legible.'), findsOneWidget);
    expect(find.text('Corregir y enviar'), findsOneWidget);
  });
}
