<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMilestoneRequest;
use App\Http\Requests\UpdateMilestoneRequest;
use App\Models\Goal;
use App\Models\Milestone;
use Illuminate\Http\JsonResponse;
use Throwable;

class MilestoneController extends BaseController
{
    /**
     * Display a listing of the milestones for a specific goal.
     */
    public function index(int $goal_id): JsonResponse
    {
        $goal = Goal::find($goal_id);

        if (!$goal) {
            return $this->sendNotFound('Goal not found');
        }

        try {
            $milestones = $goal->milestones()
                ->orderBy('due_date')
                ->get();

            return $this->sendCollection($milestones);
        } catch (Throwable $e) {
            return $this->sendServerError('Failed to retrieve milestones', [], [], $e);
        }
    }

    /**
     * Store a newly created milestone in storage.
     */
    public function store(StoreMilestoneRequest $request, int $goal_id): JsonResponse
    {
        try {
            // First, check if the goal exists
            $goal = Goal::find($goal_id);

            if (!$goal) {
                return $this->sendNotFound('Goal not found');
            }

            // Merge the goal_id with the validated data
            $data = array_merge($request->validated(), ['goal_id' => $goal_id]);

            $milestone = Milestone::create($data);
            return $this->sendCreated($milestone, 'Milestone created successfully');
        } catch (Throwable $e) {
            return $this->sendServerError('Failed to create milestone', [], [], $e);
        }
    }

    /**
     * Display the specified milestone.
     */
    public function show(int $goal_id, int $milestone_id): JsonResponse
    {
        try {
            $goal = Goal::find($goal_id);
            $milestone = Milestone::find($milestone_id);

            if (!$goal) {
                return $this->sendNotFound('Goal not found');
            }

            if (!$milestone) {
                return $this->sendNotFound('Milestone not found for this goal');
            }

            if ($milestone->goal_id !== $goal->id) {
                return $this->sendNotFound('Milestone not found for this goal');
            }

            return $this->sendSuccess($milestone);
        } catch (Throwable $e) {
            return $this->sendServerError('Failed to retrieve milestone', [], [], $e);
        }
    }

    /**
     * Update the specified milestone in storage.
     */
    public function update(UpdateMilestoneRequest $request, int $goal_id, int $milestone_id): JsonResponse
    {
        try {
            $milestone = Milestone::find($milestone_id);
            $goal = Goal::find($goal_id);

            if (!$goal) {
                return $this->sendNotFound('Goal not found');
            }

            if (!$milestone) {
                return $this->sendNotFound('Milestone not found for this goal');
            }

            if ($milestone->goal_id !== $goal->id) {
                return $this->sendNotFound('Milestone not found for this goal');
            }

            // Get the old status before update
            $oldStatus = $milestone->status;

            // Update the milestone
            $milestone->update($request->validated());

            return $this->sendSuccess($milestone, 'Milestone updated successfully');
        } catch (Throwable $e) {
            return $this->sendServerError('Failed to update milestone', [], [], $e);
        }
    }

    /**
     * Remove the specified milestone from storage.
     */
    public function destroy(int $goal_id, int $milestone_id): JsonResponse
    {
        try {
            $milestone = Milestone::find($milestone_id);
            $goal = Goal::find($goal_id);

            if (!$goal) {
                return $this->sendNotFound('Goal not found');
            }

            if (!$milestone) {
                return $this->sendNotFound('Milestone not found for this goal');
            }

            $milestone->delete();
            return $this->sendSuccess(null, 'Milestone deleted successfully');
        } catch (Throwable $e) {
            return $this->sendServerError('Failed to delete milestone', [], [], $e);
        }
    }
}
