import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/dashboard_card.dart';

class ParentDashboardScreen extends StatefulWidget {
  const ParentDashboardScreen({super.key, required this.token});
  final String token;
  @override
  State<ParentDashboardScreen> createState() => _ParentDashboardScreenState();
}

class _ParentDashboardScreenState extends State<ParentDashboardScreen> {
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
        final children = (d['children'] as List?) ?? [];
        return ListView(
          padding: const EdgeInsets.all(16),
          children: [
            DashboardCard(title: 'Children', icon: Icons.family_restroom, child: children.isEmpty ? const Text('No linked children.') : Column(children: children.map((x) => ListTile(title: Text((x['display_name'] ?? 'Child').toString()))).toList())),
            DashboardCard(title: 'Parent features', icon: Icons.school, child: const Text('Child lessons, homework status, notes, booking, and balance will expand here.')),
          ],
        );
      },
    );
  }
}
