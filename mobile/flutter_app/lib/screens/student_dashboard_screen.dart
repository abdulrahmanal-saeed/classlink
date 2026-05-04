import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/dashboard_card.dart';

class StudentDashboardScreen extends StatefulWidget {
  const StudentDashboardScreen({super.key, required this.token});
  final String token;
  @override
  State<StudentDashboardScreen> createState() => _StudentDashboardScreenState();
}

class _StudentDashboardScreenState extends State<StudentDashboardScreen> {
  late Future<Map<String, dynamic>> data;
  @override
  void initState() {
    super.initState();
    data = ApiService(token: widget.token).get('/api/mobile/dashboard');
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<Map<String, dynamic>>(
      future: data,
      builder: (context, snap) {
        if (snap.connectionState != ConnectionState.done) return const Center(child: CircularProgressIndicator());
        if (snap.hasError) return Center(child: Text(snap.error.toString()));
        final d = snap.data!['dashboard'] as Map<String, dynamic>;
        final lesson = d['upcoming_lesson'];
        final homework = (d['homework'] as List?) ?? [];
        return ListView(
          padding: const EdgeInsets.all(16),
          children: [
            DashboardCard(title: 'Upcoming lesson', icon: Icons.event, child: Text(lesson == null ? 'No upcoming lesson.' : '${lesson['start_at']} · ${lesson['status']}')),
            DashboardCard(title: 'Package balance', icon: Icons.account_balance_wallet, child: Text('${d['balance'] ?? 0} credits')),
            DashboardCard(title: 'Homework', icon: Icons.assignment, child: homework.isEmpty ? const Text('No homework yet.') : Column(children: homework.map((x) => ListTile(title: Text((x['title'] ?? 'Homework').toString()), subtitle: Text((x['due_at'] ?? '').toString()))).toList())),
            DashboardCard(title: 'Practice', icon: Icons.style, child: const Text('Flashcards, scenario recording, and notifications will expand here.')),
          ],
        );
      },
    );
  }
}
