<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\Admission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdmissionController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = Admission::with('student', 'lead', 'trainingProgram');

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('admission_number', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $admission = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Admission fetched successfully',
                'data' => $admission
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Admission: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Admission'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
            'organization_ems_student_id' => 'required|integer|exists:organization_ems_students,organization_ems_student_id',
            'organization_ems_lead_id' => 'required|integer|exists:organization_ems_leads,organization_ems_lead_id',
            'training_program_id' => 'required|integer|exists:organization_ems_training_programs,organization_ems_training_program_id',
            'organization_ems_demo_session_id' => 'nullable|integer|exists:organization_ems_demo_sessions,organization_ems_demo_session_id',
            'admission_number' => 'required|string|max:50|unique:organization_ems_admissions,admission_number',
            'admission_date' => 'required|date',
            'admission_status' => 'required|in:Pending,Confirmed,Cancelled,Completed',
            'total_fee_amount' => 'required|numeric|min:0',
            'discount_amount' => 'sometimes|nullable|numeric|min:0',
            'discount_reason' => 'sometimes|nullable|string|max:255',
            'net_fee_amount' => 'required|numeric|min:0',
            'installment_count' => 'required|integer|min:1',
            'currency_code' => 'sometimes|nullable|string|max:10',
            'preferred_study_slot' => 'sometimes|nullable|in:Morning,Afternoon,Evening,Weekend',
            'preferred_study_times' => 'sometimes|nullable|string|max:100',
            'remarks' => 'sometimes|nullable|string',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $admission = Admission::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Admission created successfully.',
                'data' => $admission,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Admission.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $admission = Admission::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $admission,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Admission not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Admission.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $admission = Admission::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|integer|exists:organization_entities,organization_entity_id',
                'organization_ems_student_id' => 'sometimes|integer|exists:organization_ems_students,organization_ems_student_id',
                'organization_ems_lead_id' => 'sometimes|integer|exists:organization_ems_leads,organization_ems_lead_id',
                'training_program_id' => 'sometimes|integer|exists:organization_ems_training_programs,organization_ems_training_program_id',
                'organization_ems_demo_session_id' => 'sometimes|integer|exists:organization_ems_demo_sessions,organization_ems_demo_session_id',

                'admission_number' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('organization_ems_admissions')
                        ->ignore($admission->organization_ems_admission_id, 'organization_ems_admission_id')
                ],
                'admission_date' => 'sometimes|date',
                'admission_status' => 'sometimes|in:Pending,Confirmed,Cancelled,Completed',
                'total_fee_amount' => 'sometimes|numeric|min:0',
                'discount_amount' => 'sometimes|nullable|numeric|min:0',
                'discount_reason' => 'sometimes|nullable|string|max:255',
                'net_fee_amount' => 'sometimes|numeric|min:0',
                'installment_count' => 'sometimes|integer|min:1',
                'currency_code' => 'sometimes|string|max:10',
                'preferred_study_slot' => 'sometimes|nullable|in:Morning,Afternoon,Evening,Weekend',
                'preferred_study_times' => 'sometimes|nullable|string|max:100',
                'remarks' => 'sometimes|nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $admission->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Admission updated successfully.',
                'data' => $admission,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Admission not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Admission.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $admission = Admission::findOrFail($id);
            $admission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Admission deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Admission not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Admission.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
