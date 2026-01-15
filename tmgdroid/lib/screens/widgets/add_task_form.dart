import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../utils/app_theme.dart';

class AddTaskForm extends StatefulWidget {
  final int projectId;
  final VoidCallback onTaskAdded; // A function to call when a task is successfully added

  const AddTaskForm({super.key, required this.projectId, required this.onTaskAdded});

  @override
  State<AddTaskForm> createState() => _AddTaskFormState();
}

class _AddTaskFormState extends State<AddTaskForm> {
  final _formKey = GlobalKey<FormState>();
  final _apiService = ApiService();

  // Form field controllers
  final _nameController = TextEditingController();
  final _descriptionController = TextEditingController();

  // State for dropdowns and loading indicators
  List<dynamic> _users = [];
  List<int> _selectedUserIds = [];
  bool _isLoading = true;
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    setState(() => _isLoading = true);
    try {
      // Fetch the list of users for the dropdown
      final users = await _apiService.getUsersList();
      setState(() {
        _users = users;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      // Handle error (e.g., show a snackbar)
      print("Failed to load users: $e");
    }
  }

  Future<void> _submitForm() async {
    // First, validate the form
    if (_formKey.currentState!.validate()) {
      setState(() => _isSubmitting = true);

      // Build the data map to send to the API
      final taskData = {
        'name': _nameController.text,
        'description': _descriptionController.text,
        'project_id': widget.projectId,
        'status_id': 1, // Default to 'New'
        'priority_id': 2, // Default to 'Medium'
        'assigned_to': _selectedUserIds,
      };

      final success = await _apiService.createTask(taskData);

      setState(() => _isSubmitting = false);

      if (success && mounted) {
        widget.onTaskAdded(); // Call the callback to refresh the task list
        Navigator.of(context).pop(); // Close the form
      } else {
        // Show an error message
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Failed to create task. Please try again.')),
        );
      }
    }
  }

  @override
  void dispose() {
    // Clean up the controllers when the widget is removed
    _nameController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom,
        left: 16,
        right: 16,
        top: 16,
      ),
      child: _isLoading
          ? Center(child: CircularProgressIndicator(color: AppTheme.primaryColor))
          : Form(
              key: _formKey,
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Add New Task', style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 20),
                    
                    // Task Name Field
                    TextFormField(
                      controller: _nameController,
                      decoration: const InputDecoration(labelText: 'Task Name'),
                      validator: (value) => value == null || value.isEmpty ? 'Please enter a task name' : null,
                    ),
                    const SizedBox(height: 16),

                    // Description Field
                    TextFormField(
                      controller: _descriptionController,
                      decoration: const InputDecoration(labelText: 'Description (Optional)'),
                      maxLines: 3,
                    ),
                    const SizedBox(height: 16),

                    // Assigned To Dropdown
                    DropdownButtonFormField<int>(
                      isExpanded: true,
                      decoration: const InputDecoration(labelText: 'Assign To'),
                      items: _users.map<DropdownMenuItem<int>>((user) {
                        return DropdownMenuItem<int>(
                          value: user['id'],
                          child: Text(user['name']),
                        );
                      }).toList(),
                      onChanged: (value) {
                        if (value != null && !_selectedUserIds.contains(value)) {
                          setState(() {
                            _selectedUserIds.add(value);
                          });
                        }
                      },
                    ),
                    const SizedBox(height: 8),
                    // Display selected users as chips
                    Wrap(
                      spacing: 8.0,
                      children: _selectedUserIds.map((userId) {
                        final user = _users.firstWhere((u) => u['id'] == userId);
                        return Chip(
                          label: Text(user['name']),
                          onDeleted: () {
                            setState(() {
                              _selectedUserIds.remove(userId);
                            });
                          },
                        );
                      }).toList(),
                    ),
                    const SizedBox(height: 24),

                    // Submit Button
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _isSubmitting ? null : _submitForm,
                        child: _isSubmitting
                            ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                            : const Text('Create Task'),
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],
                ),
              ),
            ),
    );
  }
}
