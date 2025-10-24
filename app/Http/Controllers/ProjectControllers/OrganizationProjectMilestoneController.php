<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationProjectMilestone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class OrganizationProjectMilestoneController extends Controller
{
    /**
     * Display a listing of the milestones.
     */
    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationProjectMilestone::with(['project', 'entity']);

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('milestone_title', 'like', '%' . $search . '%')
                        ->orWhere('milestone_code', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $projectMilestone = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Project Milestone fetched successfully',
                'data' => $projectMilestone
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Project Milestone: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Project Milestone'], 500);
        }

    }

    /**
     * Store a newly created milestone.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_project_id' => 'required|integer|exists:organization_project_projects,organization_project_id',
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
            'milestone_title' => 'required|string|max:150',
            'milestone_code' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('organization_project_milestones')
                    ->where(function ($query) use ($request) {
                        return $query->where('organization_id', $request->organization_id);
                    }),
            ],
            'description' => 'nullable|string',
            'start_date' => 'sometimes|nullable|date',
            'due_date' => 'sometimes|nullable|date|after_or_equal:start_date',
            'completed_date' => 'nullable|date|after_or_equal:start_date',
            'status' => ['sometimes', 'nullable', Rule::in(['Planned', 'In Progress', 'Completed', 'Delayed', 'Cancelled'])],
            'is_billable' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $milestone = OrganizationProjectMilestone::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Milestone created successfully.',
                'data' => $milestone,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create milestone.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a single milestone.
     */
    public function show($id)
    {
        try {
            $milestone = OrganizationProjectMilestone::with(['project', 'entity'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $milestone,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Milestone not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching milestone.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified milestone.
     */
    public function update(Request $request, $id)
    {
        try {

            $milestone = OrganizationProjectMilestone::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_project_id' => 'sometimes|integer|exists:organization_project_projects,organization_project_id',
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'milestone_title' => 'sometimes|string|max:150',
                'milestone_code' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:100',
                    Rule::unique('organization_project_milestones', 'milestone_code')->ignore($milestone->organization_project_milestone_id, 'organization_project_milestone_id'),
                ],
                'description' => 'nullable|string',
                'start_date' => 'sometimes|date',
                'due_date' => 'sometimes|date|after_or_equal:start_date',
                'completed_date' => 'nullable|date|after_or_equal:start_date',
                'status' => ['sometimes', 'nullable', 'required', Rule::in(['Planned', 'In Progress', 'Completed', 'Delayed', 'Cancelled'])],
                'is_billable' => 'sometimes|boolean',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $milestone->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Milestone updated successfully.',
                'data' => $milestone,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Milestone not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update milestone.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified milestone.
     */
    public function destroy($id)
    {
        try {
            $milestone = OrganizationProjectMilestone::findOrFail($id);
            $milestone->delete();

            return response()->json([
                'success' => true,
                'message' => 'Milestone deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Milestone not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete milestone.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
