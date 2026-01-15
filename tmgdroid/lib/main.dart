import 'package:flutter/material.dart';
import 'screens/home_page.dart';
import 'screens/login_page.dart';
import 'services/storage_service.dart';
import 'utils/app_theme.dart'; // 1. IMPORT YOUR NEW THEME FILE

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'TMGDroid',
      debugShowCheckedModeBanner: false, // Hides the "debug" banner
      // 2. APPLY YOUR CUSTOM THEME
      theme: AppTheme.darkTheme,

      // Use a FutureBuilder to decide which page to show first
      home: FutureBuilder<String?>(
        // Call the readToken method from our StorageService
        future: StorageService().readToken(),
        builder: (context, snapshot) {
          // While we are waiting for the token, show a loading spinner
          if (snapshot.connectionState == ConnectionState.waiting) {
            // Make the loading spinner match the theme
            return Scaffold(
              body: Center(
                child: CircularProgressIndicator(color: AppTheme.primaryColor),
              ),
            );
          }

          // If the snapshot has data and the token is not null, go to HomePage
          if (snapshot.hasData && snapshot.data != null) {
            return const HomePage();
          }

          // Otherwise, go to the LoginPage
          return const LoginPage();
        },
      ),
    );
  }
}
