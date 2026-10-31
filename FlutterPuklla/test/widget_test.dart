import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:flutter_puklla/core/storage/token_storage.dart';
import 'package:flutter_puklla/main.dart';

/// Evita tocar el plugin nativo de `flutter_secure_storage` (no disponible
/// en `flutter test`, que no tiene una plataforma real detrás).
class _FakeTokenStorage extends TokenStorage {
  @override
  Future<String?> readToken() async => null;
}

void main() {
  testWidgets('sin sesión guardada, la app aterriza en la pantalla de login', (tester) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [tokenStorageProvider.overrideWithValue(_FakeTokenStorage())],
        child: const FlutterPukllaApp(),
      ),
    );
    // No pumpAndSettle: el fondo del login tiene una animación de zoom que
    // se repite indefinidamente ("respira"), así que nunca "se asienta".
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 100));

    expect(find.text('Bienvenido(a)'), findsOneWidget);
    expect(find.text('Ingresar'), findsOneWidget);
  });
}
