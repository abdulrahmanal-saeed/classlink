import 'package:flutter/material.dart';
import '../resources/color_manager.dart';

class ProgressIndicatorWidget extends StatelessWidget {
  const ProgressIndicatorWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return LinearProgressIndicator(
      value: 0.5, // مستوى التقدم
      backgroundColor: Colors.grey.shade200,
      valueColor: const AlwaysStoppedAnimation<Color>(ColorManager.primary),
    );
  }
}
