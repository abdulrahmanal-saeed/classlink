import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import '../services/push_service.dart';
import 'role_home_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final emailController = TextEditingController();
  final passwordController = TextEditingController();
  bool loading = false;
  String? error;

  Future<void> _login() async {
    setState(() { loading = true; error = null; });
    try {
      final session = await AuthService().login(emailController.text.trim(), passwordController.text);
      await PushService().registerDeviceToken();
      if (!mounted) return;
      Navigator.of(context).pushReplacement(MaterialPageRoute(
        builder: (_) => RoleHomeScreen(token: session.token, role: session.user['role'] as String),
      ));
    } catch (e) {
      setState(() => error = e.toString());
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 420),
              child: Card(
                elevation: 0,
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const Text('Habiba Nabil Arabic Academy', style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 8),
                      const Text('Login to your mobile dashboard'),
                      const SizedBox(height: 24),
                      TextField(controller: emailController, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'Email')),
                      const SizedBox(height: 12),
                      TextField(controller: passwordController, obscureText: true, decoration: const InputDecoration(labelText: 'Password')),
                      if (error != null) ...[
                        const SizedBox(height: 12),
                        Text(error!, style: TextStyle(color: Theme.of(context).colorScheme.error)),
                      ],
                      const SizedBox(height: 24),
                      FilledButton(onPressed: loading ? null : _login, child: loading ? const CircularProgressIndicator() : const Text('Login')),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
