import 'package:flutter_bloc/flutter_bloc.dart';

class UserRoleSelectionCubit extends Cubit<UserRoleSelectionState> {
  UserRoleSelectionCubit() : super(UserRoleSelectionState());

  void selectRole(String role) {
    emit(state.copyWith(selectedRole: role));
  }
}

class UserRoleSelectionState {
  final String? selectedRole;

  UserRoleSelectionState({this.selectedRole});

  UserRoleSelectionState copyWith({String? selectedRole}) {
    return UserRoleSelectionState(
      selectedRole: selectedRole ?? this.selectedRole,
    );
  }
}
