<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationProjectTask;
use Illuminate\Http\Request;
use App\Models\ProjectModels\OrganizationProject;
use Illuminate\Validation\Rule;
class OrganizationProjectController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $projects = OrganizationProject::with([
                'client',
                'category',
                'subCategory',
                'type',
                'projectManager'
            ])
                ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
                ->when(
                    $search,
                    fn($q) =>
                    $q->where(function ($q) use ($search) {
                        $q->where('project_name', 'like', "%$search%")
                            ->orWhere('project_code', 'like', "%$search%")
                            ->orWhere('project_short_name', 'like', "%$search%");
                    })
                )
                ->with(['latestTask.assignedEmployee']) // custom relation
                ->withCount('tasks') // optional
                ->orderByDesc(
                    OrganizationProjectTask::select('updated_at')
                        ->whereColumn('organization_project_id', 'organization_project_projects.organization_project_id')
                        ->latest()
                        ->take(1)
                );

            $result = ($perPage === 'all') ? $projects->get() : $projects->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Projects fetched successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return $e;
            \Log::error('Error fetching Projects: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Projects'], 500);
        }
    }

    public function show($id)
    {
        try {
            $project = OrganizationProject::with([ 'client',
                'category',
                'subCategory',
                'type',
                'projectManager','latestTask.assignedEmployee','tasks'])->find($id);
            if (!$project) {
                return response()->json(['message' => 'Not found'], 404);

            }

            return response()->json([
                'message' => 'Project fetched successfully',
                'data' => $project
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching project: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch project'], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_category_id' => 'nullable|integer|exists:organization_project_categories,organization_project_category_id',
                'organization_project_subcategory_id' => 'nullable|integer|exists:organization_project_subcategories,organization_project_subcategory_id',
                'organization_project_type_id' => 'nullable||exists:organization_project_types,organization_project_type_id',
                'organization_client_id' => 'nullable|integer|exists:organization_clients,organization_client_id',
                'project_name' => [
                    'required',
                    'string',
                    'max:150',
                    Rule::unique('organization_project_projects', 'project_name')
                        ->where(fn($query) => $query->where('organization_id', $request->organization_id)),
                ],
                'project_short_name' => 'nullable|string|max:20',
                'description' => 'nullable|string|max:255',
                'is_billable' => 'required|boolean',
                'billing_model' => 'sometimes|nullable|string|in:Fixed Price,Dedicated Resources,Time & Material,Usage based Pricing',
                'billing_frequency' => 'sometimes|nullable|string|in:One Time,Annually,Quarterly,Monthly,Weekly',

                'estimated_hours_period' => 'nullable|string|in:Per Project,Per Year,Per Month,Per Week',
                'estimated_hours' => 'nullable|numeric',
                'start_date' => 'nullable|date',
                'expected_end_date' => 'nullable|date',
                'priority' => 'nullable|string|in:Low,Medium,High,Critical',
                'project_status' => 'required|string|in:Planned,In Progress,On Hold,Completed,Cancelled',

            ]);

            $project = OrganizationProject::create($data);

            return response()->json([
                'message' => 'Project created successfully',
                'data' => $project
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            return $e;
            return response()->json(['message' => 'Failed to create project'], 500);
        }


    }


    public function update(Request $request, $id)
    {
        try {
            $project = OrganizationProject::find($id);
            if (!$project)
                return response()->json(['message' => 'Not found'], 404);

            $data = $request->validate([
                'organization_id' => 'nullable|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_category_id' => 'nullable|integer|exists:organization_project_categories,organization_project_category_id',
                'organization_project_subcategory_id' => 'nullable|integer|exists:organization_project_subcategories,organization_project_subcategory_id',
                'organization_project_type_id' => 'nullable||exists:organization_project_types,organization_project_type_id',
                'organization_client_id' => 'nullable|integer|exists:organization_clients,organization_client_id',
                'project_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_project_projects', 'project_name')
                        ->where(function ($query) use ($request, $project) {
                            $orgId = $request->organization_id ?? $project->organization_id;
                            return $query->where('organization_id', $orgId);
                        })
                        ->ignore($project->organization_project_id, 'organization_project_id'),
                ],
                'project_short_name' => 'nullable|string|max:50',
                'is_billable' => 'required|boolean',
                'billing_model' => 'sometimes|nullable|string|in:Fixed Price,Dedicated Resources,Time & Material,Usage based Pricing',
                'billing_frequency' => 'sometimes|nullable|string|in:One Time,Annually,Quarterly,Monthly,Weekly',
                'estimated_hours_period' => 'nullable|string|in:Per Project,Per Year,Per Month,Per Week',
                'estimated_hours' => 'nullable|numeric',
                'start_date' => 'nullable|date',
                'expected_end_date' => 'nullable|date',
                'priority' => 'nullable|string|in:Low,Medium,High,Critical',
                'project_status' => 'nullable|string|in:Planned,In Progress,On Hold,Completed,Cancelled',
            ]);

            $project->update($data);

            return response()->json([
                'message' => 'Project updated successfully',
                'data' => $project
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating project: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update project'], 500);
        }

    }

    public function destroy($id)
    {
        try {
            $project = OrganizationProject::find($id);
            if (!$project)
                return response()->json(['message' => 'Not found'], 404);
            $project->delete();
            return response()->json(['message' => 'Project deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting Project: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete Project'], 500);
        }
    }
}
