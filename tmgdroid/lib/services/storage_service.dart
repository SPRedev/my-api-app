import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class StorageService {
  // Create an instance of the secure storage
  final _secureStorage = const FlutterSecureStorage();

  // A key to identify our token in storage.
  // Using a constant makes it safe from typos.
  static const _tokenKey = 'api_token';

  /// Write the API token to secure storage.
  Future<void> saveToken(String token) async {
    await _secureStorage.write(key: _tokenKey, value: token);
    print('Token saved securely.'); // For debugging
  }

  /// Read the API token from secure storage.
  /// Returns the token string if it exists, otherwise returns null.
  Future<String?> readToken() async {
    final token = await _secureStorage.read(key: _tokenKey);
    if (token != null) {
      print('Token found in storage.'); // For debugging
    } else {
      print('No token found in storage.'); // For debugging
    }
    return token;
  }

  /// Delete the API token from secure storage.
  Future<void> deleteToken() async {
    await _secureStorage.delete(key: _tokenKey);
    print('Token deleted.'); // For debugging
  }
}
