<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationProjectMilestone;
use App\Models\ProjectModels\OrganizationProjectMilestoneTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class OrganizationProjectMilestoneTemplatesController extends Controller
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
            $projectTemplateId = $request->get('organization_project_template_id');

            $query = OrganizationProjectMilestoneTemplate::with(['projectTemplate']);

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }
            if (!empty($projectTemplateId)) {
                $query->where('organization_project_template_id', $projectTemplateId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('milestone_title', 'like', '%' . $search . '%')
                        ->orWhere('milestone_description', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $projectMilestone = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Project Milestone templates fetched successfully',
                'data' => $projectMilestone
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Project Milestone template: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Project Milestone template'], 500);
        }

    }

    /**
     * Store a newly created milestone.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
            'organization_project_template_id' => 'required|integer|exists:organization_project_templates,organization_project_template_id',
            'milestone_title' => [
                'required', // or 'sometimes|required' if conditionally required
                'string',
                'max:100',
                Rule::unique('organization_project_template_milestones', 'milestone_title')
                    ->where(function ($query) use ($request) {
                        return $query->where('organization_project_template_id', $request->organization_project_template_id);
                    }),
            ],
            'milestone_description' => 'nullable|string|max:255',
            'milestone_order' => 'sometimes|nullable|integer',
            'expected_completion_days' => 'sometimes|nullable|integer',
        ], [
            // ✅ Optional: Custom message
            'milestone_title.unique' => 'This milestone title already exists for the selected project template.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $milestone = OrganizationProjectMilestoneTemplate::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Milestone template created successfully.',
                'data' => $milestone,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create milestone template.',
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
            $milestone = OrganizationProjectMilestoneTemplate::with(['projectTemplate'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $milestone,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Milestone template not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching milestone template.',
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

            $milestone = OrganizationProjectMilestoneTemplate::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_template_id' => 'required|integer|exists:organization_project_templates,organization_project_template_id',
                'milestone_title' => [
                    'sometimes',
                    'string',
                    'max:100',
                    Rule::unique('organization_project_template_milestones', 'milestone_title')
                        ->ignore($milestone->organization_project_template_milestone_id, 'organization_project_template_milestone_id')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_project_template_id', $request->organization_project_template_id);
                        }),

                ],
                'milestone_description' => 'sometimes|nullable|string|max:255',
                'milestone_order' => 'sometimes|nullable|integer',
                'expected_completion_days' => 'sometimes|nullable|integer',
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
                'message' => 'Milestone template updated successfully.',
                'data' => $milestone,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Milestone template not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update milestone template.',
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
            $milestone = OrganizationProjectMilestoneTemplate::findOrFail($id);
            $milestone->delete();

            return response()->json([
                'success' => true,
                'message' => 'Milestone template deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Milestone template not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete milestone template.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
