<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectModels\OrganizationProjectTask;
use Illuminate\Support\Facades\Log;


class OrganizationProjectTaskController extends Controller
{
    // Get tasks by employee_id

public function getEmployeeTasks(Request $request)
{
    try {
        // Validate required and optional parameters
        $request->validate([
            'organization_id' => 'required|integer',
            'employee_id'     => 'nullable|integer',
            'date'            => 'nullable|date',
            'per_page'        => 'nullable|integer|min:1',
            'page'            => 'nullable|integer|min:1',
        ]);

        // Base query
        $query = OrganizationProjectTask::with(['project', 'assignedEmployee'])
            ->where('organization_id', $request->organization_id);

        // Apply optional filters
        if ($request->filled('employee_id')) {
            $query->where('assigned_employee_id', $request->employee_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        // Pagination params
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);

        // Paginate with custom page
        $tasks = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data'    => $tasks,
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationProjectTask::with(['project', 'parentTask', 'subTasks', 'template' ,'category', 'subCategory']);

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
            $tasks = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Tasks fetched successfully',
                'data' => $tasks
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Tasks: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Tasks'], 500);
        }

    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_id' => 'required|integer|exists:organization_project_projects,organization_project_id',
                'organization_project_milestone_id' => 'nullable|integer|exists:organization_project_milestones,organization_project_milestone_id',
                'organization_project_task_template_id' => 'nullable|integer|exists:organization_project_task_templates,organization_project_task_template_id',
                'organization_project_task_category_id' => 'required|nullable|integer|exists:organization_project_task_categories,organization_project_task_category_id',
                'organization_project_task_subcategory_id' => 'nullable|integer|exists:organization_project_task_subcategories,organization_project_task_subcategory_id',
                'task_title' => 'required|string|max:150',
                'parent_task_id' => 'nullable|integer|exists:organization_project_projects,organization_project_id',
                'organization_project_task_type_id' => 'nullable|integer|exists:organization_project_task_types,organization_project_task_type_id',
                'complexity_level' => 'nullable|string|in:Low,Medium,High,Expert',
                'quantity' => 'sometimes|nullable|integer',
                'estimated_minutes' => 'sometimes|nullable|integer',
                'assigned_employee_id' => 'nullable|integer|exists:employees,employee_id',
                'assigned_date' => 'nullable|date',
                'assigned_time' => ['nullable', 'date_format:H:i:s'],
                'scheduled_date' => 'nullable|date',
                'started_time' => ['nullable', 'date_format:H:i:s'],
                'status' => 'nullable|string|in:Pending,In Progress,In Review,Approved,Rejected,Completed,Blocked',
                'status_remarks' => 'nullable|string'
            ]);

            $task = OrganizationProjectTask::create($data);
            return response()->json([
                'message' => 'Task created successfully',
                'data' => $task
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            // return $e;
            Log::error('Error creating task: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create task'], 500);
        }
    }

    public function show($id)
    {
        try {
            $task = OrganizationProjectTask::with(['project', 'parentTask', 'subTasks', 'template','category', 'subCategory'])->find($id);
            if (!$task)
                return response()->json(['message' => 'Task not found'], 404);

            return response()->json(['message' => 'Task fetched', 'data' => $task]);
        } catch (\Exception $e) {
            Log::error('Error showing task: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch task'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $task = OrganizationProjectTask::find($id);
            if (!$task)
                return response()->json(['message' => 'Task not found'], 404);

            $data = $request->validate([
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_id' => 'nullable|integer|exists:organization_project_projects,organization_project_id',
                'organization_project_milestone_id' => 'nullable|integer|exists:organization_project_milestones,organization_project_milestone_id',
                'organization_project_task_template_id' => 'nullable|integer|exists:organization_project_task_templates,organization_project_task_template_id',
                'organization_project_task_category_id' => 'sometimes|nullable|integer|exists:organization_project_task_categories,organization_project_task_category_id',
                'organization_project_task_subcategory_id' => 'nullable|integer|exists:organization_project_task_subcategories,organization_project_task_subcategory_id',
                'task_title' => 'nullable|string|max:150',
                'parent_task_id' => 'nullable|integer|exists:organization_project_projects,organization_project_id',
                'organization_project_task_type_id' => 'nullable|integer|exists:organization_project_task_types,organization_project_task_type_id',
                'complexity_level' => 'nullable|string|in:Low,Medium,High,Expert',
                'quantity' => 'sometimes|nullable|integer',
                'estimated_minutes' => 'sometimes|nullable|integer',
                'assigned_employee_id' => 'nullable|integer|exists:employees,employee_id',
                'assigned_date' => 'nullable|date',
                'assigned_time' => ['nullable', 'date_format:H:i:s'],
                'scheduled_date' => 'nullable|date',
                'started_time' => ['nullable', 'date_format:H:i:s'],
                'status' => 'nullable|string|in:Pending,In Progress,In Review,Approved,Rejected,Completed,Blocked',
                'status_remarks' => 'nullable|string'
            ]);

            $task->update($data);
            return response()->json([
                'message' => 'Task updated successfully',
                'data' => $task
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating task: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update task'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $task = OrganizationProjectTask::find($id);
            if (!$task)
                return response()->json(['message' => 'Task not found'], 404);

            $task->delete();
            return response()->json(['message' => 'Task deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting task: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete task'], 500);
        }
    }
}
