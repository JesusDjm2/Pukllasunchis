import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../auth/application/auth_controller.dart';

/// Placeholder — el dashboard real (periodo actual, resumen de cursos, etc.)
/// es la próxima tarea del plan de Fase 2. Por ahora confirma que el login,
/// la restauración de sesión y el logout funcionan de punta a punta.
class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authControllerProvider).user;

    return Scaffold(
      appBar: AppBar(
        title: Text(user != null ? 'Hola, ${user.name}' : 'Pukllasunchis'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            tooltip: 'Cerrar sesión',
            onPressed: () => ref.read(authControllerProvider.notifier).logout(),
          ),
        ],
      ),
      body: const Center(child: Text('Dashboard pendiente')),
    );
  }
}
