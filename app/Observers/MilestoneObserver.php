<?php

namespace App\Observers;

use App\Models\Milestone;

class MilestoneObserver
{
    /**
     * Handle the Milestone "created" event.
     */
    public function created(Milestone $milestone): void
    {
        $milestone->goal->updateProgressFromMilestones();
    }

    /**
     * Handle the Milestone "updated" event.
     */
    public function updated(Milestone $milestone): void
    {
        // Only update progress if the status has changed
        if ($milestone->isDirty('status')) {
            $milestone->goal->updateProgressFromMilestones();
        }
    }

    /**
     * Handle the Milestone "deleted" event.
     */
    public function deleted(Milestone $milestone): void
    {
        $milestone->goal->updateProgressFromMilestones();
    }
}
