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
     * Get milestones without tasks
     */
    protected function getStandaloneMilestones()
    {
        return $this->milestones()
            ->whereDoesntHave('tasks')
            ->get();
    }

    /**
     * Get milestones with tasks
     */
    protected function getTaskBasedMilestones()
    {
        return $this->milestones()
            ->has('tasks')
            ->withCount('tasks')
            ->get();
    }

    /**
     * Update progress percentage based on both task completion and standalone milestones.
     * Returns true if the progress was updated, false otherwise.
     */
    public function updateProgressFromMilestones(): bool
    {
        $taskBasedMilestones = $this->getTaskBasedMilestones();
        $standaloneMilestones = $this->getStandaloneMilestones();
        
        $totalWeight = 0;
        $completedWeight = 0;
        
        // Calculate progress from task-based milestones
        foreach ($taskBasedMilestones as $milestone) {
            $totalTasks = $milestone->tasks_count;
            $completedTasks = $milestone->tasks()
                ->where('status', 'completed')
                ->count();
                
            $totalWeight += $totalTasks;
            $completedWeight += $completedTasks;
        }
        
        // Add standalone milestones to the calculation
        $totalStandalone = $standaloneMilestones->count();
        if ($totalStandalone > 0) {
            $completedStandalone = $standaloneMilestones
                ->where('status', 'completed')
                ->count();
                
            // Each standalone milestone has a weight of 1
            $totalWeight += $totalStandalone;
            $completedWeight += $completedStandalone;
        }
        
        // If there are no items to track progress
        if ($totalWeight === 0) {
            return $this->updateProgress(0.0);
        }
        
        // Calculate overall progress percentage
        $progressPercentage = ($completedWeight / $totalWeight) * 100;
        
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

    /**
     * Get total tasks count
     */
    public function getTotalTasksAttribute(): int
    {
        return $this->milestones()
            ->withCount('tasks')
            ->get()
            ->sum('tasks_count');
    }

    /**
     * Get completed tasks count
     */
    public function getCompletedTasksAttribute(): int
    {
        return $this->milestones()
            ->withCount(['tasks' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->get()
            ->sum('tasks_count');
    }

    /**
     * Get in-progress tasks count
     */
    public function getInProgressTasksAttribute(): int
    {
        return $this->total_tasks - $this->completed_tasks;
    }

    /**
     * Get total milestones count
     */
    public function getTotalMilestonesAttribute(): int
    {
        return $this->milestones()->count();
    }

    /**
     * Get completed milestones count
     */
    public function getCompletedMilestonesAttribute(): int
    {
        return $this->milestones()
            ->where('status', 'completed')
            ->count();
    }

    /**
     * Get in-progress milestones count
     */
    public function getInProgressMilestonesAttribute(): int
    {
        return $this->total_milestones - $this->completed_milestones;
    }
}
