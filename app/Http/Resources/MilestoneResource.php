<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MilestoneResource extends JsonResource
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
            'goal_id' => $this->goal_id,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'priority' => $this->priority,
            'progress_percentage' => $this->progress_percentage,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'task_count' => $this->whenLoaded('tasks', function() {
                return $this->tasks->count();
            }),
        ];
    }
}
