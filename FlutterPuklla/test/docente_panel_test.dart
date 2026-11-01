import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:flutter_puklla/core/storage/token_storage.dart';
import 'package:flutter_puklla/features/auth/data/auth_user.dart';
import 'package:flutter_puklla/features/docente/application/docente_providers.dart';
import 'package:flutter_puklla/features/docente/data/docente_curso.dart';
import 'package:flutter_puklla/features/docente/data/docente_resumen.dart';
import 'package:flutter_puklla/features/docente/presentation/docente_home_screen.dart';
import 'package:flutter_puklla/routing/app_router.dart';

class _FakeTokenStorage extends TokenStorage {
  @override
  Future<String?> readToken() async => null;
}

AuthUser _usuario({int? alumnoId, int? docenteId, List<String> roles = const []}) {
  return AuthUser(
    id: 1,
    name: 'Carlos',
    apellidos: 'Guevara',
    email: 'carlos@puklla.test',
    roles: roles,
    alumnoId: alumnoId,
    docenteId: docenteId,
  );
}

void main() {
  group('inicioPara', () {
    test('un alumno aterriza en la ficha técnica', () {
      expect(inicioPara(_usuario(alumnoId: 9, roles: ['alumno'])), '/home');
    });

    test('un docente sin perfil de alumno aterriza en su panel', () {
      expect(inicioPara(_usuario(docenteId: 4, roles: ['docente'])), '/docente');
    });

    test('quien tiene ambos perfiles aterriza en su panel docente (prioridad según LoginController de Laravel)', () {
      expect(inicioPara(_usuario(alumnoId: 9, docenteId: 4, roles: ['docente'])), '/docente');
    });

    test('sin usuario conocido no se cambia la ruta', () {
      expect(inicioPara(null), '/home');
    });
  });

  testWidgets('el panel del docente muestra sus métricas y cursos', (tester) async {
    // Vista alta: el ListView es perezoso y sin espacio suficiente ni siquiera
    // construye la tarjeta del curso que queremos verificar.
    tester.view.physicalSize = const Size(1200, 2400);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(tester.view.reset);

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(_FakeTokenStorage()),
          docenteResumenProvider.overrideWith(
            (ref) async => const DocenteResumen(
              periodoActual: '2026-I',
              cursos: 8,
              cursosFid: 4,
              cursosPpd: 4,
              cursosAsincronicos: 0,
              incidenciasTotal: 3,
              incidenciasPendientes: 1,
              notificacionesSinLeer: 2,
            ),
          ),
          docenteCursosProvider.overrideWith(
            (ref) async => const [
              DocenteCurso(
                id: 232,
                nombre: 'Investigación II',
                area: 'ppd',
                ciclo: 'Ciclo II',
                programa: 'Educación Primaria PPD',
                silaboListo: false,
                progreso: {'calificado': 25.0},
              ),
            ],
          ),
        ],
        child: const MaterialApp(home: DocenteHomeScreen()),
      ),
    );
    // El fondo del Drawer y las animaciones de progreso no "asientan" nunca;
    // con un par de pumps basta para renderizar los AsyncValue resueltos.
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 200));

    expect(find.text('Mi panel'), findsOneWidget);
    expect(find.text('Período académico 2026-I'), findsOneWidget);
    expect(find.text('8'), findsOneWidget);
    expect(find.text('4 FID · 4 PPD'), findsOneWidget);
    expect(find.text('1 pendientes'), findsOneWidget);
    expect(find.text('Investigación II'), findsOneWidget);
    expect(find.text('Sílabo pendiente'), findsOneWidget);
    expect(find.text('25%'), findsOneWidget);
  });
}
