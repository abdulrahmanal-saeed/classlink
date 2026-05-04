import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'login_screen.dart';
import 'owner_dashboard_screen.dart';
import 'student_dashboard_screen.dart';
import 'parent_dashboard_screen.dart';

class RoleHomeScreen extends StatelessWidget {
  const RoleHomeScreen({super.key, required this.token, required this.role});
  final String token;
  final String role;

  Future<void> logout(BuildContext context) async {
    await AuthService().logout();
    if (!context.mounted) return;
    Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const LoginScreen()));
  }

  @override
  Widget build(BuildContext context) {
    final Widget body = switch (role) {
      'owner_teacher' => OwnerDashboardScreen(token: token),
      'student' => StudentDashboardScreen(token: token),
      'parent' => ParentDashboardScreen(token: token),
      _ => const Center(child: Text('Unsupported mobile role.')),
    };

    return Scaffold(
      appBar: AppBar(
        title: const Text('Habiba Nabil'),
        actions: [IconButton(onPressed: () => logout(context), icon: const Icon(Icons.logout))],
      ),
      body: body,
    );
  }
}
