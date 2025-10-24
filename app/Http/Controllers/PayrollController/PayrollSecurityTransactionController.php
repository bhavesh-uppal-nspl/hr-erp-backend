<?php

namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\PayrollSecurity;
use App\Models\PayrollModels\SecurityTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;

class PayrollSecurityTransactionController extends Controller
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

            $query = SecurityTransaction::with('Employee', 'PayrollSecurity')->where('organization_id', $org_id);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('pay_frequency', 'like', '%' . $search . '%')
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
            return response()->json([
                'message' => 'Payroll security transaction  fetched successfully',
                'payroll' => $statusTypes
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching security transaction' . $e->getMessage());

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
                'organization_payroll_security_id' => 'nullable|integer|exists:organization_payroll_securities,organization_payroll_security_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'transaction_type' => 'required|in:Collection,Refund,Completed,Adjustment,Forfeited',
                'amount' => 'nullable|numeric|min:0',
                'reference_no' => 'nullable|string|max:100',
                'transaction_date' => 'nullable|date',
                'payment_mode' => 'required|in:Cash,Cheque,BankTransfer,SalaryDeduction,other',
                'remarks' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            SecurityTransaction::create($data);
            return response()->json([
                'message' => 'payroll security transaction added successfully.',
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating salary structure type: ' . $e->getMessage());
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $security_id)
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
                'organization_payroll_security_id' => $security_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_security_transaction_id' => 'required|integer|exists:organization_payroll_security_transactions,organization_payroll_security_transaction_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $salary = PayrollSecurity::find($security_id);
            return response()->json([
                'message' => 'Payroll security transaction  found',
                'Payroll' => $salary
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $security_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_security_id' => $security_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_security_transaction_id' => 'required|integer|exists:organization_payroll_security_transactions,organization_payroll_security_transaction_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_payroll_security_id' => 'nullable|integer|exists:organization_payroll_securities,organization_payroll_security_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'transaction_type' => 'required|in:Collection,Refund,Completed,Adjustment,Forfeited',
                'amount' => 'nullable|numeric|min:0',
                'reference_no' => 'nullable|string|max:100',
                'transaction_date' => 'nullable|date',
                'payment_mode' => 'required|in:Cash,Cheque,BankTransfer,SalaryDeduction,other',
                'remarks' => 'nullable|string|max:255',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $salary = PayrollSecurity::find($security_id);
            $salary->update($request->only([
                'organization_entity_id',
                'organization_id',
                'employee_id',
                'security_amount',
                'balance_amount',
                'recovery_months',
                'installment_amount',
                'recovery_start_month',
                'status',
                'remarks',
            ]));
            return response()->json([
                'message' => 'Payroll Security transaction Updated Successfully.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $security_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_security_id' => $security_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_security_transaction_id' => 'required|integer|exists:organization_payroll_security_transactions,organization_payroll_security_transaction_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $salary = SecurityTransaction::find($security_id);
            $salary->delete();
            return response()->json([
                'message' => 'Payroll security transaction deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
