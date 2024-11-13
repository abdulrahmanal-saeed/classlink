import 'package:classlink/core/resources/color_manager.dart';
import 'package:classlink/core/resources/styles_manager.dart';
import 'package:classlink/features/login_register/register/signup_page.dart';
import 'package:classlink/features/welcome_page/welcome_page.dart';
import 'package:flutter/material.dart';
import '../../core/resources/values_manager.dart';
import '../login_register/parent_login/parent_register_page.dart';

class UserRoleSelectionPage extends StatefulWidget {
  const UserRoleSelectionPage({super.key});

  @override
  // ignore: library_private_types_in_public_api
  _UserRoleSelectionPageState createState() => _UserRoleSelectionPageState();
}

class _UserRoleSelectionPageState extends State<UserRoleSelectionPage>
    with SingleTickerProviderStateMixin {
  String? selectedRole;
  late AnimationController _controller;
  late Animation<Offset> _offsetAnimation;

  @override
  void initState() {
    super.initState();

    // إعداد AnimationController وتحريك النصوص والزر
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
    setState(() {});
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Widget _buildRoleCard({required String role, required String imagePath}) {
    bool isSelected = role == selectedRole;

    return GestureDetector(
      onTap: () {
        setState(() {
          selectedRole = role;
        });
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: Colors.black12,
              spreadRadius: isSelected ? 4 : 2,
              blurRadius: isSelected ? 15 : 10,
              offset: const Offset(0, 5),
            ),
          ],
          border: Border.all(
            color: isSelected ? ColorManager.primary : Colors.transparent,
            width: isSelected ? 2 : 0,
          ),
        ),
        padding: const EdgeInsets.all(6),
        child: Column(
          children: [
            Image.asset(
              imagePath,
              height: 150,
              fit: BoxFit.contain,
            ),
            const SizedBox(height: 10),
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: ColorManager.background,
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              SizedBox(height: Insets.large),
              Text(
                'Who are you?',
                style: StylesManager.headingStyle,
              ),
              SizedBox(height: Insets.large),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  _buildRoleCard(
                    role: 'Teacher',
                    imagePath: 'assets/images/teacher.png',
                  ),
                  const SizedBox(width: 20),
                  _buildRoleCard(
                    role: 'Student',
                    imagePath: 'assets/images/student.png',
                  ),
                ],
              ),
              SizedBox(height: Insets.large),
              Text(
                "To provide you with a personalized experience, please select your role.",
                style: StylesManager.bodyTextStyle,
                textAlign: TextAlign.center,
              ),
              SizedBox(height: Insets.large),
              SlideTransition(
                position: _offsetAnimation,
                child: GestureDetector(
                  onTap: () {
                    if (selectedRole != null) {
                      // Navigate based on selected role
                      Navigator.pushReplacement(
                        context,
                        MaterialPageRoute(
                          builder: (context) => selectedRole == 'Teacher'
                              ? const SignupPage() // Replace with actual teacher page
                              : const WelcomePage(), // Replace with actual student page
                        ),
                      );
                    } else {
                      // Show a message if no role is selected
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                            content: Text("Please select a role first")),
                      );
                    }
                  },
                  child: Container(
                    width: Sizes.buttonWidth,
                    height: Sizes.buttonHeight,
                    decoration: StylesManager.gradientDecoration,
                    alignment: Alignment.center,
                    child: Text(
                      'Proceed',
                      style: StylesManager.buttonTextStyle,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              GestureDetector(
                onTap: () {
                  // Navigate to Parent login page
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                        builder: (context) =>
                            const ParentRegisterPage()), // Replace with actual parent page
                  );
                },
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.person,
                      color: ColorManager.primary,
                      size: 18,
                    ),
                    SizedBox(width: 5),
                    Text(
                      "If you're a parent, click here",
                      style: TextStyle(
                        color: ColorManager.primary,
                        decoration: TextDecoration.underline,
                        fontSize: 16,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
