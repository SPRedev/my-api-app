class Task {
  final int id;
  final String name;
  final String? description;
  final String statusName;
  final String statusColor;
  final String priorityName;
  final String priorityColor;
  final List<String> assignedUserNames; // 1. ADDED: List of user names

  Task({
    required this.id,
    required this.name,
    this.description,
    required this.statusName,
    required this.statusColor,
    required this.priorityName,
    required this.priorityColor,
    required this.assignedUserNames, // 2. ADDED: To the constructor
  });

  static int colorFromHex(String hexColor) {
    hexColor = hexColor.toUpperCase().replaceAll("#", "");
    if (hexColor.length == 6) {
      hexColor = "FF" + hexColor;
    }
    return int.parse(hexColor, radix: 16);
  }

  factory Task.fromJson(Map<String, dynamic> json) {
    // 3. ADDED: Logic to parse the assigned_users array
    List<String> users = [];
    if (json['assigned_users'] != null && json['assigned_users'] is List) {
      // Use .map to transform the list of user objects into a list of user names
      users = (json['assigned_users'] as List)
          .map((user) => user['name'] as String)
          .toList();
    }

    return Task(
      id: json['id'],
      name: json['name'],
      description: json['description'],
      statusName: json['status'] != null ? json['status']['name'] : 'Unknown',
      statusColor: json['status'] != null ? json['status']['color'] : '#cccccc',
      priorityName: json['priority'] != null
          ? json['priority']['name']
          : 'Unknown',
      priorityColor: json['priority'] != null
          ? json['priority']['color']
          : '#cccccc',
      assignedUserNames: users, // 4. ADDED: Pass the list to the constructor
    );
  }
}
