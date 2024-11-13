import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/resources/color_manager.dart';
import '../../../../core/resources/styles_manager.dart';
import '../view_model/user_role_selection_cubit.dart';
import '../../../../core/resources/assets_manager.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

class RoleSelectionWidget extends StatelessWidget {
  const RoleSelectionWidget({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<UserRoleSelectionCubit, UserRoleSelectionState>(
      builder: (context, state) {
        return Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const RoleCard(
                role: 'Teacher', imagePath: ImageAssets.teacherImage),
            SizedBox(width: 14.w),
            const RoleCard(
                role: 'Student', imagePath: ImageAssets.studentImage),
          ],
        );
      },
    );
  }
}

class RoleCard extends StatelessWidget {
  final String role;
  final String imagePath;

  const RoleCard({super.key, required this.role, required this.imagePath});

  @override
  Widget build(BuildContext context) {
    final cubit = context.read<UserRoleSelectionCubit>();
    final isSelected = cubit.state.selectedRole == role;

    return GestureDetector(
      onTap: () => cubit.selectRole(role),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20.r),
          boxShadow: [
            BoxShadow(
              color: Colors.black12,
              spreadRadius: isSelected ? 4.r : 2.r,
              blurRadius: isSelected ? 15.r : 10.r,
              offset: Offset(0, 5.h),
            ),
          ],
          border: Border.all(
            color: isSelected ? ColorManager.primary : Colors.transparent,
            width: isSelected ? 2.w : 0,
          ),
        ),
        padding: EdgeInsets.all(6.w),
        child: Column(
          children: [
            Image.asset(imagePath, height: 150.h, fit: BoxFit.contain),
            SizedBox(height: 10.h),
            Text(
              role,
              style: StylesManager.subHeadingStyle.copyWith(
                color: isSelected ? ColorManager.primary : Colors.black,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
