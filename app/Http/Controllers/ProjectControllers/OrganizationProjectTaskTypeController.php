<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationProjectTaskType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrganizationProjectTaskTypeController extends Controller
{
    /**
     * Display a listing of the task types.
     */
    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationProjectTaskType::with('entity');

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('task_type_name', 'like', '%' . $search . '%')
                        ->orWhere('task_type_short_name', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $taskType = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Task Type fetched successfully',
                'data' => $taskType
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Task Types: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Task Types'], 500);
        }

    }

    /**
     * Store a newly created task type.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'task_type_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('organization_project_task_types')
                    ->where(function ($query) use ($request) {
                        return $query->where('organization_id', $request->organization_id);
                    }),
            ],

            'task_type_short_name' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ], [
            'task_type_name.unique' => 'This task type name already exists for the selected organization.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $taskType = OrganizationProjectTaskType::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Task type created successfully.',
                'data' => $taskType,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create task type.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified task type.
     */
    public function show($id)
    {
        try {
            $taskType = OrganizationProjectTaskType::with('entity')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $taskType,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task type not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching task type.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified task type.
     */
    public function update(Request $request, $id)
    {
        try {
            $taskType = OrganizationProjectTaskType::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'task_type_name' => [
                    'sometimes',
                    'string',
                    'max:150',
                    Rule::unique('organization_project_task_types', 'task_type_name')
                        ->where(function ($query) use ($request, $taskType) {
                            // Use organization_id from request or fallback to existing
                            $orgId = $request->organization_id ?? $taskType->organization_id;
                            return $query->where('organization_id', $orgId);
                        })
                        ->ignore($taskType->organization_project_task_type_id, 'organization_project_task_type_id'),
                ],
                'task_type_short_name' => 'nullable|string|max:50',
                'description' => 'nullable|string',
                'is_active' => 'sometimes|boolean',
            ], [
                'task_type_name.unique' => 'This task type name already exists for the selected organization.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $taskType->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Task type updated successfully.',
                'data' => $taskType,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task type not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task type.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified task type.
     */
    public function destroy($id)
    {
        try {
            $taskType = OrganizationProjectTaskType::findOrFail($id);
            $taskType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task type deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task type not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task type.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
