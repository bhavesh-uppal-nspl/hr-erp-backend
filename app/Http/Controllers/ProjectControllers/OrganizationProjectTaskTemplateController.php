<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationProjectTaskTemplate;
use Illuminate\Http\Request;
use App\Models\ProjectModels\OrganizationProjectTask;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrganizationProjectTaskTemplateController extends Controller
{
    public function index(Request $request)
    {
        try {

            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationProjectTaskTemplate::with(['designation','tasks', 'category', 'subCategory']);

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('task_title', 'like', '%' . $search . '%')
                        ->orWhere('task_short_name', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $taskTemplate = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Task Template fetched successfully',
                'data' => $taskTemplate
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Task Template: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Task Template'], 500);
        }

    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_task_category_id' => 'required|nullable|integer|exists:organization_project_task_categories,organization_project_task_category_id',
                'organization_project_task_subcategory_id' => 'nullable|integer|exists:organization_project_task_subcategories,organization_project_task_subcategory_id',
                'task_title' => [
                    'required',
                    'string',
                    'max:150',
                    Rule::unique('organization_project_task_templates')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'description' => 'nullable|string|max:255',
                'complexity_level' => 'nullable|string|in:Low,Medium,High,Expert',
                'task_instructions' => 'nullable|string',
                'applicable_organization_designation_id' => 'nullable|integer|exists:organization_designations,organization_designation_id',
                'is_task_time_quantity_based' => 'boolean',
                'estimated_minutes' => 'nullable|numeric',
                'quantity_unit' => 'nullable|string|max:10',
                'is_active' => 'sometimes|boolean'
            ]);

            $task = OrganizationProjectTaskTemplate::create($data);
            return response()->json([
                'message' => 'Task Template created successfully',
                'data' => $task
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {

            Log::error('Error creating task template: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create task template'], 500);
        }
    }

    public function show($id)
    {
        try {
            $task = OrganizationProjectTaskTemplate::with(['designation','tasks','category', 'subCategory'])->find($id);
            if (!$task)
                return response()->json(['message' => 'Task template not found'], 404);

            return response()->json(['message' => 'Task template fetched', 'data' => $task]);
        } catch (\Exception $e) {
            Log::error('Error showing task templates: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch task template'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $task = OrganizationProjectTaskTemplate::find($id);
            if (!$task)
                return response()->json(['message' => 'Task not found'], 404);

            $data = $request->validate([
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_task_category_id' => 'sometimes|nullable|integer|exists:organization_project_task_categories,organization_project_task_category_id',
                'organization_project_task_subcategory_id' => 'nullable|integer|exists:organization_project_task_subcategories,organization_project_task_subcategory_id',
                'task_title' => [
                    'sometimes',
                    'string',
                    'max:150',
                    Rule::unique('organization_project_task_templates', 'task_title')
                        ->where(function ($query) use ($request, $task) {
                            // Use organization_id from request or fallback to existing
                            $orgId = $request->organization_id ?? $task->organization_id;
                            return $query->where('organization_id', $orgId);
                        })
                        ->ignore($task->organization_project_task_template_id, 'organization_project_task_template_id'),
                ],
                'description' => 'sometimes|nullable|string|max:255',
                'complexity_level' => 'sometimes|nullable|string|in:Low,Medium,High,Expert',
                'task_instructions' => 'sometimes|nullable|string',
                'applicable_organization_designation_id' => 'sometimes|nullable|integer|exists:organization_designations,organization_designation_id',
                'is_task_time_quantity_based' => 'sometimes|boolean',
                'estimated_minutes' => 'sometimes|nullable|numeric',
                'quantity_unit' => 'sometimes|nullable|string|max:10',
                'is_active' => 'sometimes|boolean'
            ]);

            $task->update($data);
            return response()->json([
                'message' => 'Task template updated successfully',
                'data' => $task
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {

            Log::error('Error updating task template: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update task template'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $task = OrganizationProjectTaskTemplate::find($id);
            if (!$task)
                return response()->json(['message' => 'Task template not found'], 404);

            $task->delete();
            return response()->json(['message' => 'Task template deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting task template: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete task template'], 500);
        }
    }
}
