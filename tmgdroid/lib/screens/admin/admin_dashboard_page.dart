import 'package:flutter/material.dart';

class AdminDashboardPage extends StatelessWidget {
  const AdminDashboardPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Admin Dashboard'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16.0),
        children: [
          // --- User Management Card ---
          Card(
            elevation: 2,
            child: ListTile(
              leading: const Icon(Icons.people, size: 40),
              title: const Text('Manage Users', style: TextStyle(fontWeight: FontWeight.bold)),
              subtitle: const Text('Create, edit, and delete users and their roles.'),
              trailing: const Icon(Icons.chevron_right),
              onTap: () {
                // TODO: Navigate to UserListPage
                print('Tapped on Manage Users');
              },
            ),
          ),
          const SizedBox(height: 16),

          // --- Role & Permission Management Card ---
          Card(
            elevation: 2,
            child: ListTile(
              leading: const Icon(Icons.policy, size: 40),
              title: const Text('Manage Roles & Permissions', style: TextStyle(fontWeight: FontWeight.bold)),
              subtitle: const Text('Define roles and assign permissions.'),
              trailing: const Icon(Icons.chevron_right),
              onTap: () {
                // TODO: Navigate to RoleListPage
                print('Tapped on Manage Roles & Permissions');
              },
            ),
          ),
        ],
      ),
    );
  }
}
