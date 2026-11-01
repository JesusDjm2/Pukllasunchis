import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:flutter_puklla/core/storage/token_storage.dart';
import 'package:flutter_puklla/features/auth/application/auth_controller.dart';
import 'package:flutter_puklla/features/auth/application/auth_state.dart';
import 'package:flutter_puklla/features/auth/data/auth_repository.dart';
import 'package:flutter_puklla/features/auth/data/auth_user.dart';
import 'package:flutter_puklla/features/recursos/presentation/formatos_biblioteca_screen.dart';

class _FakeTokenStorage extends TokenStorage {
  @override
  Future<String?> readToken() async => null;
}

class _FakeAuthRepository implements AuthRepository {
  _FakeAuthRepository(this._user);
  final AuthUser _user;

  @override
  Future<bool> hasStoredToken() async => true;

  @override
  Future<AuthUser> me() async => _user;

  @override
  Future<AuthUser> login({required String email, required String password}) async => _user;

  @override
  Future<void> logout({bool silent = false}) async {}
}

class _TestAuthController extends AuthController {
  _TestAuthController(AuthUser user) : super(_FakeAuthRepository(user)) {
    state = AuthState.authenticated(user);
  }
}

void main() {
  testWidgets('FormatosBibliotecaScreen muestra solo guias FID para alumno FID', (tester) async {
    tester.view.physicalSize = const Size(800, 1600);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(() => tester.view.resetPhysicalSize());

    const fidUser = AuthUser(
      id: 1,
      name: 'Maria',
      apellidos: 'Quispe',
      email: 'maria@pukllavirtual.edu.pe',
      roles: ['alumno'],
      alumnoId: 10,
      docenteId: null,
    );

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(_FakeTokenStorage()),
          authControllerProvider.overrideWith(
            (ref) => _TestAuthController(fidUser),
          ),
        ],
        child: const MaterialApp(
          home: FormatosBibliotecaScreen(),
        ),
      ),
    );

    await tester.pumpAndSettle();

    // 1. Título y Banner de la nueva Biblioteca Virtual
    expect(find.text('Guías de TI, Tesis & Biblioteca'), findsOneWidget);
    expect(find.text('Biblioteca Virtual Pukllasunchis'), findsOneWidget);

    // 2. No debe haber selectores ni tabs para cambiar a PPD
    expect(find.text('Formación Inicial (FID)'), findsNothing);
    expect(find.text('Profesionalización (PPD)'), findsNothing);

    // 3. Indicador informativo personalizado
    expect(find.text('Documentos para Formación Inicial (FID)'), findsOneWidget);

    // 4. Pestañas (3 tabs)
    expect(find.text('Investigación (TI)'), findsOneWidget);
    expect(find.text('Tesis'), findsOneWidget);
    expect(find.text('Videos (5)'), findsOneWidget);

    // 5. Solo documentos FID en TI
    expect(find.text('Formato de TI'), findsOneWidget);
    expect(find.text('Carátula de TI (FID)'), findsOneWidget);
    expect(find.text('Carátula de TI (PPD)'), findsNothing);

    // 6. Conmutar a la pestaña Tesis: solo documentos FID
    await tester.tap(find.text('Tesis'));
    await tester.pumpAndSettle();
    expect(find.text('Formato de Tesis'), findsOneWidget);
    expect(find.text('Carátula de Tesis (FID)'), findsOneWidget);
    expect(find.text('Carátula de Tesis (PPD)'), findsNothing);
  });

  testWidgets('FormatosBibliotecaScreen muestra solo guias PPD para alumno PPD', (tester) async {
    tester.view.physicalSize = const Size(800, 1600);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(() => tester.view.resetPhysicalSize());

    const ppdUser = AuthUser(
      id: 2,
      name: 'Rosa',
      apellidos: 'Fajardo',
      email: 'rosa@pukllavirtual.edu.pe',
      roles: ['alumnoB'],
      alumnoId: 15,
      docenteId: null,
    );

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(_FakeTokenStorage()),
          authControllerProvider.overrideWith(
            (ref) => _TestAuthController(ppdUser),
          ),
        ],
        child: const MaterialApp(
          home: FormatosBibliotecaScreen(),
        ),
      ),
    );

    await tester.pumpAndSettle();

    // 1. Indicador informativo PPD
    expect(find.text('Documentos para Profesionalización (PPD)'), findsOneWidget);

    // 2. No debe haber selector manual de cambio de modalidad
    expect(find.text('Formación Inicial (FID)'), findsNothing);
    expect(find.text('Profesionalización (PPD)'), findsNothing);

    // 3. Solo documentos PPD en TI
    expect(find.text('Formato de TI'), findsOneWidget);
    expect(find.text('Carátula de TI (PPD)'), findsOneWidget);
    expect(find.text('Carátula de TI (FID)'), findsNothing);

    // 4. Conmutar a la pestaña Tesis: solo documentos PPD
    await tester.tap(find.text('Tesis'));
    await tester.pumpAndSettle();
    expect(find.text('Formato de Tesis'), findsOneWidget);
    expect(find.text('Carátula de Tesis (PPD)'), findsOneWidget);
    expect(find.text('Carátula de Tesis (FID)'), findsNothing);
  });
}
