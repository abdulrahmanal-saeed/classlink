import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:classlink/core/resources/color_manager.dart';
import 'package:classlink/core/resources/styles_manager.dart';
import 'package:classlink/core/resources/values_manager.dart';
import 'package:classlink/core/resources/assets_manager.dart';

import '../../../core/utils/validator.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _formKey = GlobalKey<FormState>();
  bool _obscurePassword = true;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: true,
      backgroundColor: ColorManager.background,
      body: SingleChildScrollView(
        child: Padding(
          padding: EdgeInsets.all(Insets.large),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              GestureDetector(
                onTap: () {
                  Navigator.pop(context);
                },
                child: const Icon(
                  Icons.arrow_back,
                  color: Colors.purple,
                  size: 28.0,
                ),
              ),
              Center(
                child: Column(
                  children: [
                    SizedBox(height: 30.h),
                    Container(
                      decoration: StylesManager.shadowDecoration,
                      child: Image.asset(
                        ImageAssets.loginImage,
                        height: Sizes.imageHeight,
                      ),
                    ),
                    SizedBox(height: 20.h),
                    Text(
                      'Welcome back!',
                      style: StylesManager.headingStyle,
                      textAlign: TextAlign.center,
                    ),
                    SizedBox(height: Insets.large),
                    Form(
                      key: _formKey,
                      child: Container(
                        padding: EdgeInsets.all(16.w),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16.r),
                          boxShadow: const [
                            BoxShadow(
                              color: Colors.black12,
                              spreadRadius: 1,
                              blurRadius: 8,
                              offset: Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Column(
                          children: [
                            _buildTextFormField(
                              hintText: 'Email',
                              icon: Icons.email,
                              validator: Validator.validateEmail,
                            ),
                            SizedBox(height: Insets.medium),
                            _buildTextFormField(
                              hintText: 'Password',
                              icon: Icons.lock,
                              obscureText: _obscurePassword,
                              togglePasswordView: () {
                                setState(() {
                                  _obscurePassword = !_obscurePassword;
                                });
                              },
                              validator: Validator.validatePassword,
                            ),
                          ],
                        ),
                      ),
                    ),
                    SizedBox(height: Insets.large),
                    GestureDetector(
                      onTap: () {
                        if (_formKey.currentState!.validate()) {
                          // تنفيذ عملية تسجيل الدخول إذا كان التحقق ناجحًا
                        }
                      },
                      child: Container(
                        width: Sizes.buttonWidth,
                        height: Sizes.buttonHeight,
                        decoration: StylesManager.gradientDecoration,
                        alignment: Alignment.center,
                        child: Text(
                          'Login',
                          style: StylesManager.buttonTextStyle,
                        ),
                      ),
                    ),
                    SizedBox(height: Insets.medium),
                    GestureDetector(
                      onTap: () {
                        // الانتقال إلى صفحة إنشاء حساب جديد
                      },
                      child: Text(
                        "Don't have an account? Sign up",
                        style: TextStyle(
                          color: ColorManager.primary,
                          decoration: TextDecoration.underline,
                          fontSize: Sizes.bodyFontSize,
                        ),
                      ),
                    ),
                    SizedBox(height: 20.h),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // عنصر لإعادة استخدام حقل الإدخال مع دعم الأيقونات والعين لإظهار/إخفاء كلمة المرور
  Widget _buildTextFormField({
    required String hintText,
    required IconData icon,
    bool obscureText = false,
    VoidCallback? togglePasswordView,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      obscureText: obscureText,
      validator: validator,
      decoration: InputDecoration(
        prefixIcon: Icon(icon, color: ColorManager.textSecondary),
        suffixIcon: togglePasswordView != null
            ? GestureDetector(
                onTap: togglePasswordView,
                child: Icon(
                  obscureText ? Icons.visibility_off : Icons.visibility,
                  color: ColorManager.textSecondary,
                ),
              )
            : null,
        hintText: hintText,
        hintStyle: StylesManager.bodyTextStyle
            .copyWith(color: ColorManager.textSecondary),
        filled: true,
        fillColor: Colors.grey[200],
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12.r),
          borderSide: BorderSide.none,
        ),
        contentPadding: EdgeInsets.symmetric(vertical: 14.h, horizontal: 16.w),
      ),
    );
  }
}
