<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\Student;
use App\Models\EMSModels\StudentFees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentsFeesController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = StudentFees::with('admission', 'student', 'installment');

            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('payment_number', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $studentFee = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Student Fee fetched successfully',
                'data' => $studentFee
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Student Fee: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Student Fee'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'organization_ems_admission_id' => 'required|nullable|integer|exists:organization_ems_admissions,organization_ems_admission_id',
            'organization_ems_student_id' => 'required|nullable|integer|exists:organization_ems_students,organization_ems_student_id',
            'organization_ems_fee_installment_id' => 'sometimes|nullable|integer|exists:organization_ems_fee_installments,organization_ems_fee_installment_id',
            'payment_number' => 'required|string|max:50|unique:organization_ems_payments,payment_number',
            'payment_date' => 'required|date',

            'student_currency_code' => 'required|string|max:10',
            'amount_paid_student_currency' => 'required|numeric|min:0',

            'settlement_currency_code' => 'required|string|max:10',
            'amount_received_inr' => 'required|numeric|min:0',

            'settlement_date' => 'nullable|date',
            'gateway_charges' => 'nullable|numeric|min:0',
            'forex_difference' => 'nullable|numeric',

            'payment_mode' => 'required|in:UPI,Credit Card,Debit Card,NetBanking,PayPal,Stripe,Razorpay,Other',
            'transaction_reference' => 'nullable|string|max:100',
            'payment_status' => 'nullable|in:Pending,Successful,Failed,Refunded,Settled',

            'remarks' => 'nullable|string',
        ], [
            'payment_number.required' => 'Payment number is required.',
            'payment_number.unique' => 'This payment number already exists.',
            'payment_date.required' => 'Payment date is required.',
            'amount_paid_student_currency.required' => 'Amount paid in student currency is required.',
            'amount_received_inr.required' => 'Amount received in INR is required.',
            'payment_mode.required' => 'Payment mode is required.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }


        try {
            $studentfee = StudentFees::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Student Fee created successfully.',
                'data' => $studentfee,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $studentFee = StudentFees::with('admission', 'student', 'installment')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $studentFee,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student Fee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Student Fee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try{
        $studentFee = StudentFees::find($id);

        if (!$studentFee) {
            return response()->json([
                'success' => false,
                'message' => 'Student Fee not found.',
            ], 404);
        }

         $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'organization_ems_admission_id' => 'sometimes|nullable|integer|exists:organization_ems_admissions,organization_ems_admission_id',
            'organization_ems_student_id' => 'sometimes|nullable|integer|exists:organization_ems_students,organization_ems_student_id',
            'organization_ems_fee_installment_id' => 'sometimes|nullable|integer|exists:organization_ems_fee_installments,organization_ems_fee_installment_id',
            'payment_number' => 'sometimes|string|max:50|unique:organization_ems_payments,payment_number',
            'payment_date' => 'sometimes|date',

            'student_currency_code' => 'sometimes|string|max:10',
            'amount_paid_student_currency' => 'sometimes|numeric|min:0',

            'settlement_currency_code' => 'sometimes|string|max:10',
            'amount_received_inr' => 'sometimes|numeric|min:0',

            'settlement_date' => 'sometimes|nullable|date',
            'gateway_charges' => 'sometimes|nullable|numeric|min:0',
            'forex_difference' => 'sometimes|nullable|numeric',

            'payment_mode' => 'required|in:UPI,Credit Card,Debit Card,NetBanking,PayPal,Stripe,Razorpay,Other',
            'transaction_reference' => 'sometimes|nullable|string|max:100',
            'payment_status' => 'sometimes|nullable|in:Pending,Successful,Failed,Refunded,Settled',

            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $studentFee->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Student Fee updated successfully.',
                'data' => $studentFee,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student Fee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Student Fee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $studentFee = StudentFees::findOrFail($id);
            $studentFee->delete();

            return response()->json([
                'success' => true,
                'message' => 'Student Fees deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student Fees not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Student Fees.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
