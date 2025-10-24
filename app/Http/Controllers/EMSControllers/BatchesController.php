<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\Assesments;
use App\Models\EMSModels\Batch;
use App\Models\EMSModels\LeadContactTimings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BatchesController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = Batch::with('trainingProgram');

            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('batch_name', 'like', '%' . $search . '%')->orWhere('batch_code', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $batch = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Batch fetched successfully',
                'data' => $batch
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Batch: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Batch'], 500);
        }

    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'training_program_id' => 'required|integer|exists:organization_ems_training_programs,organization_ems_training_program_id',

            'batch_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('organization_ems_batches', 'batch_code')
                    ->where(fn($query) => $query->where('organization_id', $request->organization_id)),
            ],
            'batch_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('organization_ems_batches', 'batch_name')
                    ->where(fn($query) => $query->where('organization_id', $request->organization_id)),
            ],

            'start_date' => 'required|date',
            'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',

            'batch_mode' => 'required|in:Online,Offline,Hybrid',
            'status' => 'required|in:Scheduled,Ongoing,Completed,Cancelled',

            'preferred_study_slot' => 'sometimes|nullable|in:Morning,Afternoon,Evening,Weekend',
            'timing_details' => 'sometimes|nullable|string|max:150',
            'remarks' => 'sometimes|nullable|string|max:1000',
        ], [
            'batch_code.unique' => 'This batch code already exists for this organization.',
            'batch_name.unique' => 'This batch name already exists for this organization.',
            'end_date.after_or_equal' => 'End date must be same or after start date.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $batch = Batch::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Batch created successfully.',
                'data' => $batch,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Batch.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function show($id)
    {
        try {
            $batch = Batch::with('trainingProgram')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $batch,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Batch .',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $batch = Batch::find($id);

            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'training_program_id' => 'sometimes|integer|exists:organization_ems_training_programs,organization_ems_training_program_id',

                'batch_code' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('organization_ems_batches', 'batch_code')
                        ->where(fn($query) => $query->where('organization_id', $request->organization_id ?? $batch->organization_id))
                        ->ignore($batch->organization_ems_batch_id, 'organization_ems_batch_id'),
                ],
                'batch_name' => [
                    'sometimes',
                    'string',
                    'max:100',
                    Rule::unique('organization_ems_batches', 'batch_name')
                        ->where(fn($query) => $query->where('organization_id', $request->organization_id ?? $batch->organization_id))
                        ->ignore($batch->organization_ems_batch_id, 'organization_ems_batch_id'),
                ],

                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',

                'batch_mode' => 'sometimes|in:Online,Offline,Hybrid',
                'status' => 'sometimes|in:Scheduled,Ongoing,Completed,Cancelled',

                'preferred_study_slot' => 'sometimes|nullable|in:Morning,Afternoon,Evening,Weekend',
                'timing_details' => 'sometimes|nullable|string|max:150',
                'remarks' => 'sometimes|nullable|string|max:1000',
            ], [
                'batch_code.unique' => 'This batch code already exists for this organization.',
                'batch_name.unique' => 'This batch name already exists for this organization.',
                'end_date.after_or_equal' => 'End date must be same or after start date.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $batch->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Batch updated successfully.',
                'data' => $batch,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Batch.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $batch = Batch::findOrFail($id);
            $batch->delete();

            return response()->json([
                'success' => true,
                'message' => 'Batch deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Batch.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
