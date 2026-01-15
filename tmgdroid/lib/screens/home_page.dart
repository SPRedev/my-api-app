import 'package:flutter/material.dart';
import '../models/project.dart';
import '../services/api_service.dart';
import '../services/storage_service.dart';
import 'login_page.dart';
import 'task_list_page.dart';
import 'admin/admin_dashboard_page.dart'; // 1. IMPORT THE NEW DASHBOARD PAGE
import '../utils/app_theme.dart';

class HomePageData {
  final Map<String, dynamic> user;
  final List<Project> projects;
  HomePageData({required this.user, required this.projects});
}

class HomePage extends StatefulWidget {
  const HomePage({super.key});
  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  final ApiService _apiService = ApiService();
  late Future<HomePageData> _dataFuture;

  @override
  void initState() {
    super.initState();
    _dataFuture = _loadData();
  }

  Future<HomePageData> _loadData() async {
    try {
      final results = await Future.wait([
        _apiService.getCurrentUser(),
        _apiService.getProjects(),
      ]);
      final user = results[0] as Map<String, dynamic>? ?? {};
      final projectData = results[1] as List<dynamic>? ?? [];
      final projects = projectData
          .map((json) => Project.fromJson(json))
          .toList();
      return HomePageData(user: user, projects: projects);
    } catch (e) {
      print("Error loading home page data: $e");
      throw Exception('Failed to load data');
    }
  }

  void _handleLogout(BuildContext context) async {
    final storageService = StorageService();
    await storageService.deleteToken();
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (context) => const LoginPage()),
      (Route<dynamic> route) => false,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: FutureBuilder<HomePageData>(
        future: _dataFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return Center(
              child: CircularProgressIndicator(color: AppTheme.primaryColor),
            );
          }
          if (snapshot.hasError || !snapshot.hasData) {
            return Center(child: Text('An error occurred: ${snapshot.error}'));
          }

          final homeData = snapshot.data!;
          final user = homeData.user;
          final projects = homeData.projects;
          final bool isAdmin = user['is_admin'] ?? false;

          return Scaffold(
            appBar: AppBar(
              title: const Text('Projects'),
              actions: [
                if (isAdmin)
                  IconButton(
                    icon: const Icon(Icons.dashboard_customize),
                    tooltip: 'Admin Dashboard',
                    onPressed: () {
                      // 2. ADD THE NAVIGATION LOGIC
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (context) => const AdminDashboardPage(),
                        ),
                      );
                    },
                  ),
                IconButton(
                  icon: const Icon(Icons.logout),
                  tooltip: 'Logout',
                  onPressed: () => _handleLogout(context),
                ),
              ],
            ),
            body: projects.isEmpty
                ? const Center(child: Text('No projects found.'))
                : ListView.builder(
                    itemCount: projects.length,
                    itemBuilder: (context, index) {
                      final project = projects[index];
                      return Card(
                        margin: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 8,
                        ),
                        color: const Color(0xFF2C3A4A),
                        child: ListTile(
                          contentPadding: const EdgeInsets.all(16),
                          title: Text(
                            project.name,
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          subtitle: Text(
                            'Created by: ${project.creatorName}',
                            style: TextStyle(
                              color: AppTheme.primaryText.withOpacity(0.7),
                            ),
                          ),
                          trailing: const Icon(
                            Icons.chevron_right,
                            color: AppTheme.primaryText,
                          ),
                          onTap: () {
                            Navigator.of(context).push(
                              MaterialPageRoute(
                                builder: (context) =>
                                    TaskListPage(project: project),
                              ),
                            );
                          },
                        ),
                      );
                    },
                  ),
          );
        },
      ),
    );
  }
}
