<?php

namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\EmployeeSalaryStructure;
use App\Models\PayrollModels\PayrollComponentTypes;
use App\Models\PayrollModels\PayrollCycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PayrollEmployeeSalaryStructureController extends Controller
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

            $query = EmployeeSalaryStructure::with('PayrollCycle','employee')->where('organization_id', $org_id);
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
                'message' => 'Payroll Cycle fetched successfully',
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
                'organization_configuration_template_id' => 'nullable|integer|exists:organization_configuration_templates,organization_configuration_template_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',
                'organization_payroll_cycle_id' => 'required|integer|exists:organization_payroll_cycles,organization_payroll_cycle_id',
                'salary_basis' => 'required|in:Monthly,Daily,Hourly',
                'hourly_salary_amount' => 'nullable|numeric|min:0|required_if:salary_basis,Hourly',
                'daily_salary_amount' => 'nullable|numeric|min:0|required_if:salary_basis,Daily',
                'monthly_salary_amount' => 'nullable|numeric|min:0|required_if:salary_basis,Monthly',
                'annual_salary_amount' => 'nullable|numeric|min:0',
                'effective_from' => 'nullable|date|before_or_equal:effective_to',
                'effective_to' => 'nullable|date|after_or_equal:effective_from',
                'is_active' => 'nullable|boolean',
                'remarks' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            EmployeeSalaryStructure::create($data);
            return response()->json([
                'message' => 'Employee salary structure added successfully.',
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating salary structure type: ' . $e->getMessage());
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $structure_id)
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
                'organization_payroll_employee_salary_structure_id' => $structure_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_employee_salary_structure_id' => 'required|integer|exists:organization_payroll_employee_salary_structures,organization_payroll_employee_salary_structure_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $salary = EmployeeSalaryStructure::find($structure_id);
            $salary->load('PayrollCycle','employee');
            return response()->json([
                'message' => 'Payroll cycle found',
                'payroll' => $salary
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $structure_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_employee_salary_structure_id' => $structure_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_employee_salary_structure_id' => 'required|integer|exists:organization_payroll_employee_salary_structures,organization_payroll_employee_salary_structure_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_configuration_template_id' => 'nullable|integer|exists:organization_configuration_templates,organization_configuration_template_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',
                'organization_payroll_cycle_id' => 'required|integer|exists:organization_payroll_cycles,organization_payroll_cycle_id',
                'salary_basis' => 'required|in:Monthly,Daily,Hourly',
                'hourly_salary_amount' => 'nullable|numeric|min:0|required_if:salary_basis,Hourly',
                'daily_salary_amount' => 'nullable|numeric|min:0|required_if:salary_basis,Daily',
                'monthly_salary_amount' => 'nullable|numeric|min:0|required_if:salary_basis,Monthly',
                'annual_salary_amount' => 'nullable|numeric|min:0',
                'effective_from' => 'nullable|date|before_or_equal:effective_to',
                'effective_to' => 'nullable|date|after_or_equal:effective_from',
                'is_active' => 'nullable|boolean',
                'remarks' => 'nullable|string|max:255',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $salary = EmployeeSalaryStructure::find($structure_id);
            $salary->update($request->only([
                'organization_configuration_template_id',
                'organization_entity_id',
                'organization_id',
                'employee_id',
                'organization_payroll_cycle_id',
                'salary_basis',
                'hourly_salary_amount',
                'daily_salary_amount',
                'monthly_salary_amount',
                'annual_salary_amount',
                'effective_from',
                'effective_to',
                'is_active',
                'effective_from',
                'effective_to',
                'remarks'
            ]));
            return response()->json([
                'message' => 'Salary Structure  Updated Successfully.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $structure_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_employee_salary_structure_id' => $structure_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_employee_salary_structure_id' => 'required|integer|exists:organization_payroll_employee_salary_structures,organization_payroll_employee_salary_structure_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $salary = EmployeeSalaryStructure::find($structure_id);
            $salary->delete();
            return response()->json([
                'message' => 'Employee salary structure Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
