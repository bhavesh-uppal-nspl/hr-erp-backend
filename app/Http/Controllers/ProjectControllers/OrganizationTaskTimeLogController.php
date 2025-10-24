<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ProjectModels\OrganizationTaskTimeLog;

class OrganizationTaskTimeLogController extends Controller
{
    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationTaskTimeLog::with(['task', 'employee']);

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('hours_logged', 'like', '%' . $search . '%')
                        ->orWhere('remarks', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $timelog = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Task TimeLog fetched successfully',
                'data' => $timelog
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Task TimeLog: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Task TimeLog'], 500);
        }
    }

    public function store(Request $request)
    {


        try {
            $data = $request->validate([
                'organization_project_task_id' => 'required|integer|exists:organization_project_tasks,organization_project_task_id',
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_project_task_type_id' => 'nullable|integer|exists:organization_project_task_types,organization_project_task_type_id',
                'log_date' => 'nullable|date',
                'start_time' => 'nullable|time',
                'end_time' => 'nullable|time',
                'total_minutes' => 'nullable|integer',
                'remarks' => 'nullable|string',
            ]);

            $log = OrganizationTaskTimeLog::create($data);
            return response()->json(['message' => 'Time log created', 'data' => $log], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating Task Logs: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create Task Logs'], 500);
        }
    }

    public function show($id)
    {
        try {
            $log = OrganizationTaskTimeLog::with(['task', 'employee'])->find($id);
            if (!$log)
                return response()->json(['message' => 'Time log not found'], 404);

            return response()->json(['message' => 'Time log fetched', 'data' => $log]);
        } catch (\Exception $e) {
            Log::error('Time log show error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch time log'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $log = OrganizationTaskTimeLog::find($id);
            if (!$log)
                return response()->json(['message' => 'Time log not found'], 404);

            $data = $request->validate([
                'organization_project_task_id' => 'sometimes|integer|exists:organization_project_tasks,organization_project_task_id',
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|integer|exists:organization_entities,organization_entity_id',
                'employee_id' => 'sometimes|integer|exists:employees,employee_id',
                'organization_project_task_type_id' => 'sometimes|integer|exists:organization_project_task_types,organization_project_task_type_id',
                'log_date' => 'sometimes|date',
                'start_time' => 'nullable|time',
                'end_time' => 'nullable|time',
                'total_minutes' => 'nullable|integer',
                'remarks' => 'nullable|string',
            ]);

            $log->update($data);
            return response()->json(['message' => 'Time log updated', 'data' => $log]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating Time Log: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update Time Log'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $log = OrganizationTaskTimeLog::find($id);
            if (!$log)
                return response()->json(['message' => 'Time log not found'], 404);

            $log->delete();
            return response()->json(['message' => 'Time log deleted']);
        } catch (\Exception $e) {
            Log::error('Time log destroy error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete time log'], 500);
        }
    }
}
