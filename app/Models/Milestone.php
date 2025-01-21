<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id',
        'title',
        'description',
        'due_date',
        'status',
        'priority',
        'progress_percentage'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'progress_percentage' => 'integer',
    ];

    /**
     * Get the goal that owns the milestone.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get the tasks for the milestone.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Check if the milestone has any associated tasks
     */
    public function hasAssociatedTasks(): bool
    {
        return $this->tasks()->exists();
    }

    /**
     * Update progress percentage based on completed milestones.
     * Returns true if the progress was updated, false otherwise.
     */
    public function updateProgressFromTasks(): bool
    {
        $totalTasks = $this->tasks()->count();

        // If there are no tasks, progress is 0%
        if ($totalTasks === 0) {
            return $this->updateProgress(0);
        }

        // Count completed tasks
        $completedTasks = $this->tasks()
            ->where('status', 'completed')
            ->count();

        // Calculate progress percentage
        $progressPercentage = ($completedTasks / $totalTasks) * 100;

        // Round to nearest integer
        $progressPercentage = (int) round($progressPercentage);

        return $this->updateProgress($progressPercentage);
    }

    /**
     * Update the progress percentage if it has changed.
     * Returns true if the progress was updated, false otherwise.
     */
    protected function updateProgress(int $newProgress): bool
    {
        if ($this->progress_percentage !== $newProgress) {
            $this->progress_percentage = $newProgress;
            $this->updateStatusFromProgress();
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Update the status based on the current progress percentage
     */
    protected function updateStatusFromProgress(): void
    {
        $this->status = match(true) {
            $this->progress_percentage === 0 => 'pending',
            $this->progress_percentage === 100 => 'completed',
            default => 'in_progress',
        };
    }

    /**
     * Set the status attribute and update progress_percentage based on status
     */
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value;
        if ($value === 'completed') {
            $this->attributes['progress_percentage'] = 100;
        } elseif ($value === 'pending') {
            $this->attributes['progress_percentage'] = 0;
        }
    }
}
