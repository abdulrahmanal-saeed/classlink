import 'package:firebase_messaging/firebase_messaging.dart';
import 'auth_service.dart';
import 'api_service.dart';

class PushService {
  Future<void> registerDeviceToken() async {
    final token = await AuthService().token();
    if (token == null) return;
    final fcmToken = await FirebaseMessaging.instance.getToken();
    if (fcmToken == null || fcmToken.isEmpty) return;
    await ApiService(token: token).post('/api/push/register-device', {
      'device_token': fcmToken,
      'platform': 'android',
      'device_label': 'Flutter mobile app',
      'app_version': '1.0.0',
    });
  }
}
