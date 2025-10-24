<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\Assesments;
use App\Models\EMSModels\Batch;
use App\Models\EMSModels\BatchClass;
use App\Models\EMSModels\BatchStudent;
use App\Models\EMSModels\LeadContactTimings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BatchStudentsController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = BatchStudent::with('admission', 'batch', 'student');

            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('batch_status', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $batchStudent = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Batch Students fetched successfully',
                'data' => $batchStudent
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Batch Students: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Batch Students'], 500);
        }

    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
            'organization_ems_batch_id' => 'required|integer|exists:organization_ems_batches,organization_ems_batch_id',
            'organization_ems_admission_id' => 'required|integer|exists:organization_ems_admissions,organization_ems_admission_id',
            'organization_ems_student_id' => 'required|integer|exists:organization_ems_students,organization_ems_student_id',
            'enrollment_date' => 'required|date',
            'completion_date' => 'sometimes|nullable|date|after_or_equal:enrollment_date',
            'batch_status' => 'sometimes|in:Active,Completed,Dropped,On Hold',
            'remarks' => 'sometimes|nullable|string|max:1000',
        ], [
            'completion_date.after_or_equal' => 'Completion date must be after or equal to enrollment date.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $batchStudent = BatchStudent::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Batch Student created successfully.',
                'data' => $batchStudent,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Batch Student.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function show($id)
    {
        try {
            $batchStudent = BatchStudent::with('admission', 'batch', 'student')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $batchStudent,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch Student not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Batch Student .',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $batchStudent = BatchStudent::find($id);

            if (!$batchStudent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch Student not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|integer|exists:organization_entities,organization_entity_id',
                'organization_ems_batch_id' => 'sometimes|integer|exists:organization_ems_batches,organization_ems_batch_id',
                'organization_ems_admission_id' => 'sometimes|integer|exists:organization_ems_admissions,organization_ems_admission_id',
                'organization_ems_student_id' => 'sometimes|integer|exists:organization_ems_students,organization_ems_student_id',
                'enrollment_date' => 'sometimes|date',
                'completion_date' => 'sometimes|nullable|date|after_or_equal:enrollment_date',
                'batch_status' => 'sometimes|in:Active,Completed,Dropped,On Hold',
                'remarks' => 'sometimes|nullable|string|max:1000',
            ], [
                'completion_date.after_or_equal' => 'Completion date must be after or equal to enrollment date.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $batchStudent->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Batch Student updated successfully.',
                'data' => $batchStudent,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch Student not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Batch Student.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $batchStudent = BatchStudent::findOrFail($id);
            $batchStudent->delete();

            return response()->json([
                'success' => true,
                'message' => 'Batch Student deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch Student not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Batch Student.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
