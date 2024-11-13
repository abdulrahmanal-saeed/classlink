import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/resources/styles_manager.dart';
import '../../../../core/resources/values_manager.dart';
import '../../welcome_page/welcome_page.dart';
import '../view_model/user_role_selection_cubit.dart';

class ProceedButton extends StatelessWidget {
  const ProceedButton({super.key});

  @override
  Widget build(BuildContext context) {
    final cubit = context.read<UserRoleSelectionCubit>();

    return GestureDetector(
      onTap: () {
        if (cubit.state.selectedRole != null) {
          Navigator.pushReplacement(
            context,
            MaterialPageRoute(
              builder: (context) => const WelcomePage(),
            ),
          );
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text("Please select a role first")),
          );
        }
      },
      child: Container(
        width: Sizes.buttonWidth,
        height: Sizes.buttonHeight,
        decoration: StylesManager.gradientDecoration,
        alignment: Alignment.center,
        child: Text(
          'Proceed',
          style: StylesManager.buttonTextStyle,
        ),
      ),
    );
  }
}
