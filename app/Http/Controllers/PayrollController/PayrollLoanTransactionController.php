<?php

namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\PayrollComponent;
use App\Models\PayrollModels\PayrollComponentTypes;
use App\Models\PayrollModels\PayrollLoan;
use App\Models\PayrollModels\PayrollLoanTranscation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PayrollLoanTransactionController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {
            // Get the authenticated user
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $query = PayrollLoanTranscation::with('Loan', 'Employee')->where('organization_id', $org_id);

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('payroll_component_type_name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }


            if ($perPage === 'all') {
                $statusTypes = $query->get();
            } elseif (!empty($perPage) && is_numeric($perPage)) {
                $perPage = (int) $perPage;
                $statusTypes = $query->paginate($perPage, ['*'], 'page', $page);
            } else {
                $statusTypes = $query->get();
            }

            // Return success response
            return response()->json([
                'message' => 'Payroll Loan transaction  fetched successfully',
                'payroll' => $statusTypes
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching attendance break types: ' . $e->getMessage());

            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request, $org_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_payroll_loan_id' => 'nullable|integer|exists:organization_payroll_loans,organization_payroll_loan_id',
                'transaction_type' => 'nullable|in:Disbursement,Installment,Prepayment.Refund,Waiver',
                'payment_mode' => 'nullable|in:Cash,Cheque,Bank Transfer.SalaryDeduction,Other',
                'amount' => 'nullable|numeric',
                'transaction_date' => 'required|date',
                'reference_no' => 'nullable|string',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            PayrollLoanTranscation::create($data);

            return response()->json([
                'message' => 'Payroll Loan added successfully.',
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating payroll loan : ' . $e->getMessage());
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $org_id, $loan_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_loan_transaction_id' => $loan_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_loan_transaction_id' => 'required|integer|exists:organization_payroll_loan_transactions,organization_payroll_loan_transaction_id',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $payroll = PayrollLoanTranscation::find($loan_id);
            $payroll->load('Loan', 'Employee');
            return response()->json([
                'message' => 'Payroll loan transaction found',
                'payroll' => $payroll
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $org_id, $loan_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_loan_transaction_id' => $loan_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_loan_transaction_id' => 'required|integer|exists:organization_payroll_loan_transactions,organization_payroll_loan_transaction_id',

                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_payroll_loan_id' => 'nullable|integer|exists:organization_payroll_loans,organization_payroll_loan_id',
                'transaction_type' => 'nullable|in:Disbursement,Installment,Prepayment.Refund,Waiver',
                'payment_mode' => 'nullable|in:Cash,Cheque,Bank Transfer.SalaryDeduction,Other',
                'amount' => 'nullable|numeric',
                'transaction_date' => 'required|date',
                'reference_no' => 'nullable|string',
                'remarks' => 'nullable|string',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = PayrollLoanTranscation::find($loan_id);
            $payroll->update($request->only([
                'employee_id',
                'organization_entity_id',
                'organization_id',
                'organization_payroll_loan_id',
                'transaction_date',
                'transaction_type',
                'amount',
                'payment_mode',
                'reference_no',
                'remarks',
            ]));
            return response()->json([
                'message' => 'Payroll loan transaction Updated Successfully.',
                'payroll' => $payroll
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $load_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_loan_transaction_id' => $load_id
            ]);
            $validator = Validator::make($request->all(), [
               'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_loan_transaction_id' => 'required|integer|exists:organization_payroll_loan_transactions,organization_payroll_loan_transaction_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = PayrollLoanTranscation::find($load_id);
            $payroll->delete();
            return response()->json([
                'message' => 'payroll loan transaction Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



}
