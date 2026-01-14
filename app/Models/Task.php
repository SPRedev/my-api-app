<?php
// In app/Models/Task.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'project_id',
        'status_id',
        'priority_id',
        'created_by',
    ];

    // A task belongs to one project
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // A task has one status
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    // A task has one priority
    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    // A task is created by one user
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // A task can be assigned to many users
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user');
    }
}
