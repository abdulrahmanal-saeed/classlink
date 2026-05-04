import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'services/auth_service.dart';
import 'services/push_service.dart';
import 'screens/login_screen.dart';
import 'screens/role_home_screen.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  try {
    await Firebase.initializeApp();
  } catch (_) {
    // Firebase config files may not exist yet in development.
  }
  runApp(const HabibaNabilApp());
}

class HabibaNabilApp extends StatelessWidget {
  const HabibaNabilApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Habiba Nabil Academy',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF0F766E)),
        useMaterial3: true,
        fontFamily: 'Roboto',
      ),
      home: const AppBootstrap(),
    );
  }
}

class AppBootstrap extends StatefulWidget {
  const AppBootstrap({super.key});

  @override
  State<AppBootstrap> createState() => _AppBootstrapState();
}

class _AppBootstrapState extends State<AppBootstrap> {
  String? token;
  String? role;
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final auth = AuthService();
    token = await auth.token();
    role = await auth.role();
    if (token != null) {
      await PushService().registerDeviceToken();
    }
    if (mounted) setState(() => loading = false);
  }

  @override
  Widget build(BuildContext context) {
    if (loading) return const Scaffold(body: Center(child: CircularProgressIndicator()));
    if (token == null || role == null) return const LoginScreen();
    return RoleHomeScreen(token: token!, role: role!);
  }
}
