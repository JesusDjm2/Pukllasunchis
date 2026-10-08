import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../application/notificaciones_providers.dart';

/// Campanita con badge de no leídas — se agrega al AppBar de cada pantalla
/// principal (ver HomeScreen, CursosEspecialesScreen, CalificacionesScreen,
/// ComunicadosScreen).
class NotificationBellAction extends ConsumerWidget {
  const NotificationBellAction({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final count = ref.watch(noLeidasCountProvider).valueOrNull ?? 0;

    return IconButton(
      icon: Badge(
        label: Text('$count'),
        isLabelVisible: count > 0,
        child: const Icon(Icons.notifications_outlined),
      ),
      tooltip: 'Notificaciones',
      onPressed: () => context.push('/notificaciones'),
    );
  }
}
