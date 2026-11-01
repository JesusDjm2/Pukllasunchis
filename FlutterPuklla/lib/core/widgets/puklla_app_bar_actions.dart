import 'package:flutter/material.dart';

import '../../features/notificaciones/presentation/notification_bell_action.dart';

/// Acciones estándar del AppBar en la parte derecha superior:
/// - Campana de notificaciones con badge en tiempo real
/// - Botón de menú hamburguesa accesible para el pulgar derecho (abre el endDrawer)
class PukllaAppBarActions extends StatelessWidget {
  const PukllaAppBarActions({super.key, this.extraActions = const []});

  final List<Widget> extraActions;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        ...extraActions,
        const NotificationBellAction(),
        Builder(
          builder: (innerContext) => IconButton(
            icon: const Icon(Icons.menu_rounded),
            tooltip: 'Menú principal',
            onPressed: () {
              final scaffold = Scaffold.maybeOf(innerContext);
              if (scaffold != null && scaffold.hasEndDrawer) {
                scaffold.openEndDrawer();
              } else if (scaffold != null && scaffold.hasDrawer) {
                scaffold.openDrawer();
              }
            },
          ),
        ),
        const SizedBox(width: 4),
      ],
    );
  }
}
