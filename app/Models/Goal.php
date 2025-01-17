<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'progress_percentage',
        'priority'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress_percentage' => 'float'
    ];

    /**
     * Get the user that owns the goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the milestones for the goal.
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    /**
     * Calculate total tasks across all milestones
     */
    protected function getTotalTasksCount(): int
    {
        return $this->milestones()
            ->withCount('tasks')
            ->get()
            ->sum('tasks_count');
    }

    /**
     * Calculate completed tasks across all milestones
     */
    protected function getCompletedTasksCount(): int
    {
        $completedTasks = 0;
        $milestones = $this->milestones()->with('tasks')->get();
        
        foreach ($milestones as $milestone) {
            $completedTasks += $milestone->tasks()
                ->where('status', 'completed')
                ->count();
        }
        
        return $completedTasks;
    }

    /**
     * Update progress percentage based on task completion.
     * Returns true if the progress was updated, false otherwise.
     */
    public function updateProgressFromMilestones(): bool
    {
        $totalTasks = $this->getTotalTasksCount();

        // If there are no tasks, progress is 0%
        if ($totalTasks === 0) {
            return $this->updateProgress(0.0);
        }

        // Calculate progress based on completed tasks
        $completedTasks = $this->getCompletedTasksCount();
        $progressPercentage = ($completedTasks / $totalTasks) * 100;

        return $this->updateProgress($progressPercentage);
    }

    /**
     * Update the progress percentage if it has changed.
     * Returns true if the progress was updated, false otherwise.
     */
    protected function updateProgress(float $newProgress): bool
    {
        if (abs($this->progress_percentage - $newProgress) > 0.001) {
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
            $this->progress_percentage < 0.1 => 'pending',
            $this->progress_percentage >= 99.9 => 'completed',
            default => 'in_progress',
        };
    }
}
