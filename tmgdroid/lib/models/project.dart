class Project {
  final int id;
  final String name;
  final String? description; // Description can be null
  final String creatorName;
  final String createdAt;

  // Constructor for the Project class
  Project({
    required this.id,
    required this.name,
    this.description,
    required this.creatorName,
    required this.createdAt,
  });

  /// A "factory" constructor that creates a Project instance from a JSON map.
  /// This is how we will convert the API response into a Dart object.
  factory Project.fromJson(Map<String, dynamic> json) {
    return Project(
      id: json['id'],
      name: json['name'],
      description: json['description'],
      // The creator's name is nested inside the 'creator' object in the JSON
      creatorName: json['creator'] != null ? json['creator']['name'] : 'Unknown',
      createdAt: json['created_at'],
    );
  }
}
