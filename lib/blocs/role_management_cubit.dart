import 'package:flutter_bloc/flutter_bloc.dart';

enum UserRole { student, parent, teacher }

class RoleManagementCubit extends Cubit<UserRole?> {
  RoleManagementCubit() : super(null);

  void setRole(UserRole role) {
    emit(role);
  }
}
