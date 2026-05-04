import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';

class AuthSession {
  AuthSession({required this.token, required this.user});
  final String token;
  final Map<String, dynamic> user;
}

class AuthService {
  static const _tokenKey = 'mobile_token';
  static const _roleKey = 'mobile_role';

  Future<AuthSession> login(String email, String password) async {
    final api = ApiService();
    final data = await api.post('/api/mobile/login', {
      'email': email,
      'password': password,
      'platform': 'flutter_android',
      'device_label': 'Flutter app',
    });
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, data['token'] as String);
    await prefs.setString(_roleKey, data['user']['role'] as String);
    return AuthSession(token: data['token'] as String, user: data['user'] as Map<String, dynamic>);
  }

  Future<String?> token() async => (await SharedPreferences.getInstance()).getString(_tokenKey);
  Future<String?> role() async => (await SharedPreferences.getInstance()).getString(_roleKey);

  Future<void> logout() async {
    final current = await token();
    if (current != null) {
      try { await ApiService(token: current).post('/api/mobile/logout', {}); } catch (_) {}
    }
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_roleKey);
  }
}
