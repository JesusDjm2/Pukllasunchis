import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

final tokenStorageProvider = Provider<TokenStorage>((ref) => TokenStorage());

/// Guarda el token personal de Sanctum en el keystore/keychain del SO.
class TokenStorage {
  TokenStorage({FlutterSecureStorage? storage})
      : _storage = storage ?? const FlutterSecureStorage();

  static const _tokenKey = 'auth_token';
  static const _usernameKey = 'saved_username';

  final FlutterSecureStorage _storage;

  Future<String?> readToken() => _storage.read(key: _tokenKey);

  Future<void> saveToken(String token) => _storage.write(key: _tokenKey, value: token);

  Future<void> deleteToken() => _storage.delete(key: _tokenKey);

  Future<String?> readSavedUsername() async {
    try {
      return await _storage.read(key: _usernameKey);
    } catch (_) {
      return null;
    }
  }

  Future<void> saveUsername(String username) async {
    try {
      await _storage.write(key: _usernameKey, value: username);
    } catch (_) {}
  }

  Future<void> deleteSavedUsername() async {
    try {
      await _storage.delete(key: _usernameKey);
    } catch (_) {}
  }
}
