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
        'progress_percentage' => 'integer'
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
     * Update progress percentage based on completed milestones.
     * Returns true if the progress was updated, false otherwise.
     */
    public function updateProgressFromMilestones(): bool
    {
        $totalMilestones = $this->milestones()->count();

        // If there are no milestones, progress is 0%
        if ($totalMilestones === 0) {
            return $this->updateProgress(0);
        }

        // Count completed milestones
        $completedMilestones = $this->milestones()
            ->where('status', 'completed')
            ->count();

        // Calculate progress percentage
        $progressPercentage = ($completedMilestones / $totalMilestones) * 100;

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
            default => 'in progress',
        };
    }
}
