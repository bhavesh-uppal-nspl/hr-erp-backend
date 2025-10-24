<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationProjectTeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrganizationProjectTeamMemberController extends Controller
{
    /**
     * Display a listing of the team members.
     */
    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationProjectTeamMember::with(['team', 'employee', 'entity']);

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('team', function ($teamQuery) use ($search) {
                        $teamQuery->where('project_team_name', 'like', "%$search%");
                    })
                        ->orWhereHas('employee', function ($empQuery) use ($search) {
                            $empQuery->where('first_name', 'like', "%$search%")
                                ->orWhere('last_name', 'like', "%$search%");
                        })
                        ->orWhere('organization_user_role_id', 'like', "%$search%");
                });
            }

            // Handle pagination or return all
            $teamMembers = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Projects Team Member fetched successfully',
                'data' => $teamMembers
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Projects Team Member : ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Projects Team Member'], 500);
        }

    }

    /**
     * Store a newly created team member.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_project_id' => 'required|integer|exists:organization_project_projects,organization_project_id',
            'organization_project_team_id' => 'required|integer|exists:organization_project_teams,organization_project_team_id',
            'employee_id' => [
                'required',
                'integer',
                'exists:employees,employee_id',
                Rule::unique('organization_project_team_members')
                    ->where(function ($query) use ($request) {
                        return $query->where('organization_project_team_id', $request->organization_project_team_id);
                    }),
            ],
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
            'organization_user_role_id' => 'nullable|integer|exists:organization_user_roles,organization_user_role_id',
            'is_team_lead' => 'required|boolean',
            'joining_date' => 'sometimes|nullable|date',
            'is_active' => 'sometimes|boolean',
        ], [
            // 👇 Custom error message for the unique constraint
            'employee_id.unique' => 'This employee is already assigned to the selected team.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $member = OrganizationProjectTeamMember::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Team member added successfully.',
                'data' => $member,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add team member.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified team member.
     */
    public function show($id)
    {
        try {
            $member = OrganizationProjectTeamMember::with(['team', 'employee', 'entity'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $member,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Team member not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching team member.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified team member.
     */
    public function update(Request $request, $id)
    {
        try {
            $member = OrganizationProjectTeamMember::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_project_id' => 'sometimes|integer|exists:organization_project_projects,organization_project_id',
                'organization_project_team_id' => 'sometimes|integer|exists:organization_project_teams,organization_project_team_id',
                'employee_id' => [
                    'sometimes',
                    'integer',
                    'exists:employees,employee_id',
                    Rule::unique('organization_project_team_members')
                        ->where(function ($query) use ($request, $member) {
                            $teamId = $request->organization_project_team_id ?? $member->organization_project_team_id;
                            return $query->where('organization_project_team_id', $teamId);
                        })
                        ->ignore($member->organization_project_team_member_id, 'organization_project_team_member_id'),
                ],
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_user_role_id' => 'sometimes|nullable|integer|exists:organization_user_roles,organization_user_role_id',
                'is_team_lead' => 'sometimes|boolean',
                'joining_date' => 'sometimes|nullable|date',
                'is_active' => 'sometimes|boolean',
            ], [
                // 👇 Custom error message for the unique constraint
                'employee_id.unique' => 'This employee is already assigned to the selected team.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $member->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Team member updated successfully.',
                'data' => $member,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Team member not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update team member.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified team member.
     */
    public function destroy($id)
    {
        try {
            $member = OrganizationProjectTeamMember::findOrFail($id);
            $member->delete();

            return response()->json([
                'success' => true,
                'message' => 'Team member deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Team member not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete team member.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
