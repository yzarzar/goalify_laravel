<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'priority' => $this->priority,
            'status' => $this->status,
            'progress_percentage' => $this->progress_percentage,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'milestone_count' => $this->whenLoaded('milestones', function() {
                return $this->milestones->count();
            }),
            // Task Statistics
            'task_stats' => [
                'total' => $this->total_tasks,
                'completed' => $this->completed_tasks,
                'in_progress' => $this->in_progress_tasks,
            ],
            // Milestone Statistics
            'milestone_stats' => [
                'total' => $this->total_milestones,
                'completed' => $this->completed_milestones,
                'in_progress' => $this->in_progress_milestones,
            ],
        ];
    }
}
