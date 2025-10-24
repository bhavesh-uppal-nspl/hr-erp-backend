<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectModels\OrganizationProjectCategory;
use Illuminate\Validation\Rule;

class OrganizationProjectCategoryController extends Controller
{
    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationProjectCategory::with('projects');

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('project_category_name', 'like', '%' . $search . '%')
                        ->orWhere('project_category_short_name', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $projects = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Project Categories fetched successfully',
                'data' => $projects
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Project Categories: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Project Categories'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'project_category_name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('organization_project_categories')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'project_category_short_name' => 'nullable|string|max:20',
                'description' => 'nullable|string',
                'is_active' => 'soemtimes|boolean',
            ]);

            $category = OrganizationProjectCategory::create($data);

            return response()->json([
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
          
            return response()->json(['message' => 'Failed to create category'], 500);
        }

    }

    public function show($id)
    {
        try {
            $category = OrganizationProjectCategory::with('projects')->find($id);
            if (!$category)
                return response()->json(['message' => 'Not found'], 404);
            return response()->json([
                'message' => 'Category fetched successfully',
                'data' => $category
            ]);
        } catch (\Exception $e) {
          
            return response()->json(['message' => 'Failed to fetch category'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $category = OrganizationProjectCategory::find($id);

            if (!$category)
                return response()->json(['message' => 'Not found'], 404);

            $data = $request->validate([
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'project_category_name' => [
                    'sometimes',
                    'string',
                    'max:100',
                    Rule::unique('organization_project_categories', 'project_category_name')
                        ->where(function ($query) use ($request, $category) {
                            // Use organization_id from request or fallback to existing
                            $orgId = $request->organization_id ?? $category->organization_id;
                            return $query->where('organization_id', $orgId);
                        })
                        ->ignore($category->organization_project_category_id, 'organization_project_category_id'),
                ],

                'project_category_short_name' => 'sometimes|nullable|string|max:20',
                'description' => 'sometimes|nullable|string',
                'is_active' => 'sometimes|boolean',
            ]);

            $category->update($data);

            return response()->json([
                'message' => 'Category updated successfully',
                'data' => $category
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
          
            return response()->json(['message' => 'Failed to update category'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = OrganizationProjectCategory::find($id);
            if (!$category)
                return response()->json(['message' => 'Not found'], 404);
            $category->delete();
            return response()->json(['message' => 'Contact deleted successfully']);

        } catch (\Exception $e) {
          
            return response()->json(['message' => 'Failed to delete category'], 500);
        }
    }
}
