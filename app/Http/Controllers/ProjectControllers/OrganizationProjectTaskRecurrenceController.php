<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationProjectTaskRecurrence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrganizationProjectTaskRecurrenceController extends Controller
{
    /**
     * Display a listing of the recurrences.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationProjectTaskRecurrence::with(['task', 'organization']);

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('recurrence_pattern', 'like', '%' . $search . '%')
                        ->orWhere('recurrence_days', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $taskRecurrence = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Projects Task Recurrence fetched successfully',
                'data' => $taskRecurrence
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Projects Task Recurrence: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Projects Task Recurrence'], 500);
        }
    }

    /**
     * Store a newly created recurrence.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_project_task_id' => 'required|integer|exists:organization_project_tasks,organization_project_task_id',
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'recurrence_pattern' => ['required', Rule::in(['Daily', 'Alternate Days', 'Weekly', 'Monthly'])],
            'recurrence_days' => ['nullable', Rule::in(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])],
            'recurrence_interval' => 'sometimes|nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $recurrence = OrganizationProjectTaskRecurrence::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Recurrence created successfully.',
                'data' => $recurrence,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create recurrence.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified recurrence.
     */
    public function show($id)
    {
        try {
            $recurrence = OrganizationProjectTaskRecurrence::with(['task', 'organization'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $recurrence,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recurrence not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching recurrence.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified recurrence.
     */
    public function update(Request $request, $id)
    {
        try {
            $recurrence = OrganizationProjectTaskRecurrence::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_project_task_id' => 'sometimes|integer|exists:organization_project_tasks,organization_project_task_id',
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'recurrence_pattern' => ['sometimes', Rule::in(['Daily', 'Alternate Days', 'Weekly', 'Monthly'])],
                'recurrence_days' => ['sometimes|nullable', Rule::in(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])],
                'recurrence_interval' => 'sometimes|integer|min:1',
                'start_date' => 'sometimes|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $recurrence->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Recurrence updated successfully.',
                'data' => $recurrence,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recurrence not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update recurrence.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified recurrence.
     */
    public function destroy($id)
    {
        try {
            $recurrence = OrganizationProjectTaskRecurrence::findOrFail($id);
            $recurrence->delete();

            return response()->json([
                'success' => true,
                'message' => 'Recurrence deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recurrence not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete recurrence.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
