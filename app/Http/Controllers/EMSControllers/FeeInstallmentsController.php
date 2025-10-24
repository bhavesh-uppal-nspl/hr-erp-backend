<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\FeeInstallments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FeeInstallmentsController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = FeeInstallments::with('student', 'admission');

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('remarks', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $feeInstallment = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Fee Installment fetched successfully',
                'data' => $feeInstallment
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching FeeInstallments: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Fee Installment'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'organization_ems_admission_id' => 'required|integer|exists:organization_ems_admissions,organization_ems_admission_id',
            'organization_ems_student_id' => 'required|integer|exists:organization_ems_students,organization_ems_student_id',
            'installment_number' => 'required|integer|min:1',
            'due_date' => 'required|date',
            'amount_due' => 'required|numeric|min:0',
            'currency_code' => 'required|nullable|string|max:10',
            'status' => 'required|nullable|in:Pending,Paid,Overdue,Cancelled',
            'remarks' => 'sometimes|nullable|string',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $feeInstallment = FeeInstallments::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Fee Installment created successfully.',
                'data' => $feeInstallment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Fee Installment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $feeInstallment = FeeInstallments::with('student' , 'admission')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $feeInstallment,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fee Installment not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Fee Installment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $feeInstallment = FeeInstallments::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|integer|exists:organization_entities,organization_entity_id',
                'organization_ems_admission_id' => 'sometimes|integer|exists:organization_ems_admissions,organization_ems_admission_id',
                'organization_ems_student_id' => 'sometimes|integer|exists:organization_ems_students,organization_ems_student_id',
                'installment_number' => 'sometimes|integer|min:1',
                'due_date' => 'sometimes|date',
                'amount_due' => 'sometimes|numeric|min:0',
                'currency_code' => 'required|nullable|string|max:10',
                'status' => 'required|nullable|in:Pending,Paid,Overdue,Cancelled',
                'remarks' => 'sometimes|nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $feeInstallment->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Fee Installment updated successfully.',
                'data' => $feeInstallment,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fee Installment not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Fee Installment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $feeInstallment = FeeInstallments::findOrFail($id);
            $feeInstallment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fee Installment deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fee Installment not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Fee Installment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
