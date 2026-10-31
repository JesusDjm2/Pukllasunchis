import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../data/tutoria_data.dart';
import '../data/tutoria_repository.dart';

final tutoriaDataProvider = FutureProvider<TutoriaData>((ref) async {
  final repository = ref.watch(tutoriaRepositoryProvider);
  return repository.fetchTutoriaData();
});
