import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../features/auth/application/auth_controller.dart';
import '../features/auth/application/auth_state.dart';
import '../features/auth/data/auth_user.dart';
import '../features/auth/presentation/login_screen.dart';
import '../features/calificaciones/presentation/calificaciones_screen.dart';
import '../features/comunicados/presentation/comunicados_screen.dart';
import '../features/cursos_especiales/presentation/curso_especial_detalle_screen.dart';
import '../features/cursos_especiales/presentation/cursos_especiales_screen.dart';
import '../features/docente/presentation/docente_home_screen.dart';
import '../features/docente/presentation/docente_incidencias_screen.dart';
import '../features/home/presentation/home_screen.dart';
import '../features/notificaciones/presentation/notificaciones_screen.dart';
import '../features/perfil/presentation/matricula_form_screen.dart';

/// Un usuario con perfil docente y sin perfil de alumno no tiene nada que ver
/// en la ficha técnica: aterriza en su propio panel.
String inicioPara(AuthUser? user) =>
    user != null && user.isDocente && !user.isAlumno ? '/docente' : '/home';

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
          final inicio = inicioPara(authState.user);
          if (onLoginPage) return inicio;
          // '/home' es la ficha del alumno: un docente sin perfil de alumno no
          // debe quedarse en esa vista vacía.
          if (state.matchedLocation == '/home' && inicio != '/home') return inicio;
          return null;
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
      GoRoute(
        path: '/cursos-especiales',
        builder: (context, state) => const CursosEspecialesScreen(),
      ),
      GoRoute(
        path: '/cursos-especiales/:id',
        builder: (context, state) {
          final cursoId = int.parse(state.pathParameters['id']!);
          return CursoEspecialDetalleScreen(cursoId: cursoId);
        },
      ),
      GoRoute(
        path: '/calificaciones',
        builder: (context, state) => const CalificacionesScreen(),
      ),
      GoRoute(
        path: '/comunicados',
        builder: (context, state) => const ComunicadosScreen(),
      ),
      GoRoute(
        path: '/notificaciones',
        builder: (context, state) => const NotificacionesScreen(),
      ),
      GoRoute(
        path: '/matricula/formulario',
        builder: (context, state) => const MatriculaFormScreen(),
      ),
      GoRoute(
        path: '/docente',
        builder: (context, state) => const DocenteHomeScreen(),
      ),
      GoRoute(
        path: '/docente/incidencias',
        builder: (context, state) => const DocenteIncidenciasScreen(),
      ),
    ],
  );
});
