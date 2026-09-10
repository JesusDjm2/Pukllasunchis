import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../routing/app_drawer.dart';
import '../../auth/application/auth_controller.dart';
import '../../perfil/presentation/ficha_tecnica_screen.dart';

/// Pantalla de aterrizaje tras el login. El contenido real (ficha técnica)
/// vive en `FichaTecnicaScreen` — esta pantalla solo aporta el andamiaje
/// compartido (AppBar, Drawer) y el gate por rol.
class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authControllerProvider).user;

    return Scaffold(
      appBar: AppBar(title: const Text('Ficha técnica')),
      drawer: const AppDrawer(),
      body: user != null && user.isAlumno
          ? const FichaTecnicaScreen()
          : const Center(
              child: Padding(
                padding: EdgeInsets.all(24),
                child: Text(
                  'Esta vista todavía solo está disponible para alumnos.',
                  textAlign: TextAlign.center,
                ),
              ),
            ),
    );
  }
}
