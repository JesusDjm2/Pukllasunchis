import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/api_exception.dart';
import '../data/auth_repository.dart';
import 'auth_state.dart';

final authControllerProvider = StateNotifierProvider<AuthController, AuthState>((ref) {
  return AuthController(ref.watch(authRepositoryProvider));
});

class AuthController extends StateNotifier<AuthState> {
  AuthController(this._repository) : super(const AuthState.unknown()) {
    _restoreSession();
  }

  final AuthRepository _repository;

  Future<void> _restoreSession() async {
    if (!await _repository.hasStoredToken()) {
      state = const AuthState.unauthenticated();
      return;
    }
    try {
      final user = await _repository.me();
      state = AuthState.authenticated(user);
    } on ApiException {
      // Token guardado ya no es válido (expiró / fue revocado en el server).
      await _repository.logout(silent: true);
      state = const AuthState.unauthenticated();
    }
  }

  Future<void> login({required String email, required String password}) async {
    final user = await _repository.login(email: email, password: password);
    state = AuthState.authenticated(user);
  }

  Future<void> logout() async {
    await _repository.logout();
    state = const AuthState.unauthenticated();
  }

  /// Cierra sesión localmente sin llamar a `/logout` — para cuando el propio
  /// servidor ya rechazó el token (401 en cualquier request, ver
  /// `dio_client.dart`); pedirle que lo revoque de nuevo sería redundante.
  Future<void> forceLogout() async {
    if (state.status == AuthStatus.unauthenticated) return;
    await _repository.logout(silent: true);
    state = const AuthState.unauthenticated();
  }
}
