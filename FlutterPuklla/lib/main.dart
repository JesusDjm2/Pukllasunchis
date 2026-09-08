import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'routing/app_router.dart';

void main() {
  runApp(const ProviderScope(child: FlutterPukllaApp()));
}

class FlutterPukllaApp extends ConsumerWidget {
  const FlutterPukllaApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(appRouterProvider);

    return MaterialApp.router(
      title: 'Pukllasunchis',
      debugShowCheckedModeBanner: false,
      routerConfig: router,
    );
  }
}
