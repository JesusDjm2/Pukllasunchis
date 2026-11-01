import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:flutter_puklla/core/storage/token_storage.dart';
import 'package:flutter_puklla/features/auth/application/auth_controller.dart';
import 'package:flutter_puklla/features/auth/data/auth_user.dart';
import 'package:flutter_puklla/features/calificaciones/application/calificaciones_providers.dart';
import 'package:flutter_puklla/features/calificaciones/data/calificacion.dart';
import 'package:flutter_puklla/features/calificaciones/presentation/calificaciones_screen.dart';

class _FakeTokenStorage extends TokenStorage {
  @override
  Future<String?> readToken() async => null;
}

final _fakeResponse = CalificacionesResponse(
  periodoActual: PeriodoActualCalificaciones(
    periodoNombre: '2026-I',
    p1Visible: true,
    p2Visible: true,
    cursos: [
      CursoConNotas(
        cursoId: 101,
        cursoNombre: 'Práctica e Investigación IV',
        cicloNombre: 'IV',
        programaNombre: 'Educación Inicial',
        parcial1: const NotaParcial(
          valoracionCurso: 'Logrado',
          calificacionCurso: '4.50',
          calificacionSistema: '17',
        ),
        parcial2: const NotaParcial(
          valoracionCurso: 'Logrado',
          calificacionCurso: '4.80',
          calificacionSistema: '18',
        ),
        promedio: const NotaParcial(
          valoracionCurso: 'Logrado',
          calificacionCurso: '4.65',
          calificacionSistema: '18',
        ),
        observaciones: 'Buen desempeño general en aula.',
      ),
    ],
  ),
  anteriores: [
    CalificacionesPorPeriodo(
      periodoNombre: '2025-II',
      calificaciones: [
        Calificacion(
          id: 1,
          cursoId: 201,
          cursoNombre: 'Desarrollo del Pensamiento Matemático',
          cicloNombre: 'III',
          programaNombre: 'Educación Inicial',
          creditos: 4,
          horas: 6,
          valoracionCurso: '4.20',
          calificacionCurso: 'Logrado',
          calificacionSistema: '16',
        ),
      ],
    ),
  ],
);

void main() {
  testWidgets('CalificacionesScreen muestra los dos tabs correctos y conmuta contenido', (tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(tester.view.reset);

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(_FakeTokenStorage()),
          calificacionesProvider.overrideWith((ref) => Future.value(_fakeResponse)),
        ],
        child: const MaterialApp(
          home: CalificacionesScreen(),
        ),
      ),
    );

    await tester.pumpAndSettle();

    // Verifica que los dos tabs existen y tienen los nombres requeridos
    expect(find.text('Período actual'), findsOneWidget);
    expect(find.text('Períodos anteriores'), findsOneWidget);

    // No debe existir el tab antiguo 'Matrícula'
    expect(find.text('Matrícula'), findsNothing);

    // Tab 1: Período actual renderizado
    expect(find.text('Período 2026-I'), findsOneWidget);
    expect(find.text('Práctica e Investigación IV'), findsOneWidget);
    expect(find.text('Parcial 1'), findsOneWidget);
    expect(find.text('Parcial 2'), findsOneWidget);
    expect(find.text('Promedio final'), findsOneWidget);
    expect(find.text('Obs. docente: Buen desempeño general en aula.'), findsOneWidget);

    // Tocar el tab de 'Períodos anteriores'
    await tester.tap(find.text('Períodos anteriores'));
    await tester.pumpAndSettle();

    // Tab 2: Períodos anteriores renderizado
    expect(find.text('Historial Semestre 2025-II'), findsOneWidget);
    expect(find.text('Desarrollo del Pensamiento Matemático'), findsOneWidget);
    expect(find.text('Aprobado'), findsOneWidget);
    expect(find.text('16'), findsOneWidget);
  });
}
