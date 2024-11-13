import 'package:flutter/material.dart';

import '../../../core/resources/color_manager.dart';

class ParentLinkWidget extends StatelessWidget {
  const ParentLinkWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        // انتقال إلى صفحة أولياء الأمور
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) =>
                const Placeholder(), // هنا يجب استبدال Placeholder بالصفحة الفعلية لأولياء الأمور
          ),
        );
      },
      child: const Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.person,
            color: ColorManager.primary,
            size: 18,
          ),
          SizedBox(width: 5),
          Text(
            "If you're a parent, click here",
            style: TextStyle(
              color: ColorManager.primary,
              decoration: TextDecoration.underline,
              fontSize: 16,
            ),
          ),
        ],
      ),
    );
  }
}
