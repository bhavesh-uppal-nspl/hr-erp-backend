<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\TrainingProgramCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TrainingProgramCategoriesController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = TrainingProgramCategory::query();

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('training_program_category_name', 'like', '%' . $search . '%')
                        ->orWhere('training_program_category_code', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $taskType = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Training Program Category fetched successfully',
                'data' => $taskType
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Training Porgam Category: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Training Porgam Category'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'training_program_category_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('organization_ems_training_program_categories')
                    ->where(function ($query) use ($request) {
                        return $query->where('organization_id', $request->organization_id);
                    }),
            ],
            'training_program_category_code' => 'nullable|string|max:10',
            'description' => 'nullable|string|max:255'
        ], [
            'training_program_category_name.unique' => 'This Training Program Category name already exists for the selected organization.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $taskType = TrainingProgramCategory::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Training Program Category created successfully.',
                'data' => $taskType,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Training Program Category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $taskType = TrainingProgramCategory::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $taskType,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Training Program Category not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Training Program Category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $programCategory = TrainingProgramCategory::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'training_program_category_name' => [
                    'sometimes',
                    'string',
                    'max:100',
                    Rule::unique('organization_ems_training_program_categories', 'training_program_category_name')
                        ->where(function ($query) use ($request, $programCategory) {
                            // Use organization_id from request or fallback to existing
                            $orgId = $request->organization_id ?? $programCategory->organization_id;
                            return $query->where('organization_id', $orgId);
                        })
                        ->ignore($programCategory->organization_ems_training_program_category_id, 'organization_ems_training_program_category_id'),
                ],
                'training_program_category_code' => 'nullable|string|max:10',
                'description' => 'nullable|string|max:255'
            ], [
                'training_program_category_name.unique' => 'This Training Program Category name already exists for the selected organization.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $programCategory->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Program Category updated successfully.',
                'data' => $programCategory,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Program Category not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Program Category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $taskType = TrainingProgramCategory::findOrFail($id);
            $taskType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Program Category deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Program Category not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Program Category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
