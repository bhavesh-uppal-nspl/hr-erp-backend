<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\TrainingProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TrainingProgramController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = TrainingProgram::with('category');;

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('training_program_name', 'like', '%' . $search . '%')
                        ->orWhere('training_program_code', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $taskType = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Training Program fetched successfully',
                'data' => $taskType
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Training Program: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Training Program'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'organization_ems_training_program_category_id' => 'required|nullable|integer|exists:organization_ems_training_program_categories,organization_ems_training_program_category_id',
            'training_program_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('organization_ems_training_programs')
                    ->where(function ($query) use ($request) {
                        return $query->where('organization_id', $request->organization_id);
                    }),
            ],
            'training_program_code' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:255',
            'duration_in_hours' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
        ], [
            'training_program_name.unique' => 'This Training Program name already exists for the selected organization.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $taskType = TrainingProgram::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Training Program created successfully.',
                'data' => $taskType,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Training Program.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $taskType = TrainingProgram::with('category')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $taskType,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Training Program not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Training Program.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $program = TrainingProgram::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_ems_training_program_category_id' => 'sometimes|nullable|integer|exists:organization_ems_training_program_categories,organization_ems_training_program_category_id',
                'training_program_name' => [
                    'sometimes',
                    'string',
                    'max:150',
                    Rule::unique('organization_ems_training_programs', 'training_program_name')
                        ->where(function ($query) use ($request, $program) {
                            // Use organization_id from request or fallback to existing
                            $orgId = $request->organization_id ?? $program->organization_id;
                            return $query->where('organization_id', $orgId);
                        })
                        ->ignore($program->organization_ems_training_program_id, 'organization_ems_training_program_id'),
                ],
                'training_program_code' => 'sometimes|nullable|string|max:20',
                'description' => 'sometimes|nullable|string|max:255',
                'duration_in_hours' => 'sometimes|nullable|integer',
                'is_active' => 'sometimes|boolean',
            ], [
                'training_program_name.unique' => 'This Training Program name already exists for the selected organization.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $program->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Program updated successfully.',
                'data' => $program,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Program not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Program.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $taskType = TrainingProgram::findOrFail($id);
            $taskType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Program deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Program not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Program.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
