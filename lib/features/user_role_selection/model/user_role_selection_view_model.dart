import '../../../../core/resources/assets_manager.dart';

class UserRoleSelectionViewModel {
  final String role;
  final String imagePath;

  UserRoleSelectionViewModel({required this.role, required this.imagePath});
}

final List<UserRoleSelectionViewModel> roles = [
  UserRoleSelectionViewModel(
      role: 'Teacher', imagePath: ImageAssets.teacherImage),
  UserRoleSelectionViewModel(
      role: 'Student', imagePath: ImageAssets.studentImage),
];
