<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\AssesmentResults;
use App\Models\EMSModels\Assesments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssesmentResultsController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = AssesmentResults::with('assesment', 'student' , 'admission');

            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('result_status', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $assesmentResults = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Assement Result fetched successfully',
                'data' => $assesmentResults
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Assement Result: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Assement Result'], 500);
        }

    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',

            'organization_ems_assessment_id' => 'required|integer|exists:organization_ems_assessments,organization_ems_assessment_id',
            'organization_ems_student_id' => 'required|integer|exists:organization_ems_students,organization_ems_student_id',
            'organization_ems_admission_id' => 'required|integer|exists:organization_ems_admissions,organization_ems_admission_id',

            'score_obtained' => 'required|numeric|min:0|max:9999.99',

            'result_status' => 'sometimes|in:Pass,Fail,Absent,Pending',

            'remarks' => 'sometimes|nullable|string|max:1000',
        ], [
            'organization_id.exists' => 'Organization must exist.',
            'organization_entity_id.exists' => 'Organization entity must exist.',
            'organization_ems_assessment_id.exists' => 'Assessment must exist.',
            'organization_ems_student_id.exists' => 'Student must exist.',
            'organization_ems_admission_id.exists' => 'Admission must exist.',
            'score_obtained.numeric' => 'Score must be a valid number.',
            'result_status.in' => 'Result status must be Pass, Fail, Absent, or Pending.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $assesmentReuslt = AssesmentResults::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Assesment Result created successfully.',
                'data' => $assesmentReuslt,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Assesment Result.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function show($id)
    {
        try {
            $assesmentResult = AssesmentResults::with('assesment', 'student' , 'admission')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $assesmentResult,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Assesment Result not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Assesment Result .',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $assesmentResult = AssesmentResults::find($id);

            if (!$assesmentResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assesment Result not found.',
                ], 404);
            }



            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',

                'organization_ems_assessment_id' => 'sometimes|integer|exists:organization_ems_assessments,organization_ems_assessment_id',
                'organization_ems_student_id' => 'sometimes|integer|exists:organization_ems_students,organization_ems_student_id',
                'organization_ems_admission_id' => 'sometimes|integer|exists:organization_ems_admissions,organization_ems_admission_id',

                'score_obtained' => 'sometimes|numeric|min:0|max:9999.99',

                'result_status' => 'sometimes|in:Pass,Fail,Absent,Pending',

                'remarks' => 'sometimes|nullable|string|max:1000',
            ], [
                'organization_id.exists' => 'Organization must exist.',
                'organization_entity_id.exists' => 'Organization entity must exist.',
                'organization_ems_assessment_id.exists' => 'Assessment must exist.',
                'organization_ems_student_id.exists' => 'Student must exist.',
                'organization_ems_admission_id.exists' => 'Admission must exist.',
                'score_obtained.numeric' => 'Score must be a valid number.',
                'result_status.in' => 'Result status must be Pass, Fail, Absent, or Pending.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $assesmentResult->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Assesment Result updated successfully.',
                'data' => $assesmentResult,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Assesment Result not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Assesment Result.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $assesmentResult = AssesmentResults::findOrFail($id);
            $assesmentResult->delete();

            return response()->json([
                'success' => true,
                'message' => 'Assesment Result deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Assesment Result not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Assesment Result.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
