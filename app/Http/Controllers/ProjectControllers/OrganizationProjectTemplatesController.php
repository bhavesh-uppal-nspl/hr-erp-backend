<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationProjectTemplates;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationProjectTemplatesController extends Controller
{
    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationProjectTemplates::with('category', 'subCategory');

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('template_name', 'like', '%' . $search . '%')
                        ->orWhere('template_description', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $projects = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Project Templates fetched successfully',
                'data' => $projects
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Project Templates: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Project  Templates'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_category_id' => 'required|integer|exists:organization_project_categories,organization_project_category_id',
                'organization_project_subcategory_id' => 'nullable|integer|exists:organization_project_subcategories,organization_project_subcategory_id',


                'template_name' => [
                    'required',
                    'string',
                    'max:150',
                    Rule::unique('organization_project_templates')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'template_description' => 'nullable|string',
                'created_by' => 'nullable|integer'
            ]);

            $category = OrganizationProjectTemplates::create($data);

            return response()->json([
                'message' => 'Project Templates created successfully',
                'data' => $category
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating template: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create template'], 500);
        }

    }

    public function show($id)
    {
        try {
            $category = OrganizationProjectTemplates::with('category', 'subCategory')->find($id);
            if (!$category)
                return response()->json(['message' => 'Not found'], 404);
            return response()->json([
                'message' => 'Project templates fetched successfully',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching sub category: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch sub category'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $template = OrganizationProjectTemplates::find($id);

            if (!$template)
                return response()->json(['message' => 'Not found'], 404);

            $data = $request->validate([
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_category_id' => 'sometimes|integer|exists:organization_project_categories,organization_project_category_id',
                'organization_project_subcategory_id' => 'sometimes|nullable|integer|exists:organization_project_subcategories,organization_project_subcategory_id',

                'template_name' => [
                    'sometimes',
                    'string',
                    'max:150',
                    Rule::unique('organization_project_categories', 'template_name')
                        ->where(function ($query) use ($request, $template) {
                            // Use organization_id from request or fallback to existing
                            $orgId = $request->organization_id ?? $template->organization_id;
                            return $query->where('organization_id', $orgId);
                        })
                        ->ignore($template->organization_project_template_id, 'organization_project_template_id'),
                ],
                'template_description' => 'nullable|string',
                'created_by' => 'nullable|integer'

            ]);

            $template->update($data);

            return response()->json([
                'message' => 'project template updated successfully',
                'data' => $template
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating template: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update category'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = OrganizationProjectTemplates::find($id);
            if (!$category)
                return response()->json(['message' => 'Not found'], 404);
            $category->delete();
            return response()->json(['message' => 'Project template deleted successfully']);

        } catch (\Exception $e) {
            \Log::error('Error deleting category: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete Project template'], 500);
        }
    }
}
