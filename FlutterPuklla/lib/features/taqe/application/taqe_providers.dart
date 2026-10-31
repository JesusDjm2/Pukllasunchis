import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/taqe_data.dart';
import '../data/taqe_repository.dart';

final taqeDataProvider = FutureProvider<TaqeData>((ref) async {
  final repository = ref.watch(taqeRepositoryProvider);
  return repository.fetchTaqeData();
});
