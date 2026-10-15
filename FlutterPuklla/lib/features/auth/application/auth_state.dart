import '../data/auth_user.dart';

enum AuthStatus {
  /// Restaurando sesión desde el token guardado — aún no se sabe si hay
  /// sesión válida.
  unknown,
  authenticated,
  unauthenticated,
}

class AuthState {
  const AuthState._(this.status, this.user);

  const AuthState.unknown() : this._(AuthStatus.unknown, null);

  const AuthState.authenticated(AuthUser user) : this._(AuthStatus.authenticated, user);

  const AuthState.unauthenticated() : this._(AuthStatus.unauthenticated, null);

  final AuthStatus status;
  final AuthUser? user;
}
