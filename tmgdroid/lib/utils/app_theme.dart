import 'package:flutter/material.dart';

class AppTheme {
  // --- Define the Core Colors from Your Logo ---

  // A dark, desaturated blue for the main background
  static const Color primaryBackground = Color(0xFF1A222D); 
  
  // The vibrant teal from the center of your logo for primary actions
  static const Color primaryColor = Color(0xFF25A5A5); 
  
  // A slightly darker teal for accents and surfaces
  static const Color accentColor = Color(0xFF1B7A8A); 
  
  // A light teal for text and icons to ensure good contrast
  static const Color primaryText = Color(0xFFB0D4D4); 

  // --- Create the ThemeData Object ---

  static ThemeData get darkTheme {
    return ThemeData(
      // Use Material 3 design
      useMaterial3: true,

      // Set the overall brightness to dark
      brightness: Brightness.dark,

      // --- Color Scheme ---
      colorScheme: const ColorScheme(
        brightness: Brightness.dark,
        primary: primaryColor,
        onPrimary: Colors.white, // Text on top of the primary color
        secondary: accentColor,
        onSecondary: Colors.white, // Text on top of the secondary color
        error: Colors.redAccent,
        onError: Colors.white,
        surface: primaryBackground, // Main background color
        onSurface: primaryText, // Default text color
        surfaceTint: Colors.transparent, // No tint on surfaces
      ),

      // --- Scaffold Background Color ---
      // This sets the default background for all pages (Scaffolds)
      scaffoldBackgroundColor: primaryBackground,

      // --- AppBar Theme ---
      appBarTheme: const AppBarTheme(
        backgroundColor: primaryBackground, // Make AppBar match the background
        elevation: 0, // No shadow for a flatter look
        foregroundColor: primaryText, // Color for title text and icons
      ),

      // --- ElevatedButton Theme ---
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primaryColor, // Button background color
          foregroundColor: Colors.white, // Button text color
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8.0), // Slightly rounded corners
          ),
        ),
      ),

      // --- TextField (Input) Theme ---
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: const Color(0xFF2C3A4A), // A slightly lighter background for inputs
        labelStyle: const TextStyle(color: primaryText),
        hintStyle: TextStyle(color: primaryText.withOpacity(0.5)),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8.0),
          borderSide: BorderSide.none, // No border for a cleaner look
        ),
      ),
    );
  }
}
