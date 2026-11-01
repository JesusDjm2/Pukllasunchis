import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:flutter_puklla/features/recursos/presentation/formatos_biblioteca_screen.dart';

void main() {
  testWidgets('FormatosBibliotecaScreen muestra banner de biblioteca virtual, tabs y guias oficiales',
      (tester) async {
    tester.view.physicalSize = const Size(800, 1600);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(() => tester.view.resetPhysicalSize());

    await tester.pumpWidget(
      const ProviderScope(
        child: MaterialApp(
          home: FormatosBibliotecaScreen(),
        ),
      ),
    );

    await tester.pumpAndSettle();

    // 1. Título y Banner de la nueva Biblioteca Virtual
    expect(find.text('Guías de TI, Tesis & Biblioteca'), findsOneWidget);
    expect(find.text('Biblioteca Virtual Pukllasunchis'), findsOneWidget);
    expect(find.text('Ingresar a la Biblioteca'), findsOneWidget);
    expect(find.text('Google Sites'), findsOneWidget);

    // 2. Selector de modalidad FID / PPD
    expect(find.text('Formación Inicial (FID)'), findsOneWidget);
    expect(find.text('Profesionalización (PPD)'), findsOneWidget);

    // 3. Pestañas (3 tabs)
    expect(find.text('Investigación (TI)'), findsOneWidget);
    expect(find.text('Tesis'), findsOneWidget);
    expect(find.text('Videos (5)'), findsOneWidget);

    // 4. Documentos de la pestaña TI (por defecto)
    expect(find.text('Formato de TI'), findsOneWidget);
    expect(find.text('Carátula de TI (FID)'), findsOneWidget);
    expect(find.text('Guía de Trabajo de Investigación'), findsOneWidget);
    expect(find.text('Guía de Redacción y Citas 2025'), findsOneWidget);

    // 5. Conmutar a modalidad PPD
    await tester.tap(find.text('Profesionalización (PPD)'));
    await tester.pumpAndSettle();
    expect(find.text('Carátula de TI (PPD)'), findsOneWidget);

    // 6. Conmutar a la pestaña Tesis
    await tester.tap(find.text('Tesis'));
    await tester.pumpAndSettle();
    expect(find.text('Formato de Tesis'), findsOneWidget);
    expect(find.text('Guía de Elaboración de Tesis'), findsOneWidget);
  });
}
