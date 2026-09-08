import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../features/auth/application/auth_controller.dart';
import '../features/auth/application/auth_state.dart';
import '../features/auth/presentation/login_screen.dart';
import '../features/home/presentation/home_screen.dart';

/// Reconstruir el GoRouter completo cuando cambia el estado de auth es
/// aceptable para el tamaño de esta app; así el `redirect` de abajo siempre
/// ve el estado actual sin depender de un Listenable separado.
final appRouterProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authControllerProvider);

  return GoRouter(
    initialLocation: '/home',
    redirect: (context, state) {
      final onLoginPage = state.matchedLocation == '/login';

      switch (authState.status) {
        case AuthStatus.unknown:
          // Restaurando sesión desde el token guardado — cada pantalla
          // muestra su propio loading mientras tanto, no redirigimos aún.
          return null;
        case AuthStatus.unauthenticated:
          return onLoginPage ? null : '/login';
        case AuthStatus.authenticated:
          return onLoginPage ? '/home' : null;
      }
    },
    routes: [
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: '/home',
        builder: (context, state) => const HomeScreen(),
      ),
    ],
  );
});
