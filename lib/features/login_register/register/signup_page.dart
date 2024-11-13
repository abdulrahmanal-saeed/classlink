import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:classlink/core/resources/color_manager.dart';
import 'package:classlink/core/resources/styles_manager.dart';
import 'package:classlink/core/resources/values_manager.dart';
import 'package:classlink/core/resources/assets_manager.dart';
import '../../../core/utils/validator.dart';
import '../../welcome_page/user_role_selection_page.dart';
import '../login/login_page.dart';

class SignupPage extends StatefulWidget {
  const SignupPage({super.key});

  @override
  State<SignupPage> createState() => _SignupPageState();
}

class _SignupPageState extends State<SignupPage> {
  final _formKey = GlobalKey<FormState>();
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;

  // متغيرات لحفظ المدخلات من الحقول
  String? _password;

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
                  Navigator.pushReplacement(
                    context,
                    MaterialPageRoute(
                        builder: (context) => const UserRoleSelectionPage()),
                  );
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
                        ImageAssets.signupImage,
                        height: Sizes.imageHeight,
                      ),
                    ),
                    SizedBox(height: 20.h),
                    Text(
                      'Join us today!',
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
                              hintText: 'UserName',
                              icon: Icons.person_2,
                              validator: Validator.validateUsername,
                            ),
                            SizedBox(height: Insets.medium),
                            _buildTextFormField(
                              hintText: 'Email',
                              icon: Icons.email,
                              validator: Validator.validateEmail,
                            ),
                            SizedBox(height: Insets.medium),
                            _buildTextFormField(
                              hintText: 'Phone',
                              icon: Icons.phone,
                              validator: Validator.validatePhoneNumber,
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
                              validator: (val) {
                                _password = val;
                                return Validator.validatePassword(val);
                              },
                            ),
                            SizedBox(height: Insets.medium),
                            _buildTextFormField(
                              hintText: 'Confirm Password',
                              icon: Icons.lock,
                              obscureText: _obscureConfirmPassword,
                              togglePasswordView: () {
                                setState(() {
                                  _obscureConfirmPassword =
                                      !_obscureConfirmPassword;
                                });
                              },
                              validator: (val) =>
                                  Validator.validateConfirmPassword(
                                      val, _password),
                            ),
                          ],
                        ),
                      ),
                    ),
                    SizedBox(height: Insets.large),
                    GestureDetector(
                      onTap: () {
                        if (_formKey.currentState!.validate()) {
                          // تنفيذ عملية التسجيل إذا كان التحقق ناجحًا
                        }
                      },
                      child: Container(
                        width: Sizes.buttonWidth,
                        height: Sizes.buttonHeight,
                        decoration: StylesManager.gradientDecoration,
                        alignment: Alignment.center,
                        child: Text(
                          'Signup',
                          style: StylesManager.buttonTextStyle,
                        ),
                      ),
                    ),
                    SizedBox(height: Insets.medium),
                    GestureDetector(
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                              builder: (context) => const LoginPage()),
                        );
                      },
                      child: Text(
                        "If you have an account, click here",
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
