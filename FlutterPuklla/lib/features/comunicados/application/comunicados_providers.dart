import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/comunicados_page.dart';
import '../data/comunicados_repository.dart';

final comunicadosPageIndexProvider = StateProvider<int>((ref) => 1);

final comunicadosProvider = FutureProvider<ComunicadosPage>((ref) {
  final page = ref.watch(comunicadosPageIndexProvider);
  return ref.watch(comunicadosRepositoryProvider).fetchComunicados(page: page);
});
