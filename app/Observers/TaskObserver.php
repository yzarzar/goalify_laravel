<?php

namespace App\Observers;

use App\Models\Task;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        if ($task->milestone) {
            $task->milestone->updateProgressFromTasks();
            
            // Update parent goal's progress
            if ($task->milestone->goal) {
                $task->milestone->goal->updateProgressFromMilestones();
            }
        }
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        // Only update progress if status has changed
        if ($task->isDirty('status') && $task->milestone) {
            $task->milestone->updateProgressFromTasks();
            
            // Update parent goal's progress
            if ($task->milestone->goal) {
                $task->milestone->goal->updateProgressFromMilestones();
            }
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        if ($task->milestone) {
            $task->milestone->updateProgressFromTasks();
            
            // Update parent goal's progress
            if ($task->milestone->goal) {
                $task->milestone->goal->updateProgressFromMilestones();
            }
        }
    }
}
