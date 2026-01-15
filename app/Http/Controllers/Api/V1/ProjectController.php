<?php

// In app/Http/Controllers/Api/V1/ProjectController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class ProjectController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the projects.
     */
    public function index()
    {
        // Eager load the creator's name
        return Project::with('creator:id,name')->latest()->paginate();
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:projects'],
            'description' => ['nullable', 'string'],
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'created_by' => Auth::id(), // Automatically set the creator
        ]);

        return response()->json($project->load('creator:id,name'), 201);
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        return $project->load('creator:id,name');
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', 'unique:projects,name,' . $project->id],
            'description' => ['nullable', 'string'],
        ]);

        $project->update($validated);

        return response()->json($project->load('creator:id,name'));
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
         $this->authorize('delete', $project);
        $project->delete();

        return response()->json(null, 204);
    }
}
