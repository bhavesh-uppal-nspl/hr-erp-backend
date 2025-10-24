<?php

namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\EmployeeSalaryStructure;
use App\Models\PayrollModels\PayrollSalaryComponent;
use App\Models\PayrollModels\PayrollSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;

class PayrollSecurityController extends Controller
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

            $query = PayrollSecurity::with('employee')->where('organization_id', $org_id);
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
                'message' => 'Payroll security  fetched successfully',
                'payroll' => $statusTypes
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching attendance break types' . $e->getMessage());

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
                'status' => 'required|in:Agreed,InRecovery,Completed,Refunded,Forfeited,Cancelled',
                'security_amount' => 'nullable|numeric|min:0',
                'balance_amount' => 'nullable|numeric|min:0',
                'installment_amount' => 'nullable|numeric|min:1',
                'recovery_start_month' => 'nullable|date',
                'recovery_months' => 'nullable|integer|min:1',
                'is_active' => 'boolean',
                'remarks' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            PayrollSecurity::create($data);
            return response()->json([
                'message' => 'Employee payroll security added successfully.',
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
                'organization_payroll_security_id' => 'required|integer|exists:organization_payroll_securities,organization_payroll_security_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $salary = PayrollSecurity::find($security_id);
            $salary->load('PayrollComponent', 'PayrollSalaryStructure');
            return response()->json([
                'message' => 'Payroll security  found',
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
                'organization_payroll_security_id' => 'required|integer|exists:organization_payroll_securities,organization_payroll_security_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'status' => 'required|in:Agreed,InRecovery,Completed,Refunded,Forfeited,Cancelled',
                'security_amount' => 'nullable|numeric|min:0',
                'balance_amount' => 'nullable|numeric|min:0',
                'installment_amount' => 'nullable|numeric|min:1',
                'recovery_start_month' => 'nullable|date',
                'recovery_months' => 'nullable|integer|min:1',
                'is_active' => 'boolean',
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
                'message' => 'Payroll Security Updated Successfully.',
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
                'organization_payroll_security_id' => 'required|integer|exists:organization_payroll_securities,organization_payroll_security_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $salary = PayrollSecurity::find($security_id);
            $salary->delete();
            return response()->json([
                'message' => 'Payroll security deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
