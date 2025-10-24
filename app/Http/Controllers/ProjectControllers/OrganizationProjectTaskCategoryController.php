<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationProjectTaskCategory;
use Illuminate\Http\Request;
use App\Models\ProjectModels\OrganizationProjectTask;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;


class OrganizationProjectTaskCategoryController extends Controller
{
    // Get tasks by employee_id

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationProjectTaskCategory::query();

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('task_category_name', 'like', '%' . $search . '%')
                        ->orWhere('task_category_short_name', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $tasks = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Tasks category fetched successfully',
                'data' => $tasks
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Task Categories: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Tasks Categories'], 500);
        }

    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'task_category_name' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('organization_project_task_categories', 'task_category_name')
                        ->where(fn($query) => $query->where('organization_id', $request->organization_id)),
                ],
                'task_category_short_name' => 'sometimes|nullable|string|max:20',
                'description' => 'sometimes|nullable|string|max:250'
            ]);

            $task = OrganizationProjectTaskCategory::create($data);
            return response()->json([
                'message' => 'Task category created successfully',
                'data' => $task
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {

            Log::error('Error creating task category: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create task category'], 500);
        }
    }

    public function show($id)
    {
        try {
            $task = OrganizationProjectTaskCategory::find($id);
            if (!$task)
                return response()->json(['message' => 'Task Category not found'], 404);

            return response()->json(['message' => 'Task Category fetched', 'data' => $task]);
        } catch (\Exception $e) {
            Log::error('Error showing task category: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch task category'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $task = OrganizationProjectTaskCategory::find($id);
            if (!$task)
                return response()->json(['message' => 'Task category not found'], 404);

            $data = $request->validate([
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'task_category_name' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('organization_project_task_categories', 'task_category_name')
                        ->where(function ($query) use ($request, $task) {
                            $orgId = $request->organization_id ?? $task->organization_id;
                            return $query->where('organization_id', $orgId);
                        })
                        ->ignore($task->organization_project_task_category_id, 'organization_project_task_category_id'),
                ],
                'task_category_short_name' => 'sometimes|nullable|string|max:20',
                'description' => 'sometimes|nullable|string|max:250'
            ]);

            $task->update($data);
            return response()->json([
                'message' => 'Task Category updated successfully',
                'data' => $task
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating task category: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update task category'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $task = OrganizationProjectTaskCategory::find($id);
            if (!$task)
                return response()->json(['message' => 'Task category not found'], 404);

            $task->delete();
            return response()->json(['message' => 'Task category deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting task: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete task'], 500);
        }
    }
}
