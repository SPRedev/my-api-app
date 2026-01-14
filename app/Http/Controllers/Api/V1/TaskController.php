<?php
// In app/Http/Controllers/Api/V1/TaskController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Status;
use App\Models\Priority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        // Eager load all relationships for maximum efficiency
        return Task::with(['project:id,name', 'status:id,name,color', 'priority:id,name,color', 'creator:id,name', 'assignedUsers:id,name'])
            ->latest()
            ->paginate();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'project_id' => ['required', 'exists:projects,id'],
            'status_id' => ['required', 'exists:statuses,id'],
            'priority_id' => ['required', 'exists:priorities,id'],
            'assigned_to' => ['nullable', 'array'],
            'assigned_to.*' => ['exists:users,id'],
        ]);

        $task = Task::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'project_id' => $validated['project_id'],
            'status_id' => $validated['status_id'],
            'priority_id' => $validated['priority_id'],
            'created_by' => Auth::id(),
        ]);

        if (!empty($validated['assigned_to'])) {
            $task->assignedUsers()->sync($validated['assigned_to']);
        }

        return response()->json($task->load(['project:id,name', 'status:id,name,color', 'priority:id,name,color', 'creator:id,name', 'assignedUsers:id,name']), 201);
    }

    public function show(Task $task)
    {
        return $task->load(['project:id,name', 'status:id,name,color', 'priority:id,name,color', 'creator:id,name', 'assignedUsers:id,name']);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'project_id' => ['sometimes', 'required', 'exists:projects,id'],
            'status_id' => ['sometimes', 'required', 'exists:statuses,id'],
            'priority_id' => ['sometimes', 'required', 'exists:priorities,id'],
            'assigned_to' => ['nullable', 'array'],
            'assigned_to.*' => ['exists:users,id'],
        ]);

        $task->update($request->except('assigned_to'));

        if ($request->has('assigned_to')) {
            $task->assignedUsers()->sync($validated['assigned_to']);
        }

        return response()->json($task->load(['project:id,name', 'status:id,name,color', 'priority:id,name,color', 'creator:id,name', 'assignedUsers:id,name']));
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(null, 204);
    }

    /**
     * Get all available statuses.
     */
    public function getStatuses()
    {
        return Status::all();
    }

    /**
     * Get all available priorities.
     */
    public function getPriorities()
    {
        return Priority::all();
    }
}
