<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationProjectTaskCategory;
use App\Models\ProjectModels\OrganizationProjectTaskSubCategory;
use Illuminate\Http\Request;
use App\Models\ProjectModels\OrganizationProjectTask;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;


class OrganizationProjectTaskSubCategoryController extends Controller
{
    // Get tasks by employee_id

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');
             $categoryId = $request->get('category_id'); // ✅ Get category_id

            $query = OrganizationProjectTaskSubCategory::with('category');

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }
            
            if (!empty($categoryId)) {
                $query->where('organization_project_task_category_id', $categoryId); // ✅ Filter by category
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('task_subcategory_name', 'like', '%' . $search . '%')
                        ->orWhere('task_subcategory_short_name', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $tasks = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Tasks sub category fetched successfully',
                'data' => $tasks
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Task Sub Categories: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Tasks Sub Categories'], 500);
        }

    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_task_category_id' => 'required|integer|exists:organization_project_task_categories,organization_project_task_category_id',
                'task_subcategory_name' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('organization_project_task_subcategories', 'task_subcategory_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_project_task_category_id', $request->organization_project_task_category_id);
                        })
                ],
                'task_subcategory_short_name' => 'sometimes|nullable|string|max:20',
                'description' => 'sometimes|nullable|string|max:250'
            ]);

            $task = OrganizationProjectTaskSubCategory::create($data);
            return response()->json([
                'message' => 'Task sub category created successfully',
                'data' => $task
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {

            Log::error('Error creating task sub category: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create task sub category'], 500);
        }
    }

    public function show($id)
    {
        try {
            $task = OrganizationProjectTaskSubCategory::find($id);
            if (!$task)
                return response()->json(['message' => 'Task Sub Category not found'], 404);

            return response()->json(['message' => 'Task Sub Category fetched', 'data' => $task]);
        } catch (\Exception $e) {
            Log::error('Error showing task sub category: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch task sub category'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $task = OrganizationProjectTaskSubCategory::find($id);
            if (!$task)
                return response()->json(['message' => 'Task category not found'], 404);

            $data = $request->validate([
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_task_category_id' => 'required|integer|exists:organization_project_task_categories,organization_project_task_category_id',
                 'task_subcategory_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('organization_project_task_subcategories', 'task_subcategory_name')
                    ->where(function ($query) use ($request) {
                        return $query->where('organization_project_task_category_id', $request->organization_project_task_category_id);
                    })
                    ->ignore($id, 'organization_project_task_subcategory_id') // primary key column
            ],
                'task_subcategory_short_name' => 'sometimes|nullable|string|max:20',
                'description' => 'sometimes|nullable|string|max:250'

            ]);

            $task->update($data);
            return response()->json([
                'message' => 'Task Sub Category updated successfully',
                'data' => $task
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating task sub category: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update task sub category'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $task = OrganizationProjectTaskSubCategory::find($id);
            if (!$task)
                return response()->json(['message' => 'Task sub category not found'], 404);

            $task->delete();
            return response()->json(['message' => 'Task sub category deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting task: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete sub task'], 500);
        }
    }
}
