import 'package:flutter/material.dart';
import 'package:classlink/core/resources/color_manager.dart';
import 'package:classlink/core/resources/values_manager.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

class StylesManager {
  // النصوص الرئيسية
  static TextStyle headingStyle = TextStyle(
    fontSize: Sizes.headingFontSize,
    fontWeight: FontWeight.bold,
    color: ColorManager.textPrimary,
  );

  static TextStyle subHeadingStyle = TextStyle(
    fontSize: Sizes.subHeadingFontSize,
    fontWeight: FontWeight.w600,
    color: ColorManager.textSecondary,
  );

  static TextStyle bodyTextStyle = TextStyle(
    fontSize: Sizes.bodyFontSize,
    fontWeight: FontWeight.normal,
    color: ColorManager.textSecondary,
  );

  static TextStyle buttonTextStyle = TextStyle(
    fontSize: Sizes.buttonFontSize,
    fontWeight: FontWeight.bold,
    color: Colors.white,
  );

  // إعدادات الزر مع التدرج اللوني
  static ButtonStyle gradientButtonStyle = ButtonStyle(
    padding: WidgetStateProperty.all(
      EdgeInsets.symmetric(horizontal: 50.w, vertical: 15.h),
    ),
    shape: WidgetStateProperty.all(
      RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(30.r),
      ),
    ),
    backgroundColor: WidgetStateProperty.all(Colors.transparent),
  );

  // نمط للتدرج اللوني يمكن استخدامه على الأزرار
  static BoxDecoration gradientDecoration = BoxDecoration(
    gradient: const LinearGradient(
      colors: [ColorManager.gradientStart, ColorManager.gradientEnd],
      begin: Alignment.topLeft,
      end: Alignment.bottomRight,
    ),
    borderRadius: BorderRadius.circular(30.r),
    boxShadow: [
      BoxShadow(
        color: ColorManager.shadowColor,
        spreadRadius: 3.r,
        blurRadius: 6.r,
        offset: Offset(0, 3.h),
      ),
    ],
  );

  // Shadow decoration for images
  static BoxDecoration shadowDecoration = BoxDecoration(
    boxShadow: [
      BoxShadow(
        color: ColorManager.shadowColor.withOpacity(0.1),
        spreadRadius: 3.r,
        blurRadius: 50.r,
        offset: Offset(0, 6.h),
      ),
    ],
  );
}
