<?php

namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\PayrollComponent;
use App\Models\PayrollModels\PayrollComponentTypes;
use App\Models\PayrollModels\PayrollLoan;
use App\Models\PayrollModels\PayrollRunEmployee;
use App\Models\PayrollModels\PayrollRunEmployeeComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PayrollRunEmployeeComponentController extends Controller
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

            $query = PayrollRunEmployeeComponent::with('PayrollRun', 'PayrollComponent')->where('organization_id', $org_id);

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
                'message' => 'Payroll Run Employee  fetched successfully',
                'payroll' => $statusTypes
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching payroll run employee: ' . $e->getMessage());

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
                'organization_payroll_run_employee_id' => 'nullable|integer|exists:organization_payroll_run_employees,organization_payroll_run_employee_id',
                'organization_payroll_component_id' => 'nullable|integer|exists:organization_payroll_components,organization_payroll_component_id',
                'component_type' => 'nullable|in:Earning,Deduction,Rejected,EmployerContribution,InRepayment',
                'amount' => 'nullable|numeric',
                'remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            PayrollLoan::create($data);
            return response()->json([
                'message' => 'Payroll Run Employee Component added successfully.',
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating payroll run employee component : ' . $e->getMessage());
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $org_id, $id)
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
                'organization_payroll_run_employee_component_id' => $id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_run_employee_component_id' => 'required|integer|exists:organization_payroll_run_employee_components,organization_payroll_run_employee_component_id',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $payroll = PayrollRunEmployeeComponent::find($id);
            $payroll->load('PayrollRun', 'PayrollComponent');
            return response()->json([
                'message' => 'Payroll run employee component found',
                'payroll' => $payroll
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $org_id, $id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_run_employee_component_id' => $id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_run_employee_component_id' => 'required|integer|exists:organization_payroll_run_employee_components,organization_payroll_run_employee_component_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_payroll_run_employee_id' => 'nullable|integer|exists:organization_payroll_run_employees,organization_payroll_run_employee_id',
                'organization_payroll_component_id' => 'nullable|integer|exists:organization_payroll_components,organization_payroll_component_id',
                'component_type' => 'nullable|in:Earning,Deduction,Rejected,EmployerContribution,InRepayment',
                'amount' => 'nullable|numeric',
                'remarks' => 'nullable|string',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = PayrollRunEmployeeComponent::find($id);
            $payroll->update($request->only([
                'organization_entity_id',
                'organization_id',
                'organization_payroll_run_employee_id',
                'organization_payroll_component_id',
                'component_type',
                'amount',
                'remarks',
            ]));
            return response()->json([
                'message' => 'Payroll run employee component  Updated Successfully.',
                'payroll' => $payroll
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_run_employee_component_id' => $id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_run_employee_component_id' => 'required|integer|exists:organization_payroll_run_employee_components,organization_payroll_run_employee_component_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = PayrollRunEmployeeComponent::find($id);
            $payroll->delete();
            return response()->json([
                'message' => 'payroll run employee component Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



}
