import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'storage_service.dart';

class ApiService {
  // --- Base URL Configuration ---
  static String get _host {
    if (kIsWeb) {
      return 'http://localhost:8000';
    } else {
      return 'http://10.0.2.2:8000';
    }
  }

  static String get baseUrl => '$_host/api/v1';
  static String get loginUrl => '$_host/api/login';

  // --- Helper for Authenticated Headers ---
  Future<Map<String, String>?> _getAuthHeaders({bool isPost = false}) async {
    final storage = StorageService();
    final token = await storage.readToken();
    if (token == null) {
      print('Authentication token not found.');
      return null;
    }

    final headers = {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    };
    if (isPost) {
      headers['Content-Type'] = 'application/json';
    }
    return headers;
  }

  // --- Login Method ---
  Future<String?> login(String email, String password) async {
    final url = Uri.parse(loginUrl);
    try {
      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({'email': email, 'password': password}),
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['token'];
      } else {
        print('Login failed: ${response.statusCode}');
        return null;
      }
    } catch (e) {
      print('An error occurred during login: $e');
      return null;
    }
  }

  // --- NEW: Get Current User ---
  Future<Map<String, dynamic>?> getCurrentUser() async {
    final headers = await _getAuthHeaders();
    if (headers == null) return null;

    final url = Uri.parse('$baseUrl/user'); // The endpoint for the current user
    try {
      final response = await http.get(url, headers: headers);
      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      } else {
        print('Failed to fetch user: ${response.statusCode}');
        return null;
      }
    } catch (e) {
      print('An error occurred while fetching user: $e');
      return null;
    }
  }

  // --- Get Projects Method ---
  Future<List<dynamic>> getProjects() async {
    final headers = await _getAuthHeaders();
    if (headers == null) return [];
    final url = Uri.parse('$baseUrl/projects');
    try {
      final response = await http.get(url, headers: headers);
      if (response.statusCode == 200) return jsonDecode(response.body)['data'];
      print('Failed to fetch projects: ${response.statusCode}');
      return [];
    } catch (e) {
      print('An error occurred while fetching projects: $e');
      return [];
    }
  }

  // --- Get Tasks Method ---
  Future<List<dynamic>> getTasks() async {
    final headers = await _getAuthHeaders();
    if (headers == null) return [];
    final url = Uri.parse('$baseUrl/tasks');
    try {
      final response = await http.get(url, headers: headers);
      if (response.statusCode == 200) return jsonDecode(response.body)['data'];
      print('Failed to fetch tasks: ${response.statusCode}');
      return [];
    } catch (e) {
      print('An error occurred while fetching tasks: $e');
      return [];
    }
  }

  // --- Get Simple Users List ---
  Future<List<dynamic>> getUsersList() async {
    final headers = await _getAuthHeaders();
    if (headers == null) return [];
    final url = Uri.parse('$baseUrl/users-list');
    try {
      final response = await http.get(url, headers: headers);
      if (response.statusCode == 200) return jsonDecode(response.body);
      print('Failed to fetch users list: ${response.statusCode}');
      return [];
    } catch (e) {
      print('An error occurred while fetching users list: $e');
      return [];
    }
  }

  // --- Create a Task ---
  Future<bool> createTask(Map<String, dynamic> taskData) async {
    final headers = await _getAuthHeaders(isPost: true);
    if (headers == null) return false;
    final url = Uri.parse('$baseUrl/tasks');
    try {
      final response = await http.post(
        url,
        headers: headers,
        body: jsonEncode(taskData),
      );
      if (response.statusCode == 201) {
        print('Task created successfully.');
        return true;
      } else {
        print('Failed to create task: ${response.statusCode}');
        print('Response body: ${response.body}');
        return false;
      }
    } catch (e) {
      print('An error occurred while creating task: $e');
      return false;
    }
  }
}
