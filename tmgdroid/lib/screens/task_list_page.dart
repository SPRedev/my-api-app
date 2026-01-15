import 'package:flutter/material.dart';
import '../models/project.dart';
import '../models/task.dart';
import '../services/api_service.dart';
import '../utils/app_theme.dart';
import 'widgets/add_task_form.dart'; // 1. IMPORT THE NEW FORM WIDGET

class TaskListPage extends StatefulWidget {
  final Project project;
  const TaskListPage({super.key, required this.project});

  @override
  State<TaskListPage> createState() => _TaskListPageState();
}

class _TaskListPageState extends State<TaskListPage> {
  final ApiService _apiService = ApiService();
  late Future<List<Task>> _tasksFuture;

  @override
  void initState() {
    super.initState();
    _tasksFuture = _loadTasks();
  }

  Future<List<Task>> _loadTasks() async {
    final allTasksData = await _apiService.getTasks();
    final filteredTasks = allTasksData
        .where((taskJson) => taskJson['project']['id'] == widget.project.id)
        .map((taskJson) => Task.fromJson(taskJson))
        .toList();
    return filteredTasks;
  }

  // 2. ADD METHOD TO REFRESH THE TASK LIST
  void _refreshTasks() {
    setState(() {
      _tasksFuture = _loadTasks();
    });
  }

  // 3. ADD METHOD TO SHOW THE FORM
  void _showAddTaskForm() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      // CORRECTED LINE:
      backgroundColor: AppTheme.primaryBackground, // Use the correct color name
      builder: (context) {
        return AddTaskForm(
          projectId: widget.project.id,
          onTaskAdded: _refreshTasks,
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.project.name)),
      // 4. ADD THE FLOATING ACTION BUTTON
      floatingActionButton: FloatingActionButton(
        onPressed: _showAddTaskForm,
        backgroundColor: AppTheme.primaryColor,
        child: const Icon(Icons.add, color: Colors.white),
      ),
      body: FutureBuilder<List<Task>>(
        future: _tasksFuture,
        builder: (context, snapshot) {
          // ... (The FutureBuilder and ListView code remains exactly the same)
          if (snapshot.connectionState == ConnectionState.waiting) {
            return Center(
              child: CircularProgressIndicator(color: AppTheme.primaryColor),
            );
          }
          if (snapshot.hasError) {
            return Center(child: Text('An error occurred: ${snapshot.error}'));
          }
          if (!snapshot.hasData || snapshot.data!.isEmpty) {
            return const Center(
              child: Text('No tasks found for this project.'),
            );
          }
          final tasks = snapshot.data!;
          return ListView.builder(
            itemCount: tasks.length,
            itemBuilder: (context, index) {
              final task = tasks[index];
              return Card(
                margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                color: const Color(0xFF2C3A4A),
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              task.name,
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                              ),
                            ),
                          ),
                          if (task.assignedUserNames.isNotEmpty)
                            _buildUserAvatars(task.assignedUserNames),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Container(
                            width: 12,
                            height: 12,
                            decoration: BoxDecoration(
                              color: Color(
                                Task.colorFromHex(task.priorityColor),
                              ),
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            task.priorityName,
                            style: TextStyle(
                              color: AppTheme.primaryText.withOpacity(0.7),
                            ),
                          ),
                          const SizedBox(width: 16),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: Color(
                                Task.colorFromHex(task.statusColor),
                              ).withOpacity(0.2),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              task.statusName,
                              style: TextStyle(
                                color: Color(
                                  Task.colorFromHex(task.statusColor),
                                ),
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }

  Widget _buildUserAvatars(List<String> userNames) {
    final displayUsers = userNames.length > 3
        ? userNames.sublist(0, 3)
        : userNames;
    return SizedBox(
      width: (displayUsers.length * 24.0) + 10,
      height: 34,
      child: Stack(
        children: List.generate(displayUsers.length, (index) {
          final userName = displayUsers[index];
          return Positioned(
            left: index * 24.0,
            child: CircleAvatar(
              radius: 17,
              backgroundColor: AppTheme.primaryBackground,
              child: CircleAvatar(
                radius: 15,
                backgroundColor: AppTheme.primaryColor,
                child: Text(
                  userName.isNotEmpty ? userName[0].toUpperCase() : '?',
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
          );
        }),
      ),
    );
  }
}
