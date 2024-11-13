import 'package:classlink/core/resources/assets_manager.dart';
import 'package:classlink/core/resources/styles_manager.dart';
import 'package:classlink/core/resources/values_manager.dart';
import 'package:flutter/material.dart';
import '../../core/resources/color_manager.dart';
import 'user_role_selection_page.dart';

class WelcomePage extends StatefulWidget {
  const WelcomePage({super.key});

  @override
  State<WelcomePage> createState() => _WelcomePageState();
}

class _WelcomePageState extends State<WelcomePage>
    with SingleTickerProviderStateMixin {
  bool _isVisible = false;
  late AnimationController _controller;
  late Animation<Offset> _offsetAnimation;

  @override
  void initState() {
    super.initState();

    _controller = AnimationController(
      duration: const Duration(seconds: 2),
      vsync: this,
    );

    _offsetAnimation = Tween<Offset>(
      begin: const Offset(0, 0.5),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _controller,
      curve: Curves.easeOut,
    ));

    _controller.forward();
    setState(() {
      _isVisible = true;
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: ColorManager.background,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            AnimatedOpacity(
              opacity: _isVisible ? 1.0 : 0.0,
              duration: const Duration(seconds: 2),
              child: Container(
                decoration: StylesManager.shadowDecoration,
                child: Image.asset(
                  ImageAssets.welcomeImage,
                  height: Sizes.imageHeight,
                ),
              ),
            ),
            SizedBox(height: Insets.large),
            SlideTransition(
              position: _offsetAnimation,
              child: Text(
                'Welcome to ClassLink!',
                style: StylesManager.headingStyle,
                textAlign: TextAlign.center,
              ),
            ),
            SizedBox(height: Insets.medium),
            Padding(
              padding: EdgeInsets.symmetric(horizontal: Insets.medium),
              child: Text(
                "Get ready to gain new knowledge and valuable skills. Tap 'Start Learning' to begin your educational journey.",
                style: StylesManager.bodyTextStyle,
                textAlign: TextAlign.center,
              ),
            ),
            SizedBox(height: Insets.large),
            SlideTransition(
              position: _offsetAnimation,
              child: GestureDetector(
                onTap: () {
                  Navigator.pushReplacement(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const UserRoleSelectionPage(),
                    ),
                  );
                },
                child: Container(
                  width: Sizes.buttonWidth,
                  height: Sizes.buttonHeight,
                  decoration: StylesManager.gradientDecoration,
                  alignment: Alignment.center,
                  child: Text(
                    'Start Learning',
                    style: StylesManager.buttonTextStyle,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
