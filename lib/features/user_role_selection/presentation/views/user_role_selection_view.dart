import 'package:flutter/material.dart';
import '../../../../core/resources/color_manager.dart';
import '../../../../core/resources/styles_manager.dart';
import '../../../../core/resources/values_manager.dart';
import '../../widgets/parent_link_widget.dart';
import '../../widgets/proceed_button.dart';
import '../../widgets/role_selection_widget.dart';

class UserRoleSelectionView extends StatelessWidget {
  const UserRoleSelectionView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: ColorManager.background,
      body: Center(
        child: Padding(
          padding: EdgeInsets.all(Insets.large),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              LinearProgressIndicator(
                value: 0.5,
                backgroundColor: Colors.grey.shade200,
                valueColor:
                    const AlwaysStoppedAnimation<Color>(ColorManager.primary),
              ),
              SizedBox(height: Insets.large),
              Text(
                'Who are you?',
                style: StylesManager.headingStyle,
              ),
              SizedBox(height: Insets.large),
              const RoleSelectionWidget(),
              SizedBox(height: Insets.large),
              Text(
                "To provide you with a personalized experience, please select your role.",
                style: StylesManager.bodyTextStyle,
                textAlign: TextAlign.center,
              ),
              SizedBox(height: Insets.large),
              const ProceedButton(),
              SizedBox(height: Insets.medium),
              const ParentLinkWidget(),
            ],
          ),
        ),
      ),
    );
  }
}
