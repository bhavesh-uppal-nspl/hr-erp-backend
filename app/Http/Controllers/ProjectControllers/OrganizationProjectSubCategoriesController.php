<?php

namespace App\Http\Controllers\ProjectControllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectModels\OrganizationProjectSubCategory;
use Illuminate\Http\Request;
use App\Models\ProjectModels\OrganizationProjectCategory;
use Illuminate\Validation\Rule;

class OrganizationProjectSubCategoriesController extends Controller
{
    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');
            $categoryId = $request->get('category_id'); // ✅ Get category_id

            $query = OrganizationProjectSubCategory::with('category');

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            if (!empty($categoryId)) {
                $query->where('organization_project_category_id', $categoryId); // ✅ Filter by category
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('project_subcategory_name', 'like', '%' . $search . '%')
                        ->orWhere('project_subcategory_short_name', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $projects = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Project Sub Categories fetched successfully',
                'data' => $projects
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Project Sub Categories: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Project Sub Categories'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_category_id' => 'required|integer|exists:organization_project_categories,organization_project_category_id',
                'project_subcategory_name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('organization_project_subcategories')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id)
                                ->where('organization_project_category_id', $request->organization_project_category_id);
                        }),
                ],
                'project_subcategory_short_name' => 'nullable|string|max:20',
                'description' => 'nullable|string',
                'is_active' => 'soemtimes|boolean',
            ], [
                'project_subcategory_name.unique' => 'The subcategory name already exists for this organization and category.',
            ]);

            $category = OrganizationProjectSubCategory::create($data);

            return response()->json([
                'message' => 'Sub Category created successfully',
                'data' => $category
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating category: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create category'], 500);
        }

    }

    public function show($id)
    {
        try {
            $category = OrganizationProjectSubCategory::with('category')->find($id);
            if (!$category)
                return response()->json(['message' => 'Not found'], 404);
            return response()->json([
                'message' => 'SubCategory fetched successfully',
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

            $category = OrganizationProjectSubCategory::find($id);

            if (!$category)
                return response()->json(['message' => 'Not found'], 404);

            $data = $request->validate([
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_project_category_id' => 'sometimes|nullable|integer|exists:organization_project_categories,organization_project_category_id',
                'project_subcategory_name' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:100',
                    Rule::unique('organization_project_subcategories')
                        ->ignore($id) // the ID of the row being updated
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id)
                                ->where('organization_project_category_id', $request->organization_project_category_id);
                        }),
                ],
                'project_subcategory_short_name' => 'sometimes|nullable|string|max:20',
                'description' => 'sometimes|nullable|string',
                'is_active' => 'sometimes|boolean',
            ]);

            $category->update($data);

            return response()->json([
                'message' => 'Sub Category updated successfully',
                'data' => $category
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $ve->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating sub category: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update category'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = OrganizationProjectSubCategory::find($id);
            if (!$category)
                return response()->json(['message' => 'Not found'], 404);
            $category->delete();
            return response()->json(['message' => 'SubCategory deleted successfully']);

        } catch (\Exception $e) {
            \Log::error('Error deleting category: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete subcategory'], 500);
        }
    }
}
