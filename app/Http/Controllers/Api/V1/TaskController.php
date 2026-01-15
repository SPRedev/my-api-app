<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Status;
use App\Models\Priority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    private $relations = [
        'project:id,name',
        'status:id,name,color',
        'priority:id,name,color',
        'creator:id,name',
        'assignedUsers:id,name'
    ];

    public function index()
    {
        return Task::with($this->relations)->latest()->paginate();
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

        // 1. MERGE the authenticated user's ID into the validated data
        $dataToCreate = array_merge($validated, ['created_by' => Auth::id()]);

        // 2. CREATE the task using the merged data
        $task = Task::create($dataToCreate);

        $task->assignedUsers()->sync($validated['assigned_to'] ?? []);

        return response()->json($task->load($this->relations), 201);
    }

    public function show(Task $task)
    {
        return $task->load($this->relations);
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

        // 3. UPDATE the task using the validated data (no created_by needed here)
        $task->update($validated);

        if ($request->has('assigned_to')) {
            $task->assignedUsers()->sync($validated['assigned_to'] ?? []);
        }

        return response()->json($task->load($this->relations));
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(null, 204);
    }

    public function getStatuses()
    {
        return Status::all();
    }

    public function getPriorities()
    {
        return Priority::all();
    }
}
