import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'core/resources/color_manager.dart';
import 'features/user_role_selection/view_model/user_role_selection_cubit.dart';
import 'features/welcome_page/welcome_page.dart';

void main() {
  // ضبط لون شريط الحالة
  SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(
    statusBarColor: Colors.transparent, // يجعل الشريط شفافًا
    statusBarIconBrightness:
        Brightness.dark, // اضبط أيقونات الشريط لتناسب خلفية فاتحة
  ));
  runApp(const ClassLink());
}

class ClassLink extends StatelessWidget {
  const ClassLink({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiBlocProvider(
      providers: [
        BlocProvider<UserRoleSelectionCubit>(
          create: (context) => UserRoleSelectionCubit(),
        ),
      ],
      child: ScreenUtilInit(
        designSize: const Size(375, 812), // تحديد أبعاد التصميم الأساسي
        minTextAdapt: true,
        splitScreenMode: true,
        builder: (context, child) {
          return MaterialApp(
            debugShowCheckedModeBanner: false,
            title: 'ClassLink',
            theme: ThemeData(
              primaryColor: ColorManager.primary,
              scaffoldBackgroundColor: ColorManager.background,
              visualDensity: VisualDensity.adaptivePlatformDensity,
            ),
            home: child,
          );
        },
        child: const WelcomePage(),
      ),
    );
  }
}
