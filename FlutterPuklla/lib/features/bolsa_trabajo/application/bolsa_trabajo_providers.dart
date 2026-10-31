import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/bolsa_trabajo_oferta.dart';
import '../data/bolsa_trabajo_repository.dart';

final bolsaTrabajoProvider = FutureProvider<List<BolsaTrabajoOferta>>((ref) {
  return ref.watch(bolsaTrabajoRepositoryProvider).fetchOfertas();
});
