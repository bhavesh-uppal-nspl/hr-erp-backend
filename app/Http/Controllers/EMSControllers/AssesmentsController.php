<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\Assesments;
use App\Models\EMSModels\LeadContactTimings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssesmentsController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = Assesments::with('trainingProgram', 'batch');

            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('assessment_name', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $assesment = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Assement fetched successfully',
                'data' => $assesment
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Assement: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Assement'], 500);
        }

    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'training_program_id' => 'required|integer|exists:organization_ems_training_programs,organization_ems_training_program_id',
            'organization_ems_batch_id' => 'sometimes|nullable|integer|exists:organization_ems_batches,organization_ems_batch_id',

            'assessment_name' => 'required|string|max:150',
            'assessment_type' => 'required|in:Quiz,Assignment,Project,Final Exam,Practical,Other',
            'max_score' => 'required|numeric|min:0|max:999999.99',
            'passing_score' => 'sometimes|nullable|numeric|min:0|max:999999.99|lte:max_score',

            'assessment_date' => 'required|date|after_or_equal:today',
            'status' => 'sometimes|in:Scheduled,Conducted,Cancelled',
            'remarks' => 'sometimes|nullable|string|max:1000',
        ], [
            'organization_id.required' => 'Organization ID is required.',
            'organization_id.exists' => 'Organization must exist.',
            'training_program_id.required' => 'Training program is required.',
            'training_program_id.exists' => 'Training program must exist.',
            'assessment_name.required' => 'Assessment name is required.',
            'assessment_type.in' => 'Assessment type must be one of: Quiz, Assignment, Project, Final Exam, Practical, Other.',
            'max_score.required' => 'Maximum score is required.',
            'passing_score.lte' => 'Passing score must be less than or equal to maximum score.',
            'assessment_date.after_or_equal' => 'Assessment date cannot be in the past.',
            'status.in' => 'Status must be Scheduled, Conducted, or Cancelled.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $assesment = Assesments::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Assesment created successfully.',
                'data' => $assesment,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Assesment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function show($id)
    {
        try {
            $assesment = Assesments::with('trainingProgram', 'batch')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $assesment,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Assesment not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Assesment .',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $assesment = Assesments::find($id);

            if (!$assesment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assesment not found.',
                ], 404);
            }


            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'training_program_id' => 'sometimes|integer|exists:organization_ems_training_programs,organization_ems_training_program_id',
                'organization_ems_batch_id' => 'sometimes|nullable|integer|exists:organization_ems_batches,organization_ems_batch_id',

                'assessment_name' => 'sometimes|string|max:150',

                'assessment_type' => 'sometimes|in:Quiz,Assignment,Project,Final Exam,Practical,Other',

                'max_score' => 'sometimes|numeric|min:0|max:999999.99',
                'passing_score' => 'sometimes|nullable|numeric|min:0|max:999999.99|lte:max_score',

                'assessment_date' => 'sometimes|date|after_or_equal:today',

                'status' => 'sometimes|in:Scheduled,Conducted,Cancelled',

                'remarks' => 'sometimes|nullable|string|max:1000',
            ], [
                'organization_id.exists' => 'Organization must exist.',
                'training_program_id.exists' => 'Training program must exist.',
                'assessment_type.in' => 'Assessment type must be one of: Quiz, Assignment, Project, Final Exam, Practical, Other.',
                'passing_score.lte' => 'Passing score must be less than or equal to maximum score.',
                'assessment_date.after_or_equal' => 'Assessment date cannot be in the past.',
                'status.in' => 'Status must be Scheduled, Conducted, or Cancelled.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $assesment->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Assesment updated successfully.',
                'data' => $assesment,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Assesment not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Assesment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $assesment = Assesments::findOrFail($id);
            $assesment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Assesment deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Assesment not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Assesment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
