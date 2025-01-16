<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGoalRequest;
use App\Http\Requests\UpdateGoalRequest;
use App\Http\Resources\GoalResource;
use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Facades\JWTAuth;

class GoalController extends BaseController
{

    /**
     * Display a paginated listing of the user's goals with optional filtering.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = JWTAuth::user()->goals();

            // Apply filters if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }
            if ($request->has('start_date')) {
                $query->where('start_date', '>=', $request->start_date);
            }
            if ($request->has('end_date')) {
                $query->where('end_date', '<=', $request->end_date);
            }

            // Apply search if provided
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->get('per_page', 10);
            $goals = $query->paginate($perPage);

            // Prepare pagination metadata
            $meta = [
                'current_page' => $goals->currentPage(),
                'from' => $goals->firstItem(),
                'to' => $goals->lastItem(),
                'last_page' => $goals->lastPage(),
                'per_page' => $goals->perPage(),
                'total' => $goals->total(),
                'path' => $goals->path(),
                'links' => [
                    'first' => $goals->url(1),
                    'last' => $goals->url($goals->lastPage()),
                    'prev' => $goals->previousPageUrl(),
                    'next' => $goals->nextPageUrl()
                ]
            ];

            return $this->sendSuccess([
                'data' => GoalResource::collection($goals),
                'meta' => $meta
            ], 'Goals retrieved successfully', self::HTTP_OK);

        } catch (\Exception $e) {
            return $this->sendError(
                'Error retrieving goals',
                ['error' => $e->getMessage()],
                self::HTTP_SERVER_ERROR
            );
        }
    }

    /**
     * Store a newly created goal.
     *
     * @param CreateGoalRequest $request
     * @return JsonResponse
     */
    public function store(CreateGoalRequest $request): JsonResponse
    {
        try {
            $goal = JWTAuth::user()->goals()->create($request->validated());

            return $this->sendCreated(
                new GoalResource($goal),
                'Goal created successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Error creating goal',
                ['error' => $e->getMessage()],
                self::HTTP_SERVER_ERROR
            );
        }
    }

    /**
     * Display the specified goal.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $goal = Goal::with('user')->find($id);

            if (!$goal) {
                return $this->sendNotFound('Goal not found');
            }

            if ($goal->user_id !== JWTAuth::user()->getKey()) {
                return $this->sendForbidden('You do not have permission to view this goal');
            }

            return $this->sendSuccess(
                new GoalResource($goal),
                'Goal retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Error retrieving goal',
                ['error' => $e->getMessage()],
                self::HTTP_SERVER_ERROR,
                [],
                $e
            );
        }
    }

    /**
     * Update the specified goal.
     *
     * @param UpdateGoalRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateGoalRequest $request, int $id): JsonResponse
    {
        try {
            $goal = Goal::find($id);

            if (!$goal) {
                return $this->sendNotFound('Goal not found');
            }

            if ($goal->user_id !== JWTAuth::user()->getKey()) {
                return $this->sendForbidden('You do not have permission to update this goal');
            }

            $goal->update($request->validated());

            return $this->sendSuccess(
                new GoalResource($goal),
                'Goal updated successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Error updating goal',
                ['error' => $e->getMessage()],
                self::HTTP_SERVER_ERROR,
                [],
                $e
            );
        }
    }

    /**
     * Remove the specified goal.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $goal = Goal::find($id);

            if (!$goal) {
                return $this->sendNotFound('Goal not found');
            }

            if ($goal->user_id !== JWTAuth::user()->getKey()) {
                return $this->sendForbidden('You do not have permission to delete this goal');
            }

            $goal->delete();

            return $this->sendSuccess(
                null,
                'Goal deleted successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Error deleting goal',
                ['error' => $e->getMessage()],
                self::HTTP_SERVER_ERROR,
                [],
                $e
            );
        }
    }

    /**
     * Get all goals without pagination and with search functionality.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllGoals(Request $request): JsonResponse
    {
        try {
            $query = JWTAuth::user()->goals();

            // Apply search if provided
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $goals = $query->get();

            return $this->sendSuccess([
                'goals' => GoalResource::collection($goals)
            ], 'Goals retrieved successfully', self::HTTP_OK);

        } catch (\Exception $e) {
            return $this->sendError(
                'Error retrieving goals',
                ['error' => $e->getMessage()],
                self::HTTP_SERVER_ERROR
            );
        }
    }
}
