<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Milestone;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'milestone_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date'
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saved(function ($task) {
            // Update milestone progress when task is saved
            if ($task->milestone) {
                $task->milestone->updateProgressFromTasks();
            }
        });

        static::deleted(function ($task) {
            // Update milestone progress when task is deleted
            if ($task->milestone) {
                $task->milestone->updateProgressFromTasks();
            }
        });
    }

    /**
     * Get the milestone that owns the task.
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }
}
