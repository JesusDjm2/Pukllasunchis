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
    await tester.pumpAndSettle();

    expect(find.text('Pukllasunchis'), findsWidgets);
    expect(find.text('Ingresar'), findsOneWidget);
  });
}
