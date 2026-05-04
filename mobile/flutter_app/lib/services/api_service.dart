import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/app_config.dart';

class ApiException implements Exception {
  final String message;
  ApiException(this.message);
  @override
  String toString() => message;
}

class ApiService {
  ApiService({this.token});
  final String? token;

  Uri _url(String path) => Uri.parse('${AppConfig.backendBaseUrl}$path');

  Map<String, String> get _headers => {
        'Content-Type': 'application/json',
        if (token != null && token!.isNotEmpty) 'Authorization': 'Bearer $token',
      };

  Future<Map<String, dynamic>> post(String path, Map<String, dynamic> body) async {
    final response = await http.post(_url(path), headers: _headers, body: jsonEncode(body));
    return _decode(response);
  }

  Future<Map<String, dynamic>> get(String path) async {
    final response = await http.get(_url(path), headers: _headers);
    return _decode(response);
  }

  Map<String, dynamic> _decode(http.Response response) {
    final data = jsonDecode(response.body.isEmpty ? '{}' : response.body) as Map<String, dynamic>;
    if (response.statusCode >= 400 || data['ok'] == false) {
      throw ApiException((data['error'] ?? 'Request failed').toString());
    }
    return data;
  }
}
