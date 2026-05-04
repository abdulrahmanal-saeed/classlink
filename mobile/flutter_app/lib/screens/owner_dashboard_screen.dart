import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/dashboard_card.dart';

class OwnerDashboardScreen extends StatefulWidget {
  const OwnerDashboardScreen({super.key, required this.token});
  final String token;
  @override
  State<OwnerDashboardScreen> createState() => _OwnerDashboardScreenState();
}

class _OwnerDashboardScreenState extends State<OwnerDashboardScreen> {
  late Future<Map<String, dynamic>> data;
  @override
  void initState() {
    super.initState();
    data = ApiService(token: widget.token).get('/api/mobile/dashboard');
  }

  void reload() {
    setState(() { data = ApiService(token: widget.token).get('/api/mobile/dashboard'); });
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<Map<String, dynamic>>(
      future: data,
      builder: (context, snap) {
        if (snap.connectionState != ConnectionState.done) return const Center(child: CircularProgressIndicator());
        if (snap.hasError) return Center(child: Text(snap.error.toString()));
        final d = snap.data!['dashboard'] as Map<String, dynamic>;
        final lessons = (d['today_lessons'] as List?) ?? [];
        return ListView(
          padding: const EdgeInsets.all(16),
          children: [
            DashboardCard(title: 'Today lessons', icon: Icons.calendar_today, child: lessons.isEmpty ? const Text('No lessons today.') : Column(children: lessons.map((x) => ListTile(title: Text((x['student_name'] ?? 'Student').toString()), subtitle: Text((x['start_at'] ?? '').toString()))).toList())),
            DashboardCard(title: 'Homework submissions', icon: Icons.assignment, child: Text('${d['homework_submissions_pending'] ?? 0} pending')),
            DashboardCard(title: 'Scenario submissions', icon: Icons.mic, child: Text('${d['scenario_submissions_pending'] ?? 0} pending')),
            DashboardCard(title: 'Next features', icon: Icons.mobile_friendly, child: const Text('Student list, WhatsApp actions, attendance, and lesson notes will expand here.')),
            FilledButton(onPressed: reload, child: const Text('Refresh')),
          ],
        );
      },
    );
  }
}
