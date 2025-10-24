<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationProjectTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrganizationProjectTeamController extends Controller
{
    /**
     * Display a listing of the project teams.
     */
    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');
            $project_id = $request->get('project_id');

            $query = OrganizationProjectTeam::with(['project', 'entity', 'members']);

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }
            if (!empty($project_id)) {
                $query->where('organization_project_id', $project_id);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('project_team_name', 'like', '%' . $search . '%')
                        ->orWhere('project_team_short_name', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $projectsTeam = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Projects Team fetched successfully',
                'data' => $projectsTeam
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Projects Team: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Projects Team'], 500);
        }

    }

    /**
     * Store a newly created project team.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_project_id' => 'required|integer|exists:organization_project_projects,organization_project_id',
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
            'project_team_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('organization_project_teams')
                    ->where(function ($query) use ($request) {
                        return $query->where('organization_id', $request->organization_id);
                    }),
            ],

            'project_team_short_name' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $team = OrganizationProjectTeam::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Project team created successfully.',
                'data' => $team,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project team.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified project team.
     */
    public function show($id)
    {
        try {
            $team = OrganizationProjectTeam::with(['project', 'entity', 'members'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $team,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project team not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching project team.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified project team.
     */
    public function update(Request $request, $id)
    {
        try {
            $team = OrganizationProjectTeam::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_project_id' => 'sometimes|integer|exists:organization_project_projects,organization_project_id',
                'organization_id' => 'sometimes|nullable|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'project_team_name' => [
                    'sometimes',
                    'string',
                    'max:150',
                    Rule::unique('organization_project_categories', 'project_team_name')
                        ->where(function ($query) use ($request, $team) {
                            // Use organization_id from request or fallback to existing
                            $orgId = $request->organization_id ?? $team->organization_id;
                            return $query->where('organization_id', $orgId);
                        })
                        ->ignore($team->organization_project_team_id, 'organization_project_team_id'),
                ],
                'project_team_short_name' => 'nullable|string|max:50',
                'description' => 'nullable|string|max:255',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $team->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Project team updated successfully.',
                'data' => $team,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project team not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project team.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified project team.
     */
    public function destroy($id)
    {
        try {
            $team = OrganizationProjectTeam::findOrFail($id);
            $team->delete();

            return response()->json([
                'success' => true,
                'message' => 'Project team deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project team not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete project team.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
