import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:classlink/core/resources/color_manager.dart';
import 'package:classlink/core/resources/styles_manager.dart';
import 'package:classlink/core/resources/values_manager.dart';
import '../../../core/resources/assets_manager.dart';
import '../../../core/utils/validator.dart';

class ParentLoginPage extends StatefulWidget {
  const ParentLoginPage({super.key});

  @override
  State<ParentLoginPage> createState() => _ParentLoginPageState();
}

class _ParentLoginPageState extends State<ParentLoginPage> {
  final _formKey = GlobalKey<FormState>();

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
                    Container(
                      decoration: StylesManager.shadowDecoration,
                      child: Image.asset(
                        ImageAssets.parentImage,
                        height: Sizes.imageHeight,
                      ),
                    ),
                    SizedBox(height: 30.h),
                    Text(
                      'Parent Login',
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
                            // كود الطالب
                            _buildTextFormField(
                              hintText: 'Student Code',
                              icon: Icons.code,
                              validator: Validator.validateUsername,
                            ),
                            SizedBox(height: Insets.medium),
                            // البريد الإلكتروني
                            _buildTextFormField(
                              hintText: 'Email',
                              icon: Icons.email,
                              validator: Validator.validateEmail,
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

  Widget _buildTextFormField({
    required String hintText,
    required IconData icon,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      validator: validator,
      decoration: InputDecoration(
        prefixIcon: Icon(icon, color: ColorManager.textSecondary),
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
