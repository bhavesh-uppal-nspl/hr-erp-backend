<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\Assesments;
use App\Models\EMSModels\Batch;
use App\Models\EMSModels\BatchClass;
use App\Models\EMSModels\LeadContactTimings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BatchClassesController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = BatchClass::with('trainer', 'batch');

            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('class_status', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $batchClass = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Batch Classes fetched successfully',
                'data' => $batchClass
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Batch Classes: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Batch Classes'], 500);
        }

    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'organization_ems_batch_id' => 'required|integer|exists:organization_ems_batches,organization_ems_batch_id',
            'trainer_employee_id' => 'required|integer|exists:employees,employee_id',
            'class_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'topic' => 'sometimes|nullable|string|max:255',
            'class_status' => 'sometimes|in:Scheduled,Conducted,Cancelled,Trainer Absent',
            'remarks' => 'sometimes|nullable|string|max:1000',
        ], [
            'end_time.after' => 'End time must be after start time.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $batchClass = BatchClass::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Batch Class created successfully.',
                'data' => $batchClass,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Batch Class.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function show($id)
    {
        try {
            $batchClass = BatchClass::with('trainer', 'batch')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $batchClass,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch Class not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Batch Class .',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $batchClass = BatchClass::find($id);

            if (!$batchClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch Class not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_ems_batch_id' => 'sometimes|integer|exists:organization_ems_batches,organization_ems_batch_id',
                'trainer_employee_id' => 'sometimes|integer|exists:employees,employee_id',
                'class_date' => 'sometimes|date',
                'start_time' => 'sometimes|date_format:H:i',
                'end_time' => 'sometimes|date_format:H:i|after:start_time',
                'topic' => 'sometimes|nullable|string|max:255',
                'class_status' => 'sometimes|in:Scheduled,Conducted,Cancelled,Trainer Absent',
                'remarks' => 'sometimes|nullable|string|max:1000',
            ], [
                'end_time.after' => 'End time must be after start time.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $batchClass->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Batch Class updated successfully.',
                'data' => $batchClass,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch Class not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Batch Class.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $batchClass = BatchClass::findOrFail($id);
            $batchClass->delete();

            return response()->json([
                'success' => true,
                'message' => 'Batch Class deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch Class not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Batch Class.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
