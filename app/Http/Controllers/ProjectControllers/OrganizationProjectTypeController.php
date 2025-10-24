<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationProjectType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrganizationProjectTypeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationProjectType::with(['projects']);

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('project_type_name', 'like', '%' . $search . '%')
                        ->orWhere('project_type_short_name', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $projects = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Project Types fetched successfully',
                'data' => $projects
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Project Types: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Project Types'], 500);
        }


    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'project_type_name' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('organization_project_types')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'project_type_short_name' => 'nullable|string|max:20',
                'description' => 'nullable|string|max:255',
                'is_active' => 'sometimes|boolean'
            ]);

            $projectTypes = OrganizationProjectType::create($data);
            return response()->json([
                'message' => 'projectTypes created successfully',
                'data' => $projectTypes
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating projectTypes: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create projectTypes'], 500);
        }
    }

    public function show($id)
    {
        try {
            $projectTypes = OrganizationProjectType::with(['projects'])->find($id);
            if (!$projectTypes)
                return response()->json(['message' => 'Project Task not found'], 404);

            return response()->json(['message' => 'Project Task fetched', 'data' => $projectTypes]);
        } catch (\Exception $e) {
            Log::error('Error showing Project Task: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Project Task'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $projectTypes = OrganizationProjectType::find($id);
            if (!$projectTypes)
                return response()->json(['message' => 'Task not found'], 404);

            $data = $request->validate([
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'project_type_name' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('organization_project_types', 'project_type_name')
                        ->where(function ($query) use ($request, $projectTypes) {
                            // Use organization_id from request or fallback to existing
                            $orgId = $request->organization_id ?? $projectTypes->organization_id;
                            return $query->where('organization_id', $orgId);
                        })
                        ->ignore($projectTypes->organization_project_type_id, 'organization_project_type_id'),
                ],
                'project_type_short_name' => 'sometimes|string|max:20',
                'description' => 'sometimes|nullable|string|max:255',
                'is_active' => 'sometimes|boolean'
            ]);

            $projectTypes->update($data);
            return response()->json([
                'message' => 'Project Types updated successfully',
                'data' => $projectTypes
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating project types: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update project types'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $projectTypes = OrganizationProjectType::find($id);
            if (!$projectTypes)
                return response()->json(['message' => 'Project Types not found'], 404);

            $projectTypes->delete();
            return response()->json(['message' => 'Project Types deleted']);
        } catch (\Exception $e) {
            Log::error('Error deleting proeject types: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete proeject types'], 500);
        }
    }
}
