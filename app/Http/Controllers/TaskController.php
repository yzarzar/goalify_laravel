<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Milestone;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends BaseController
{
    /**
     * Display a listing of tasks for a milestone.
     */
    public function index(int $milestone_id): JsonResponse
    {
        $milestone = Milestone::find($milestone_id);

        if (!$milestone) {
            return $this->sendNotFound('Milestone not found');
        }

        try {
            $tasks = $milestone->tasks()
                ->orderBy('due_date')
                ->get();
            return $this->sendCollection($tasks);
        } catch (\Throwable $th) {
            return $this->sendServerError('Failed to retrieve tasks', [], [], $th);
        }
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(StoreTaskRequest $request, int $milestone_id): JsonResponse
    {
        $milestone = Milestone::find($milestone_id);

        if (!$milestone) {
            return $this->sendNotFound('Milestone not found');
        }

        try {
            $validated = $request->validated();
            $task = $milestone->tasks()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'priority' => $validated['priority'],
                'due_date' => $validated['due_date'] ?? null,
            ]);

            return $this->sendCreated(new TaskResource($task->load('milestone')));
        } catch (\Throwable $th) {
            return $this->sendServerError('Failed to create task', [], [], $th);
        }
    }

    /**
     * Display the specified task.
     */
    public function show(int $milestone_id, int $task_id): JsonResponse
    {
        $task = Task::where('milestone_id', $milestone_id)
            ->where('id', $task_id)
            ->first();

        if (!$task) {
            return $this->sendNotFound('Task not found in this milestone');
        }

        return $this->sendSuccess(new TaskResource($task->load('milestone')));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(UpdateTaskRequest $request, int $milestone_id, int $task_id): JsonResponse
    {
        $task = Task::where('milestone_id', $milestone_id)
            ->where('id', $task_id)
            ->first();

        if (!Milestone::find($milestone_id)) {
            return $this->sendNotFound('Milestone not found');
        }

        if (!$task) {
            return $this->sendNotFound('Task not found in this milestone');
        }

        try {
            $task->update($request->validated());
            return $this->sendSuccess(new TaskResource($task->load('milestone')));
        } catch (\Throwable $th) {
            return $this->sendServerError('Failed to update task', [], [], $th);
        }
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(int $milestone_id, int $task_id): JsonResponse
    {
        $task = Task::where('milestone_id', $milestone_id)
            ->where('id', $task_id)
            ->first();

        if (!Milestone::find($milestone_id)) {
            return $this->sendNotFound('Milestone not found');
        }

        if (!$task) {
            return $this->sendNotFound('Task not found in this milestone');
        }

        try {
            $task->delete();
            return $this->sendSuccess(null, self::DEFAULT_DELETED_MESSAGE);
        } catch (\Throwable $th) {
            return $this->sendServerError('Failed to delete task', [], [], $th);
        }
    }
}
